// Roundcube inserts the current identity's exact text signature in plain mode.
export function findPlainSignature(body, signature) {
  if (!signature || !signature.trim()) {
    return null;
  }

  const text = signature.replace(/\r\n/g, "\n");
  let start = body.indexOf(text);

  while (start !== -1) {
    const end = start + text.length;
    if (
      (start === 0 || body[start - 1] === "\n") &&
      (end === body.length || body[end] === "\n")
    ) {
      return { start, end };
    }

    start = body.indexOf(text, start + 1);
  }

  return null;
}

const quoteHeader =
  /^(?:>+|(?:on|am|le|el|il) .{1,160} (?:wrote|schrieb|a écrit|escribió|ha scritto)\s*:|.{1,160}\b(?:je napisao(?:\/la)?|je napisala|je pisao|je pisala)\s*:|[-_]{2,}\s*(?:original message|izvorna poruka)|(?:from|od)\s*:)/iu;

function normalizeQuoteText(text) {
  return text.replace(/\s+/g, " ").trim().toLocaleLowerCase();
}

export function stripGeneratedConversation(email, previousConversation = "") {
  if (!previousConversation.trim()) return email.trim();

  const source = normalizeQuoteText(previousConversation);
  const lines = email.replace(/\r\n/g, "\n").split("\n");
  let cutoff = lines.length;

  for (let index = 0; index < lines.length; index++) {
    const line = lines[index].trim();
    if (quoteHeader.test(line)) {
      cutoff = index;
      break;
    }

    const normalized = normalizeQuoteText(line);
    if (
      normalized.length >= 60 &&
      normalized.split(" ").length >= 8 &&
      source.includes(normalized)
    ) {
      cutoff = index;
      break;
    }
  }

  return lines.slice(0, cutoff).join("\n").trim();
}

const closing =
  /^(?:srdač(?:an|ni) pozdrav(?:i)?|srdačno|lijep(?:i)? pozdrav(?:i)?|lep pozdrav|topli pozdravi|pozdrav(?:i)?|s poštovanjem|uz poštovanje|sve najbolje|best regards|kind regards|warm regards|regards|sincerely|yours sincerely|yours faithfully|best|cheers|mit freundlichen grüßen|freundliche grüße|viele grüße|met vriendelijke groet(?:en)?|vriendelijke groet(?:en)?|cordialement|bien cordialement|saludos cordiales|un saludo|atentamente|distinti saluti|cordiali saluti)[,.!\s]*$/iu;

export function stripGeneratedClosing(
  email,
  senderName = "",
  signatureText = ""
) {
  const lines = email.replace(/\r\n/g, "\n").trimEnd().split("\n");
  const signatureLines = signatureText
    .replace(/\r\n/g, "\n")
    .split("\n")
    .map((line) => line.trim())
    .filter(Boolean);

  const trimBlankLines = () => {
    while (lines.length && !lines[lines.length - 1].trim()) {
      lines.pop();
    }
  };

  if (
    signatureLines.length &&
    lines.length >= signatureLines.length &&
    signatureLines.every(
      (line, index) =>
        line.toLocaleLowerCase() ===
        lines[lines.length - signatureLines.length + index]
          .trim()
          .toLocaleLowerCase()
    )
  ) {
    lines.splice(-signatureLines.length);
    trimBlankLines();
  }

  if (
    senderName.trim() &&
    lines[lines.length - 1]?.trim().replace(/,$/, "").toLocaleLowerCase() ===
      senderName.trim().toLocaleLowerCase()
  ) {
    lines.pop();
    trimBlankLines();
  }

  if (closing.test(lines[lines.length - 1]?.trim() ?? "")) {
    lines.pop();
  }

  trimBlankLines();
  if (lines[lines.length - 1]?.trim() === "--") {
    lines.pop();
    trimBlankLines();
  }

  return lines.join("\n").trim();
}
