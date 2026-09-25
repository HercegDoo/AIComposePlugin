import assert from "node:assert/strict";
import { readFileSync } from "node:fs";
import test from "node:test";
import {
  collectComposeOptions,
  composeOptionsPostData,
  initComposeOptionsSaveButton,
} from "../../assets/src/compose/emailHelpers/composeOptionsPersistence.mjs";
import { postComposeOptions } from "../../assets/src/compose/emailHelpers/saveComposeOptions.mjs";

function select(id, value) {
  return { id, tagName: "SELECT", value };
}

function composePage() {
  const elements = new Map([
    ["aic_style_select", select("aic_style_select", "professional")],
    ["aic_length_select", select("aic_length_select", "medium")],
    ["aic_creativity_select", select("aic_creativity_select", "low")],
    ["aic_language_select", select("aic_language_select", "bosnian")],
  ]);
  let onClick;
  return {
    elements,
    querySelector(selector) {
      return elements.get(selector.slice(1)) ?? null;
    },
    addEventListener(type, callback, capture) {
      assert.equal(type, "click");
      assert.equal(capture, true);
      onClick = callback;
    },
    click(button) {
      let prevented = false;
      const promise = onClick({
        target: {
          closest: (selector) =>
            selector === "#aic-save-compose-options" ? button : null,
        },
        preventDefault() {
          prevented = true;
        },
      });
      return { promise, prevented };
    },
  };
}

test("collects all four current choices or refuses an incomplete sidebar", () => {
  const page = composePage();
  page.elements.get("aic_style_select").value = "casual";
  assert.deepEqual(collectComposeOptions(page), {
    aic_style_select: "casual",
    aic_length_select: "medium",
    aic_creativity_select: "low",
    aic_language_select: "bosnian",
  });

  page.elements.delete("aic_language_select");
  assert.equal(collectComposeOptions(page), null);
});

test("posts all choices using the Roundcube preference field names", () => {
  const data = composeOptionsPostData(collectComposeOptions(composePage()));
  assert.deepEqual(data, {
    data: {
      aic: {
        style: "professional",
        length: "medium",
        creativity: "low",
        language: "bosnian",
      },
    },
  });
});

test("a changed select is saved only after clicking Save as default", async () => {
  const page = composePage();
  const saved = [];
  const messages = [];
  initComposeOptionsSaveButton(
    page,
    async (options) => saved.push(options),
    () => messages.push("saved"),
    () => messages.push("error")
  );

  page.elements.get("aic_style_select").value = "casual";
  assert.deepEqual(saved, []);
  const button = { disabled: false };
  const click = page.click(button);
  assert.equal(click.prevented, true);
  assert.equal(button.disabled, true);
  await click.promise;

  assert.deepEqual(saved, [
    {
      aic_style_select: "casual",
      aic_length_select: "medium",
      aic_creativity_select: "low",
      aic_language_select: "bosnian",
    },
  ]);
  assert.deepEqual(messages, ["saved"]);
  assert.equal(button.disabled, false);
});

test("repeated clicks during one request send only one POST", async () => {
  const page = composePage();
  const saved = [];
  let finish;
  initComposeOptionsSaveButton(page, (options) => {
    saved.push(options);
    return new Promise((resolve) => (finish = resolve));
  });

  const button = { disabled: false };
  const first = page.click(button);
  const second = page.click(button);
  assert.equal(saved.length, 1);
  assert.equal(second.prevented, true);
  finish();
  await first.promise;
  assert.equal(button.disabled, false);
});

test("a failed request reports an error and allows retry", async () => {
  const page = composePage();
  const messages = [];
  let attempts = 0;
  initComposeOptionsSaveButton(
    page,
    async () => {
      if (++attempts === 1) throw new Error("Server rejected the save");
    },
    () => messages.push("saved"),
    () => messages.push("error")
  );

  const button = { disabled: false };
  await page.click(button).promise;
  assert.equal(button.disabled, false);
  await page.click(button).promise;
  assert.deepEqual(messages, ["error", "saved"]);
});

test("the button remains outside inline JavaScript and does not submit the mail form", () => {
  const html = readFileSync(
    new URL(
      "../../skins/elastic/templates/ai_select_fields.html",
      import.meta.url
    ),
    "utf8"
  );
  assert.match(html, /<button id="aic-save-compose-options" type="button"/);
  assert.doesNotMatch(html, /\bonchange=|\bonclick=|<script\b/i);
});

test("the save request includes all fields and Roundcube's CSRF header", async () => {
  const requests = [];
  const roundcube = {
    env: { request_token: "test-token" },
    url: (action) => `/roundcube/?_task=mail&_action=${action}`,
  };
  await postComposeOptions(
    roundcube,
    async (url, options) => {
      requests.push({ url, options });
      return { ok: true, json: async () => ({ status: "success" }) };
    },
    collectComposeOptions(composePage())
  );

  assert.equal(
    requests[0].url,
    "/roundcube/?_task=mail&_action=plugin.aicomposeplugin_SaveComposeOptionsAction"
  );
  assert.equal(requests[0].options.method, "POST");
  assert.equal(requests[0].options.credentials, "same-origin");
  assert.equal(
    requests[0].options.headers["X-Roundcube-Request"],
    "test-token"
  );
  assert.deepEqual(
    Object.fromEntries(new URLSearchParams(requests[0].options.body)),
    {
      _remote: "1",
      "data[aic][style]": "professional",
      "data[aic][length]": "medium",
      "data[aic][creativity]": "low",
      "data[aic][language]": "bosnian",
    }
  );
});

test("a rejected server response is not treated as a save", async () => {
  await assert.rejects(
    postComposeOptions(
      { env: { request_token: "test-token" }, url: () => "/roundcube/" },
      async () => ({ ok: true, json: async () => ({ status: "error" }) }),
      collectComposeOptions(composePage())
    ),
    /Could not save compose options/
  );
});
