import assert from "node:assert/strict";
import test from "node:test";
import { checkMessageEligibility } from "../../assets/src/summary/eligibility.js";

function fakeRoundcube(response) {
  const calls = [];
  globalThis.rcmail = {
    http_post(action, data) {
      calls.push({ action, data });
      return {
        done(callback) {
          queueMicrotask(() => callback(response));
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

test("checks opened-message length without requesting a summary", async () => {
  const calls = fakeRoundcube({ status: "success", eligible: true });

  assert.equal(await checkMessageEligibility("42", "INBOX"), true);
  assert.deepEqual(calls, [
    {
      action: "plugin.aicomposeplugin_SummarizeMessageAction",
      data: { uid: "42", mailbox: "INBOX", view: "message", check: "1" },
    },
  ]);
});

test("returns false for a short message and rejects an invalid response", async () => {
  fakeRoundcube({ status: "success", eligible: false });
  assert.equal(await checkMessageEligibility("42", "INBOX"), false);

  fakeRoundcube({ status: "error" });
  await assert.rejects(checkMessageEligibility("42", "INBOX"));
});
