import assert from "node:assert/strict";
import test from "node:test";
import { translateAllParts } from "../../assets/src/summary/translation.js";

function fakeRoundcube(responses) {
  const calls = [];
  globalThis.rcmail = {
    http_post(action, data) {
      calls.push({ action, data });
      return {
        done(callback) {
          queueMicrotask(() => callback(responses.shift()));
          return this;
        },
        fail() {
          return this;
        },
      };
    },
  };
  return calls;
}

const sourceHash = "a".repeat(64);

test("translates every part in order and reports progress", async () => {
  const calls = fakeRoundcube([
    {
      status: "success",
      index: 0,
      totalChunks: 2,
      sourceHash,
      translation: "Prvi dio",
    },
    {
      status: "success",
      index: 1,
      totalChunks: 2,
      sourceHash,
      translation: "Drugi dio",
    },
  ]);
  const progress = [];

  assert.equal(
    await translateAllParts("42", "INBOX", "bs_BA", (...part) =>
      progress.push(part)
    ),
    "Prvi dio\n\nDrugi dio"
  );
  assert.deepEqual(progress, [
    [1, null],
    [2, 2],
  ]);
  assert.deepEqual(calls, [
    {
      action: "plugin.aicomposeplugin_TranslateMessageAction",
      data: { uid: "42", mailbox: "INBOX", locale: "bs_BA", index: "0" },
    },
    {
      action: "plugin.aicomposeplugin_TranslateMessageAction",
      data: { uid: "42", mailbox: "INBOX", locale: "bs_BA", index: "1" },
    },
  ]);
});

test("rejects a failed part instead of presenting an incomplete email", async () => {
  fakeRoundcube([
    {
      status: "success",
      index: 0,
      totalChunks: 2,
      sourceHash,
      translation: "Prvi dio",
    },
    { status: "error" },
  ]);

  await assert.rejects(translateAllParts("42", "INBOX", "bs_BA"));
});

test("rejects a changed message part count", async () => {
  fakeRoundcube([
    {
      status: "success",
      index: 0,
      totalChunks: 2,
      sourceHash,
      translation: "Prvi dio",
    },
    {
      status: "success",
      index: 1,
      totalChunks: 3,
      sourceHash,
      translation: "Drugi dio",
    },
  ]);

  await assert.rejects(translateAllParts("42", "INBOX", "bs_BA"));
});

test("rejects a message changed between part requests", async () => {
  fakeRoundcube([
    {
      status: "success",
      index: 0,
      totalChunks: 2,
      sourceHash,
      translation: "Prvi dio",
    },
    {
      status: "success",
      index: 1,
      totalChunks: 2,
      sourceHash: "b".repeat(64),
      translation: "Drugi dio",
    },
  ]);

  await assert.rejects(translateAllParts("42", "INBOX", "bs_BA"));
});

test("preserves the too-large error for a clear user message", async () => {
  fakeRoundcube([{ status: "error", code: "too_large" }]);

  await assert.rejects(translateAllParts("42", "INBOX", "bs_BA"), {
    code: "too_large",
  });
});
