"""Resumable, per-user IMAP indexer for the optional Ask Mail feature.

Only embeddings and message coordinates are sent to Solr. Message text and
attachment bytes are never persisted in this worker's state or vector index.
"""

import base64
import email
import hashlib
import imaplib
import json
import logging
import os
import re
import sqlite3
import ssl
import time
import urllib.error
import urllib.parse
import urllib.request
import math
from email import policy
from html.parser import HTMLParser


LOG = logging.getLogger("ask_mail")
CHUNK_SIZE = 1800
CHUNK_OVERLAP = 180
VECTOR_DIMENSION = 1024
ATTACHMENT_TYPES = {
    "application/pdf", "application/msword",
    "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
    "application/vnd.ms-excel",
    "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
    "application/vnd.ms-powerpoint",
    "application/vnd.openxmlformats-officedocument.presentationml.presentation",
    "application/vnd.oasis.opendocument.text",
    "application/vnd.oasis.opendocument.spreadsheet",
    "application/vnd.oasis.opendocument.presentation",
}


class HtmlText(HTMLParser):
    def __init__(self):
        super().__init__(convert_charrefs=True)
        self.parts = []
        self.suppressed = 0

    def handle_starttag(self, tag, attrs):
        if tag in {"script", "style"}:
            self.suppressed += 1
        elif tag in {"p", "div", "br", "li", "tr"}:
            self.parts.append(" ")

    def handle_endtag(self, tag):
        if tag in {"script", "style"} and self.suppressed:
            self.suppressed -= 1
        elif tag in {"p", "div", "li", "tr"}:
            self.parts.append(" ")

    def handle_data(self, data):
        if not self.suppressed:
            self.parts.append(data)


def normalize(text):
    return " ".join(text.split())


def chunks(text):
    text = normalize(text)
    step = CHUNK_SIZE - CHUNK_OVERLAP
    for start in range(0, len(text), step):
        part = text[start:start + CHUNK_SIZE]
        if part:
            yield start, part
        if start + CHUNK_SIZE >= len(text):
            break


def sampled_chunks(text, limit):
    """Bound vector cost while spreading excerpts across long documents."""
    text = normalize(text)
    count = max(1, math.ceil(max(0, len(text) - CHUNK_OVERLAP) / (CHUNK_SIZE - CHUNK_OVERLAP)))
    if count <= limit:
        yield from chunks(text)
        return
    indexes = sorted({round(index * (count - 1) / max(1, limit - 1)) for index in range(limit)})
    for index in indexes:
        start = index * (CHUNK_SIZE - CHUNK_OVERLAP)
        yield start, text[start:start + CHUNK_SIZE]


def decode_mailbox(value):
    """Decode IMAP modified UTF-7, retaining the exact wire name separately."""
    def replace(match):
        encoded = match.group(1)
        if not encoded:
            return "&"
        data = base64.b64decode(encoded.replace(",", "/") + "=" * (-len(encoded) % 4))
        return data.decode("utf-16-be")

    return re.sub(r"&([A-Za-z0-9+,]*)-", replace, value.decode("ascii"))


def quote_mailbox(value):
    return '"' + value.decode("ascii").replace("\\", "\\\\").replace('"', '\\"') + '"'


def excluded_mailbox(name, flags):
    flags = flags.lower()
    parts = {part.casefold() for part in re.split(r"[/.]", name)}
    return any(flag in flags for flag in (b"\\junk", b"\\trash", b"\\drafts")) or bool(parts & {
        "spam", "junk", "trash", "deleted items", "drafts", "skice", "smece", "smeće",
    })


