import assert from "node:assert/strict";
import test from "node:test";
import {
  findPlainSignature,
  stripGeneratedClosing,
} from "../../assets/src/compose/emailHelpers/signatureUtils.mjs";

test("finds the current plain signature as complete lines", () => {
  const body = "Odgovor\n\n-- \nSrdačan pozdrav,\nMeho\n\n> Stari odgovor";
  const signature = "-- \nSrdačan pozdrav,\nMeho";

  assert.deepEqual(findPlainSignature(body, signature), {
    start: 9,
    end: 34,
  });
  assert.equal(findPlainSignature(body, ""), null);
  assert.equal(findPlainSignature(`> ${signature}`, signature), null);
});

test("removes a duplicated closing and name but retains the opening greeting", () => {
  assert.equal(
    stripGeneratedClosing(
      "Poštovani,\n\nHvala na poruci.\n\nSrdačan pozdrav,\nMeho",
      "Meho"
    ),
    "Poštovani,\n\nHvala na poruci."
  );
});

test("removes a copied signature and leaves ordinary final sentences intact", () => {
  assert.equal(
    stripGeneratedClosing(
      "Hello,\n\nPlease review the file.\n\nKind regards,\nMeho",
      "Meho",
      "Kind regards,\nMeho"
    ),
    "Hello,\n\nPlease review the file."
  );
  assert.equal(
    stripGeneratedClosing("Hello,\n\nPlease review the file.", "Meho"),
    "Hello,\n\nPlease review the file."
  );
});
