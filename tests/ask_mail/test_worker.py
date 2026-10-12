import email
import importlib.util
import pathlib
import tempfile
import unittest
from email import policy
from unittest.mock import patch


PATH = pathlib.Path(__file__).resolve().parents[2] / "deploy" / "ask-mail" / "worker.py"
SPEC = importlib.util.spec_from_file_location("ask_mail_worker", PATH)
worker = importlib.util.module_from_spec(SPEC)
SPEC.loader.exec_module(worker)


class WorkerTest(unittest.TestCase):
    def test_modified_utf7_mailbox_and_exclusions(self):
        self.assertEqual(worker.decode_mailbox(b"Projects/&ZeVnLIqe-"), "Projects/日本語")
        self.assertEqual(worker.decode_mailbox(b"A&-B"), "A&B")
        self.assertTrue(worker.excluded_mailbox("INBOX.Drafts", b""))
        self.assertTrue(worker.excluded_mailbox("Work", b"\\Junk"))
        self.assertFalse(worker.excluded_mailbox("INBOX.Sent", b"\\Sent"))

    def test_chunks_are_bounded_and_cover_long_text(self):
        text = "A" * 9000 + " end"
        selected = list(worker.sampled_chunks(text, 3))
        self.assertEqual(len(selected), 3)
        self.assertEqual(selected[0][0], 0)
        self.assertIn("end", selected[-1][1])
        self.assertLessEqual(max(len(value) for _, value in selected), 1800)

    def test_extracts_plain_body_once_and_supported_attachment(self):
        message = email.message_from_string(
            "MIME-Version: 1.0\nContent-Type: multipart/mixed; boundary=x\n\n"
            "--x\nContent-Type: text/plain; charset=utf-8\n\nHello from body\n"
            "--x\nContent-Type: application/pdf\nContent-Disposition: attachment; filename=report.pdf\n"
            "Content-Transfer-Encoding: base64\n\nJVBERi0xLjQ=\n--x--\n", policy=policy.default)
        with patch.object(worker, "http_bytes", return_value=b"Quarterly report") as tika:
            parts = worker.extract_parts(message, "http://tika:9998", 1000)
        self.assertEqual(parts[0], ("1", "", "Hello from body"))
        self.assertEqual(parts[1], ("2", "report.pdf", "Quarterly report"))
        self.assertEqual(tika.call_count, 1)

    def test_state_resumes_and_detects_uidvalidity_change(self):
        with tempfile.TemporaryDirectory() as directory:
            state = worker.State(str(pathlib.Path(directory) / "state.sqlite3"))
            self.assertEqual(state.folder(4, "INBOX", 10, 51, 50), (50, 50, 0, 0))
            state.update(4, "INBOX", 49, 50, 1, 0)
            self.assertEqual(state.folder(4, "INBOX", 10, 51, 50), (49, 50, 1, 0))
            self.assertEqual(state.validity(4, "INBOX"), 10)
            self.assertEqual(state.folder(4, "INBOX", 11, 5, 4), (4, 4, 0, 0))
            self.assertEqual(state.progress(4), (0, 0, 4, False))
            state.db.close()

    def test_backfill_resumes_newest_first_and_prioritizes_new_mail(self):
        class Imap:
            uids = [2, 5, 8]

            def select(self, mailbox, readonly):
                self.asserted_mailbox = mailbox
                return "OK", [str(len(self.uids)).encode()]

            def response(self, key):
                return key, [b"21" if key == "UIDVALIDITY" else str(max(self.uids) + 1).encode()]

            def uid(self, command, _, keyword, uid_range):
                low, high = (int(value) for value in uid_range.split(":"))
                found = [uid for uid in self.uids if low <= uid <= high]
                return "OK", [" ".join(map(str, found)).encode()]

            def close(self):
                pass

        class Endpoints:
            def indexed_uids(self, owner, mailbox, low, high):
                return set()

        settings = {"batch": 2, "uid_window": 1000, "tika": "",
                    "max_message": 1000, "max_attachment": 1000}
        indexed = []
        with tempfile.TemporaryDirectory() as directory:
            state = worker.State(str(pathlib.Path(directory) / "state.sqlite3"))
            imap = Imap()
            with patch.object(worker, "index_uid", side_effect=lambda *args: indexed.append(args[5]) or True):
                worker.process_folder(imap, Endpoints(), state, 4, b"INBOX", "INBOX", settings)
                self.assertEqual(indexed, [8, 5])
                self.assertFalse(state.progress(4)[3])
                worker.process_folder(imap, Endpoints(), state, 4, b"INBOX", "INBOX", settings)
                self.assertEqual(indexed, [8, 5, 2])
                self.assertTrue(state.progress(4)[3])
                imap.uids.append(10)
                worker.process_folder(imap, Endpoints(), state, 4, b"INBOX", "INBOX", settings)
                self.assertEqual(indexed, [8, 5, 2, 10])
            state.db.close()

    def test_vector_documents_have_coordinates_but_no_message_text(self):
        raw = b"From: sender@example.com\r\nSubject: Project\r\nContent-Type: text/plain\r\n\r\nThe launch is on Friday."

        class Imap:
            def uid(self, command, uid, fields):
                if fields == "(RFC822.SIZE)":
                    return "OK", [b"3 (UID 3 RFC822.SIZE 110)"]
                return "OK", [(b"3 (UID 3 BODY[])", raw), b")"]

        class Endpoints:
            documents = None

            def embed(self, texts):
                return [[0.0] * 1024 for _ in texts]

            def replace_message(self, owner, mailbox, uid, docs):
                self.documents = docs

        endpoints = Endpoints()
        self.assertTrue(worker.index_uid(Imap(), endpoints, 4, "INBOX", 21, 3, "", 1000, 1000))
        self.assertEqual(endpoints.documents[0]["owner_i"], 4)
        self.assertEqual(endpoints.documents[0]["uid_l"], 3)
        self.assertNotIn("text_t", endpoints.documents[0])
        self.assertNotIn("filename_s", endpoints.documents[0])
        self.assertNotIn("The launch", str(endpoints.documents[0]))


if __name__ == "__main__":
    unittest.main()