def extract_parts(message, tika_url, max_attachment_bytes):
    """Return (MIME part id, filename, text) without mixing alternative bodies."""
    body_plain = None
    body_html = None
    attachments = []

    def visit(part, path):
        nonlocal body_plain, body_html
        if part.is_multipart():
            for number, child in enumerate(part.iter_parts(), 1):
                visit(child, f"{path}.{number}" if path else str(number))
            return
        mime = part.get_content_type().lower()
        filename = part.get_filename()
        try:
            payload = part.get_payload(decode=True) or b""
            if not filename and part.get_content_disposition() != "attachment":
                if mime == "text/plain" and body_plain is None:
                    body_plain = (path or "1", part.get_content())
                elif mime == "text/html" and body_html is None:
                    parser = HtmlText()
                    parser.feed(part.get_content())
                    body_html = (path or "1", "".join(parser.parts))
                return
            if not payload or len(payload) > max_attachment_bytes:
                return
            if mime in ("text/plain", "text/html"):
                content = part.get_content()
                if mime == "text/html":
                    parser = HtmlText()
                    parser.feed(content)
                    content = "".join(parser.parts)
            elif mime in ATTACHMENT_TYPES and tika_url:
                content = http_bytes(tika_url.rstrip("/") + "/tika", payload,
                                     {"Accept": "text/plain", "Content-Type": mime}, "PUT", 90).decode("utf-8", "replace")
            else:
                return
            attachments.append((path or "1", filename or "attachment", content))
        except (UnicodeError, ValueError, urllib.error.URLError) as error:
            LOG.warning("Skipping unreadable MIME part %s: %s", path, type(error).__name__)

    visit(message, "")
    body = body_plain or body_html
    result = [(body[0], "", body[1])] if body else []
    return result + attachments


def http_bytes(url, payload=None, headers=None, method=None, timeout=30, username="", password=""):
    headers = dict(headers or {})
    if username:
        token = base64.b64encode((username + ":" + password).encode()).decode()
        headers["Authorization"] = "Basic " + token
    request = urllib.request.Request(url, data=payload, headers=headers, method=method)
    with urllib.request.urlopen(request, timeout=timeout) as response:
        return response.read()


class Endpoints:
    def __init__(self, solr_url, ollama_url, model, solr_user="", solr_password=""):
        self.solr = solr_url.rstrip("/")
        self.ollama = ollama_url.rstrip("/")
        self.model = model
        self.solr_user = solr_user
        self.solr_password = solr_password

    def solr_json(self, path, payload):
        raw = http_bytes(self.solr + path, json.dumps(payload).encode(),
                         {"Content-Type": "application/json"}, "POST", 45,
                         self.solr_user, self.solr_password)
        answer = json.loads(raw)
        if answer.get("responseHeader", {}).get("status", 0) != 0:
            raise RuntimeError("Solr rejected an update")
        return answer

    def embed(self, texts):
        raw = http_bytes(self.ollama + "/api/embed",
                         json.dumps({"model": self.model, "input": texts, "truncate": False}).encode(),
                         {"Content-Type": "application/json"}, "POST", 120)
        vectors = json.loads(raw).get("embeddings")
        if not isinstance(vectors, list) or len(vectors) != len(texts):
            raise RuntimeError("Ollama returned an invalid embedding batch")
        if any(not isinstance(v, list) or len(v) != VECTOR_DIMENSION for v in vectors):
            raise RuntimeError("Embedding dimensions differ from the Solr schema")
        return vectors

    def replace_message(self, owner, mailbox, uid, docs):
        query = "owner_i:%d AND mailbox_s:%s AND uid_l:%d AND kind_s:chunk" % (
            owner, json.dumps(mailbox, ensure_ascii=False), uid)
        self.solr_json("/update?commitWithin=60000", {"delete": {"query": query}})
        if docs:
            self.solr_json("/update?commitWithin=60000", docs)

    def delete_folder(self, owner, mailbox):
        query = "owner_i:%d AND mailbox_s:%s AND kind_s:chunk" % (
            owner, json.dumps(mailbox, ensure_ascii=False))
        self.solr_json("/update?commitWithin=60000", {"delete": {"query": query}})

    def delete_owner(self, owner):
        self.solr_json("/update?commitWithin=60000",
                       {"delete": {"query": "owner_i:%d" % owner}})

    def indexed_uids(self, owner, mailbox, low, high):
        params = urllib.parse.urlencode({
            "q": "owner_i:%d AND mailbox_s:%s AND uid_l:[%d TO %d] AND kind_s:chunk" % (
                owner, json.dumps(mailbox, ensure_ascii=False), low, high),
            "rows": 0, "wt": "json",
            "json.facet": json.dumps({"uids": {"type": "terms", "field": "uid_l", "limit": high - low + 1}}),
        })
        raw = http_bytes(self.solr + "/select", params.encode(),
                         {"Content-Type": "application/x-www-form-urlencoded"}, "POST", 45,
                         self.solr_user, self.solr_password)
        return {int(item["val"]) for item in json.loads(raw).get("facets", {}).get("uids", {}).get("buckets", [])}

    def progress(self, owner, indexed, skipped, estimated, complete, error=""):
        doc = {"id": "state:%d" % owner, "kind_s": "state", "owner_i": owner,
               "indexed_i": indexed, "skipped_i": skipped, "estimated_i": estimated,
               "complete_b": complete, "error_s": error[:120]}
        self.solr_json("/update?commitWithin=60000", [doc])


