import assert from "node:assert/strict";
import test from "node:test";
import {
  findPlainSignature,
  stripGeneratedClosing,
  stripGeneratedConversation,
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

test("removes a generated quote before the duplicated closing", () => {
  const previous =
    "Pozdrav, Nećemo uvoditi dodatnu administraciju. Samo napiši u emailu.";
  const generated =
    "Pozdrav Nahide,\n\nPredlažem raspon sati po osobi.\n\nLijep pozdrav,\nAmel\n\n03-09-2026 08:00, Nahid Unkić je napisao/la:\nPozdrav,\nNećemo uvoditi dodatnu administraciju.\nSamo napiši u emailu.";

  const withoutQuote = stripGeneratedConversation(generated, previous);
  assert.equal(
    stripGeneratedClosing(withoutQuote, "Amel"),
    "Pozdrav Nahide,\n\nPredlažem raspon sati po osobi."
  );
});

test("removes quoted lines while preserving an ordinary reply", () => {
  const previous =
    "Please send the complete project report with the final numbers and the timeline tomorrow.";
  const generated =
    "Hello,\n\nI can send the report tomorrow.\n\n> Please send the complete project report with the final numbers and the timeline tomorrow.";

  assert.equal(
    stripGeneratedConversation(generated, previous),
    "Hello,\n\nI can send the report tomorrow."
  );
  assert.equal(
    stripGeneratedConversation(
      "Hello,\n\nI can send the report tomorrow.",
      previous
    ),
    "Hello,\n\nI can send the report tomorrow."
  );
});

test("removes an English reply header without relying on copied text", () => {
  const previous = "Could you send the numbers?";
  const generated =
    "Hello,\n\nHere are the numbers.\n\nOn Tuesday, Chris wrote:\nCould you send the numbers?";

  assert.equal(
    stripGeneratedConversation(generated, previous),
    "Hello,\n\nHere are the numbers."
  );
});

test("removes an unmarked copied paragraph from the previous conversation", () => {
  const quote =
    "We reviewed all of the tasks in the current project and need a breakdown of each person's hours by module.";
  const generated = `Hello,\n\nI will provide the estimate.\n\n${quote}`;

  assert.equal(
    stripGeneratedConversation(generated, quote),
    "Hello,\n\nI will provide the estimate."
  );
});
