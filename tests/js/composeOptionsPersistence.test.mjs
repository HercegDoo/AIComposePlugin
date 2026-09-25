import assert from "node:assert/strict";
import { readFileSync } from "node:fs";
import test from "node:test";
import {
  composeOptionsPostData,
  initComposeOptionPersistence,
} from "../../assets/src/compose/emailHelpers/composeOptionsPersistence.mjs";
import { postComposeOptions } from "../../assets/src/compose/emailHelpers/saveComposeOptions.mjs";

function select(id, value, values = [value]) {
  return {
    id,
    tagName: "SELECT",
    value,
    options: values.map((optionValue) => ({ value: optionValue })),
  };
}

function root(...selects) {
  const elements = new Map();
  let onChange;
  const container = {
    querySelector(selector) {
      return elements.get(selector.slice(1)) ?? null;
    },
    addEventListener(name, listener) {
      if (name === "change") onChange = listener;
    },
    add(element) {
      elements.set(element.id, element);
      element.change = (nextValue) => {
        element.value = nextValue;
        onChange({ target: element });
      };
    },
  };
  for (const element of selects) container.add(element);
  return container;
}

test("keeps Roundcube-rendered defaults until the user changes a select", () => {
  const style = select("aic_style_select", "professional");
  const saved = [];
  initComposeOptionPersistence(root(style), (options) => saved.push(options));

  assert.equal(style.value, "professional");
  assert.deepEqual(saved, []);
});

test("saves changes from controls inserted after initialization", () => {
  const container = root();
  const saved = [];
  initComposeOptionPersistence(container, (options) => saved.push(options));

  const style = select("aic_style_select", "casual");
  container.add(style);
  style.change("professional");

  assert.deepEqual(saved, [{ aic_style_select: "professional" }]);
});

test("ignores changes outside AI compose options", () => {
  const container = root();
  const saved = [];
  initComposeOptionPersistence(container, (options) => saved.push(options));

  const unrelated = select("some_other_select", "first");
  container.add(unrelated);
  unrelated.change("second");

  assert.deepEqual(saved, []);
});

test("posts compose choices using the v2 Roundcube settings field names", () => {
  assert.deepEqual(
    composeOptionsPostData({
      aic_style_select: "professional",
      aic_length_select: "long",
      aic_creativity_select: "high",
      aic_language_select: "german",
    }),
    {
      data: {
        aic: {
          style: "professional",
          length: "long",
          creativity: "high",
          language: "german",
        },
      },
    }
  );
  assert.deepEqual(composeOptionsPostData({ aic_length_select: "long" }), {
    data: { aic: { length: "long" } },
  });
});

test("loads saved Roundcube preferences into a new compose window", async () => {
  const style = select("aic_style_select", "casual", [
    "casual",
    "professional",
  ]);
  const language = select("aic_language_select", "bosnian", [
    "bosnian",
    "german",
  ]);
  const saved = [];
  initComposeOptionPersistence(
    root(style, language),
    (options) => saved.push(options),
    () => Promise.resolve({ style: "professional", language: "German" })
  );

  await new Promise(setImmediate);
  assert.equal(style.value, "professional");
  assert.equal(language.value, "german");
  assert.deepEqual(saved, []);
});

test("applies saved preferences to controls inserted after the response", async () => {
  const container = root();
  let onMutation;
  container.documentElement = {};
  container.defaultView = {
    MutationObserver: class {
      constructor(callback) {
        onMutation = callback;
      }
      observe() {}
      disconnect() {}
    },
  };

  initComposeOptionPersistence(
    container,
    () => {},
    () => Promise.resolve({ style: "professional" })
  );
  await new Promise(setImmediate);

  const style = select("aic_style_select", "casual", [
    "casual",
    "professional",
  ]);
  container.add(style);
  onMutation();
  assert.equal(style.value, "professional");
});

test("saves a changed field immediately while loading other server choices", async () => {
  const style = select("aic_style_select", "casual", [
    "casual",
    "professional",
  ]);
  const length = select("aic_length_select", "medium", ["medium", "long"]);
  const saved = [];
  let finishLoad;
  initComposeOptionPersistence(
    root(style, length),
    (options) => saved.push(options),
    () => new Promise((resolve) => (finishLoad = resolve))
  );

  style.change("professional");
  assert.deepEqual(saved, [{ aic_style_select: "professional" }]);
  finishLoad({ style: "casual", length: "long" });
  await new Promise(setImmediate);
  assert.equal(style.value, "professional");
  assert.equal(length.value, "long");
  assert.deepEqual(saved, [{ aic_style_select: "professional" }]);
});

test("sends only the changed field on each change", async () => {
  const style = select("aic_style_select", "casual");
  const language = select("aic_language_select", "bosnian");
  const saved = [];
  initComposeOptionPersistence(root(style, language), (options) => {
    saved.push(options);
  });

  style.change("professional");
  await new Promise(setImmediate);
  language.change("german");
  assert.deepEqual(saved, [
    { aic_style_select: "professional" },
    { aic_language_select: "german" },
  ]);
});

test("coalesces rapid changes and saves every changed field last", async () => {
  const style = select("aic_style_select", "casual");
  const length = select("aic_length_select", "medium");
  const saved = [];
  let finishFirst;
  initComposeOptionPersistence(root(style, length), (options) => {
    saved.push(options);
    if (saved.length === 1) {
      return new Promise((resolve) => {
        finishFirst = resolve;
      });
    }
  });

  style.change("professional");
  length.change("long");
  style.change("casual");
  assert.deepEqual(saved, [{ aic_style_select: "professional" }]);

  finishFirst();
  await new Promise(setImmediate);
  assert.deepEqual(saved, [
    { aic_style_select: "professional" },
    { aic_style_select: "casual", aic_length_select: "long" },
  ]);
});