class State:
    def __init__(self, path):
        self.db = sqlite3.connect(path)
        self.db.execute("""CREATE TABLE IF NOT EXISTS folders (
            owner INTEGER NOT NULL, mailbox TEXT NOT NULL, uidvalidity INTEGER NOT NULL,
            backfill_next INTEGER NOT NULL, live_high INTEGER NOT NULL,
            indexed INTEGER NOT NULL DEFAULT 0, skipped INTEGER NOT NULL DEFAULT 0,
            estimated INTEGER NOT NULL DEFAULT 0, PRIMARY KEY(owner, mailbox))""")
        columns = {row[1] for row in self.db.execute("PRAGMA table_info(folders)")}
        if "reconcile_next" not in columns:
            self.db.execute("ALTER TABLE folders ADD COLUMN reconcile_next INTEGER NOT NULL DEFAULT 0")
        self.db.commit()

    def validity(self, owner, mailbox):
        row = self.db.execute("SELECT uidvalidity FROM folders WHERE owner=? AND mailbox=?", (owner, mailbox)).fetchone()
        return row[0] if row else None

    def mailboxes(self, owner):
        return {row[0] for row in self.db.execute("SELECT mailbox FROM folders WHERE owner=?", (owner,))}

    def owners(self):
        return {row[0] for row in self.db.execute("SELECT DISTINCT owner FROM folders")}

    def forget_folder(self, owner, mailbox):
        self.db.execute("DELETE FROM folders WHERE owner=? AND mailbox=?", (owner, mailbox))
        self.db.commit()

    def forget_owner(self, owner):
        self.db.execute("DELETE FROM folders WHERE owner=?", (owner,))
        self.db.commit()

    def folder(self, owner, mailbox, uidvalidity, uidnext, estimated):
        row = self.db.execute("SELECT uidvalidity, backfill_next, live_high, indexed, skipped FROM folders WHERE owner=? AND mailbox=?",
                              (owner, mailbox)).fetchone()
        if not row or row[0] != uidvalidity:
            self.db.execute("""REPLACE INTO folders
                (owner, mailbox, uidvalidity, backfill_next, live_high, indexed, skipped, estimated, reconcile_next)
                VALUES (?, ?, ?, ?, ?, 0, 0, ?, ?)""",
                (owner, mailbox, uidvalidity, max(0, uidnext - 1), max(0, uidnext - 1),
                 estimated, max(0, uidnext - 1)))
            self.db.commit()
            return (max(0, uidnext - 1), max(0, uidnext - 1), 0, 0)
        self.db.execute("UPDATE folders SET estimated=? WHERE owner=? AND mailbox=?", (estimated, owner, mailbox))
        self.db.commit()
        return row[1:]

    def update(self, owner, mailbox, backfill, high, indexed_delta, skipped_delta):
        self.db.execute("""UPDATE folders SET backfill_next=?, live_high=?,
            indexed=indexed+?, skipped=skipped+? WHERE owner=? AND mailbox=?""",
            (backfill, high, indexed_delta, skipped_delta, owner, mailbox))
        self.db.commit()

    def progress(self, owner):
        row = self.db.execute("SELECT COALESCE(SUM(indexed),0), COALESCE(SUM(skipped),0), COALESCE(SUM(estimated),0), COALESCE(SUM(backfill_next),0) FROM folders WHERE owner=?", (owner,)).fetchone()
        return row[:3] + (row[3] == 0,)

    def reconciliation_cursor(self, owner, mailbox, uidnext):
        row = self.db.execute("SELECT reconcile_next FROM folders WHERE owner=? AND mailbox=?", (owner, mailbox)).fetchone()
        return row[0] if row and row[0] > 0 else max(0, uidnext - 1)

    def set_reconciliation_cursor(self, owner, mailbox, cursor):
        self.db.execute("UPDATE folders SET reconcile_next=? WHERE owner=? AND mailbox=?", (cursor, owner, mailbox))
        self.db.commit()

    def decrement_indexed(self, owner, mailbox, count):
        self.db.execute("UPDATE folders SET indexed=MAX(0, indexed-?) WHERE owner=? AND mailbox=?",
                        (count, owner, mailbox))
        self.db.commit()


