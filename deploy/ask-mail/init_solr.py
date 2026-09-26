"""Create the separate Ask Mail collection and its explicit Solr 9 schema."""

import json
import os
import urllib.parse
import urllib.error

from worker import http_bytes


def request(url, payload=None):
    raw = http_bytes(url, json.dumps(payload).encode() if payload is not None else None,
                     {"Content-Type": "application/json"} if payload is not None else {},
                     "POST" if payload is not None else "GET", 30,
                     os.environ.get("AIC_SOLR_USER", ""), os.environ.get("AIC_SOLR_PASSWORD", ""))
    response = json.loads(raw)
    if response.get("responseHeader", {}).get("status", 0) != 0:
        raise RuntimeError("Solr setup failed: " + str(response.get("error", {}).get("msg", "unknown error")))
    return response


def ensure_schema(base):
    schema = request(base + "/schema")
    field_types = {item["name"]: item for item in schema["schema"]["fieldTypes"]}
    fields = {item["name"]: item for item in schema["schema"]["fields"]}
    if "aic_vector_1024" in field_types and str(field_types["aic_vector_1024"].get("vectorDimension")) != "1024":
        raise RuntimeError("Existing vector type has a different dimension; use a new collection")
    if "vector" in fields and fields["vector"].get("type") != "aic_vector_1024":
        raise RuntimeError("Existing vector field uses a different type; use a new collection")
    if "text_t" in fields or "filename_s" in fields:
        raise RuntimeError("Existing collection contains private text or filenames; use a new vector-only collection")
    if "aic_vector_1024" not in field_types:
        request(base + "/schema", {"add-field-type": {
            "name": "aic_vector_1024", "class": "solr.DenseVectorField",
            "vectorDimension": 1024, "similarityFunction": "cosine", "knnAlgorithm": "hnsw"}})
    definitions = [
        {"name": "kind_s", "type": "string", "indexed": True, "stored": True},
        {"name": "owner_i", "type": "pint", "indexed": True, "stored": True},
        {"name": "mailbox_s", "type": "string", "indexed": True, "stored": True},
        {"name": "uid_l", "type": "plong", "indexed": True, "stored": True, "docValues": True},
        {"name": "uidvalidity_l", "type": "plong", "indexed": True, "stored": True},
        {"name": "part_s", "type": "string", "indexed": False, "stored": True},
        {"name": "offset_i", "type": "pint", "indexed": False, "stored": True},
        {"name": "vector", "type": "aic_vector_1024", "indexed": True, "stored": False},
        {"name": "indexed_i", "type": "pint", "indexed": False, "stored": True},
        {"name": "skipped_i", "type": "pint", "indexed": False, "stored": True},
        {"name": "estimated_i", "type": "pint", "indexed": False, "stored": True},
        {"name": "complete_b", "type": "boolean", "indexed": False, "stored": True},
        {"name": "error_s", "type": "string", "indexed": False, "stored": True},
    ]
    for field in definitions:
        if field["name"] not in fields:
            request(base + "/schema", {"add-field": field})


def main():
    base = os.environ["AIC_SOLR_URL"].rstrip("/")
    marker = "/solr/"
    if marker not in base:
        raise SystemExit("AIC_SOLR_URL must end in /solr/<collection>")
    root, collection = base.rsplit(marker, 1)
    if not collection or "/" in collection:
        raise SystemExit("Invalid Solr collection name")
    admin = root + "/solr/admin/collections?" + urllib.parse.urlencode({"action": "LIST", "wt": "json"})
    try:
        existing = request(admin).get("collections", [])
        if collection not in existing:
            create = root + "/solr/admin/collections?" + urllib.parse.urlencode({
                "action": "CREATE", "name": collection, "numShards": "1", "replicationFactor": "1",
                "collection.configName": "_default", "wt": "json"})
            request(create)
    except urllib.error.HTTPError:
        # Standalone Solr has no Collections API; its core must already exist.
        request(base + "/schema")
    ensure_schema(base)
    print("Ask Mail collection and schema are ready:", base)


if __name__ == "__main__":
    main()