test("a failed request does not stop later changes", async () => {
  const length = select("aic_length_select", "medium");
  const saved = [];
  initComposeOptionPersistence(root(length), (options) => {
    saved.push(options);
    return Promise.reject(new Error("Save failed"));
  });

  length.change("long");
  await new Promise(setImmediate);
  length.change("short");
  await new Promise(setImmediate);
  assert.deepEqual(saved, [
    { aic_length_select: "long" },
    { aic_length_select: "short" },
  ]);
});

test("the compose options template contains no inline JavaScript", () => {
  const html = readFileSync(
    new URL(
      "../../skins/elastic/templates/ai_select_fields.html",
      import.meta.url
    ),
    "utf8"
  );
  assert.match(html, /<div class="select-div">/);
  assert.doesNotMatch(html, /\bonchange=|<script\b/i);
});

test("direct select and parent listeners send only one request", () => {
  const style = select("aic_style_select", "casual");
  const listeners = [];
  style.addEventListener = (name, listener) => listeners.push(listener);
  const wrapper = {
    querySelector(selector) {
      return selector === "#aic_style_select" ? style : null;
    },
    addEventListener(name, listener) {
      listeners.push(listener);
    },
  };
  const container = {
    querySelector(selector) {
      if (selector === ".select-div") return wrapper;
      if (selector === "#aic_style_select") return style;
      return null;
    },
    addEventListener(name, listener) {
      listeners.push(listener);
    },
  };
  const saved = [];
  initComposeOptionPersistence(container, (options) => saved.push(options));

  style.value = "professional";
  const event = { target: style };
  for (const listener of listeners) listener(event);

  assert.deepEqual(saved, [{ aic_style_select: "professional" }]);
});

test("the parent listener saves a select inserted after initialization", () => {
  let onParentChange;
  let onDocumentChange;
  const wrapper = {
    querySelector() {
      return null;
    },
    addEventListener(name, listener) {
      onParentChange = listener;
    },
  };
  const container = {
    querySelector(selector) {
      return selector === ".select-div" ? wrapper : null;
    },
    addEventListener(name, listener) {
      onDocumentChange = listener;
    },
  };
  const saved = [];
  initComposeOptionPersistence(container, (options) => saved.push(options));

  const event = { target: select("aic_language_select", "german") };
  onParentChange(event);
  onDocumentChange(event);

  assert.deepEqual(saved, [{ aic_language_select: "german" }]);
});

test("captures changes before the compose options markup finishes rendering", async () => {
  let onDocumentChange;
  let onDomReady;
  let onParentChange;
  let wrapper = null;
  const container = {
    readyState: "loading",
    querySelector(selector) {
      return selector === ".select-div" ? wrapper : null;
    },
    addEventListener(name, listener, options) {
      if (name === "change") {
        assert.equal(options, true);
        onDocumentChange = listener;
      }
      if (name === "DOMContentLoaded") onDomReady = listener;
    },
  };
  const saved = [];
  initComposeOptionPersistence(container, (options) => saved.push(options));

  const style = select("aic_style_select", "professional");
  onDocumentChange({ target: style });
  wrapper = {
    querySelector() {
      return null;
    },
    addEventListener(name, listener) {
      onParentChange = listener;
    },
  };
  onDomReady();
  const laterChange = { target: select("aic_length_select", "long") };
  onDocumentChange(laterChange);
  onParentChange(laterChange);

  await new Promise(setImmediate);
  assert.deepEqual(saved, [
    { aic_style_select: "professional" },
    { aic_length_select: "long" },
  ]);
});

test("saves on input and ignores the following change for the same value", () => {
  const listeners = {};
  const container = {
    querySelector() {
      return null;
    },
    addEventListener(name, listener) {
      listeners[name] = listener;
    },
  };
  const saved = [];
  initComposeOptionPersistence(container, (options) => saved.push(options));

  const style = select("aic_style_select", "professional");
  listeners.input({ target: style });
  listeners.change({ target: style });

  assert.deepEqual(saved, [{ aic_style_select: "professional" }]);
});

test("posts the changed field directly with Roundcube's CSRF header", async () => {
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
    { aic_style_select: "casual" }
  );

  assert.equal(
    requests[0].url,
    "/roundcube/?_task=mail&_action=plugin.aicomposeplugin_SaveComposeOptionsAction"
  );
  assert.equal(requests[0].options.method, "POST");
  assert.equal(requests[0].options.credentials, "same-origin");
  assert.equal(requests[0].options.headers["X-Roundcube-Request"], "test-token");
  assert.deepEqual(
    Object.fromEntries(new URLSearchParams(requests[0].options.body)),
    { _remote: "1", "data[aic][style]": "casual" }
  );
});

test("reports a server rejection of the preference change", async () => {
  await assert.rejects(
    postComposeOptions(
      {
        env: { request_token: "test-token" },
        url: () => "/roundcube/",
      },
      async () => ({ ok: true, json: async () => ({ status: "error" }) }),
      { aic_length_select: "long" }
    ),
    /Could not save compose options/
  );
});

test("saves choices changed before a late bundle binds its listeners", () => {
  const style = select("aic_style_select", "casual");
  style.addEventListener = () => {};
  const wrapper = {
    querySelector: (selector) =>
      selector === "#aic_style_select" ? style : null,
    addEventListener() {},
  };
  const container = {
    querySelector: (selector) =>
      selector === ".select-div" ? wrapper : null,
    addEventListener() {},
  };
  const saved = [];

  initComposeOptionPersistence(
    container,
    (options) => saved.push(options),
    null,
    { style: "professional" }
  );

  assert.deepEqual(saved, [{ aic_style_select: "casual" }]);
});