def accounts(db_url):
    parsed = urllib.parse.urlparse(db_url)
    if parsed.scheme == "sqlite":
        connection = sqlite3.connect(parsed.path)
        rows = connection.execute("SELECT user_id, username FROM users WHERE username IS NOT NULL").fetchall()
    elif parsed.scheme in ("mysql", "mariadb"):
        import pymysql
        connection = pymysql.connect(host=parsed.hostname, port=parsed.port or 3306,
                                     user=urllib.parse.unquote(parsed.username or ""),
                                     password=urllib.parse.unquote(parsed.password or ""),
                                     database=parsed.path.lstrip("/"), connect_timeout=15)
        with connection.cursor() as cursor:
            cursor.execute("SELECT user_id, username FROM users WHERE username IS NOT NULL")
            rows = cursor.fetchall()
    elif parsed.scheme in ("postgres", "postgresql"):
        import psycopg
        connection = psycopg.connect(db_url)
        with connection.cursor() as cursor:
            cursor.execute("SELECT user_id, username FROM users WHERE username IS NOT NULL")
            rows = cursor.fetchall()
    else:
        raise ValueError("Unsupported Roundcube database URL")
    connection.close()
    return [(int(user_id), str(username)) for user_id, username in rows]


def list_mailboxes(imap):
    status, lines = imap.list()
    if status != "OK":
        raise RuntimeError("Could not list IMAP folders")
    for line in lines or []:
        if not line:
            continue
        match = re.match(rb"^\(([^)]*)\) (?:\"([^\"]*)\"|NIL) (?:\"(.*)\"|(.*))$", line)
        if not match:
            continue
        flags, delimiter, quoted, bare = match.groups()
        wire = (quoted or bare or b"").replace(b'\\"', b'"')
        name = decode_mailbox(wire)
        if b"\\noselect" not in flags.lower() and not excluded_mailbox(name, flags):
            yield wire, name


def response_number(imap, key, fallback=0):
    result = imap.response(key)[1]
    return int(result[0]) if result and result[0] else fallback


def uid_range(imap, low, high):
    if high < low:
        return []
    status, data = imap.uid("SEARCH", None, "UID", "%d:%d" % (low, high))
    if status != "OK":
        raise RuntimeError("IMAP UID search failed")
    return [int(item) for item in (data[0] or b"").split()]


def index_uid(imap, endpoints, owner, mailbox, uidvalidity, uid, tika_url, max_message, max_attachment):
    status, size_data = imap.uid("FETCH", str(uid), "(RFC822.SIZE)")
    if status != "OK":
        raise RuntimeError("IMAP size fetch failed")
    size_bytes = b" ".join(item if isinstance(item, bytes) else item[0] for item in size_data or [] if item)
    match = re.search(rb"RFC822.SIZE (\d+)", size_bytes)
    if match and int(match.group(1)) > max_message:
        return False
    status, data = imap.uid("FETCH", str(uid), "(BODY.PEEK[])")
    if status != "OK":
        raise RuntimeError("IMAP message fetch failed")
    raw = next((item[1] for item in data or [] if isinstance(item, tuple)), None)
    if raw is None:
        return False  # Message was removed between SEARCH and FETCH.
    if len(raw) > max_message:
        return False
    message = email.message_from_bytes(raw, policy=policy.default)
    parts = extract_parts(message, tika_url, max_attachment)
    coordinates = []
    texts = []
    for part, filename, content in parts:
        part_limit = 24 if not filename else 12
        for offset, text in sampled_chunks(content, min(part_limit, 80 - len(texts))):
            coordinates.append((part, offset))
            texts.append(text)
        if len(texts) >= 80:
            break
    docs = []
    for batch_start in range(0, len(texts), 12):
        batch = texts[batch_start:batch_start + 12]
        vectors = endpoints.embed(batch)
        for local, vector in enumerate(vectors):
            index = batch_start + local
            part, offset = coordinates[index]
            identity = "%s\0%s\0%s\0%s\0%s\0%s" % (owner, mailbox, uidvalidity, uid, part, offset)
            docs.append({"id": "chunk:" + hashlib.sha256(identity.encode()).hexdigest(),
                         "kind_s": "chunk", "owner_i": owner, "mailbox_s": mailbox,
                         "uid_l": uid, "uidvalidity_l": uidvalidity, "part_s": part,
                         "offset_i": offset,
                         "vector": vector})
    endpoints.replace_message(owner, mailbox, uid, docs)
    return bool(docs)


def process_folder(imap, endpoints, state, owner, wire, mailbox, settings):
    status, count = imap.select(quote_mailbox(wire), readonly=True)
    if status != "OK":
        raise RuntimeError("Could not select IMAP folder")
    uidvalidity = response_number(imap, "UIDVALIDITY")
    uidnext = response_number(imap, "UIDNEXT", 1)
    if uidvalidity < 1:
        raise RuntimeError("IMAP server did not report UIDVALIDITY")
    previous_validity = state.validity(owner, mailbox)
    if previous_validity is not None and previous_validity != uidvalidity:
        endpoints.delete_folder(owner, mailbox)
    backfill, high, _, _ = state.folder(owner, mailbox, uidvalidity, uidnext, int(count[0]))
    budget = settings["batch"]
    # New mail is prioritized even during a very large historical backfill.
    for uid in uid_range(imap, high + 1, uidnext - 1):
        if budget <= 0:
            break
        indexed = index_uid(imap, endpoints, owner, mailbox, uidvalidity, uid,
                            settings["tika"], settings["max_message"], settings["max_attachment"])
        state.update(owner, mailbox, backfill, uid, int(indexed), int(not indexed))
        high = uid
        budget -= 1
    if budget > 0 and high < uidnext - 1:
        high = uidnext - 1
        state.update(owner, mailbox, backfill, high, 0, 0)
    while backfill > 0 and budget > 0:
        low = max(1, backfill - settings["uid_window"] + 1)
        found = uid_range(imap, low, backfill)
        for uid in reversed(found[:]):
            if budget <= 0:
                break
            indexed = index_uid(imap, endpoints, owner, mailbox, uidvalidity, uid,
                                settings["tika"], settings["max_message"], settings["max_attachment"])
            state.update(owner, mailbox, uid - 1, high, int(indexed), int(not indexed))
            backfill = uid - 1
            budget -= 1
        if budget and (not found or backfill >= low):
            backfill = low - 1
            state.update(owner, mailbox, backfill, high, 0, 0)
    if backfill == 0 and uidnext > 1:
        high_uid = state.reconciliation_cursor(owner, mailbox, uidnext)
        low_uid = max(1, high_uid - settings["uid_window"] + 1)
        present = set(uid_range(imap, low_uid, high_uid))
        missing_uids = endpoints.indexed_uids(owner, mailbox, low_uid, high_uid) - present
        for missing in missing_uids:
            endpoints.replace_message(owner, mailbox, missing, [])
        if missing_uids:
            state.decrement_indexed(owner, mailbox, len(missing_uids))
        state.set_reconciliation_cursor(owner, mailbox, low_uid - 1)
    imap.close()


def run_once(settings, endpoints, state):
    known_accounts = accounts(settings["database"])
    active_owners = {owner for owner, _ in known_accounts}
    for removed in state.owners() - active_owners:
        endpoints.delete_owner(removed)
        state.forget_owner(removed)
    for owner, username in known_accounts:
        imap = None
        last_error = ""
        try:
            context = ssl.create_default_context(cafile=settings["imap_ca"] or None)
            imap = imaplib.IMAP4_SSL(settings["imap_host"], settings["imap_port"], ssl_context=context)
            imap.login(username + settings["master_separator"] + settings["master_user"], settings["master_password"])
            mailboxes = list(list_mailboxes(imap))
            current_names = {name for _, name in mailboxes}
            for removed in state.mailboxes(owner) - current_names:
                endpoints.delete_folder(owner, removed)
                state.forget_folder(owner, removed)
            for wire, mailbox in mailboxes:
                try:
                    process_folder(imap, endpoints, state, owner, wire, mailbox, settings)
                except Exception as error:
                    LOG.exception("Could not index user %d folder %s", owner, mailbox)
                    last_error = type(error).__name__
            endpoints.progress(owner, *state.progress(owner), error=last_error)
        except Exception as error:
            LOG.exception("Could not index user %d", owner)
            try:
                endpoints.progress(owner, *state.progress(owner), error=type(error).__name__)
            except Exception:
                LOG.exception("Could not publish progress for user %d", owner)
        finally:
            if imap:
                try:
                    imap.logout()
                except Exception:
                    pass


def main():
    logging.basicConfig(level=os.environ.get("AIC_LOG_LEVEL", "INFO"))
    required = ["AIC_ROUNDCUBE_DB_URL", "AIC_IMAP_HOST", "AIC_IMAP_MASTER_USER",
                "AIC_IMAP_MASTER_PASSWORD", "AIC_SOLR_URL", "AIC_OLLAMA_URL"]
    missing = [name for name in required if not os.environ.get(name)]
    if missing:
        raise SystemExit("Missing required environment: " + ", ".join(missing))
    settings = {"database": os.environ["AIC_ROUNDCUBE_DB_URL"],
                "imap_host": os.environ["AIC_IMAP_HOST"],
                "imap_port": int(os.environ.get("AIC_IMAP_PORT", "993")),
                "imap_ca": os.environ.get("AIC_IMAP_CA_FILE", ""),
                "master_user": os.environ["AIC_IMAP_MASTER_USER"],
                "master_password": os.environ["AIC_IMAP_MASTER_PASSWORD"],
                "master_separator": os.environ.get("AIC_IMAP_MASTER_SEPARATOR", "*"),
                "tika": os.environ.get("AIC_TIKA_URL", ""),
                "batch": int(os.environ.get("AIC_BATCH_PER_FOLDER", "30")),
                "uid_window": int(os.environ.get("AIC_UID_WINDOW", "1000")),
                "max_message": int(os.environ.get("AIC_MAX_MESSAGE_BYTES", "31457280")),
                "max_attachment": int(os.environ.get("AIC_MAX_ATTACHMENT_BYTES", "20971520"))}
    if settings["batch"] < 1 or settings["uid_window"] < 1:
        raise SystemExit("Batch and UID window must be positive")
    endpoints = Endpoints(os.environ["AIC_SOLR_URL"], os.environ["AIC_OLLAMA_URL"],
                          os.environ.get("AIC_EMBED_MODEL", "qwen3-embedding:0.6b"),
                          os.environ.get("AIC_SOLR_USER", ""), os.environ.get("AIC_SOLR_PASSWORD", ""))
    state = State(os.environ.get("AIC_STATE_PATH", "/data/ask-mail.sqlite3"))
    while True:
        run_once(settings, endpoints, state)
        time.sleep(max(5, int(os.environ.get("AIC_SCAN_INTERVAL_SECONDS", "60"))))


if __name__ == "__main__":
    main()
