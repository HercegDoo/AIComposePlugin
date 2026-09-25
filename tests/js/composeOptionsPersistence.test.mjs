import assert from "node:assert/strict";
import test from "node:test";
import {
  composeOptionsPostData,
  initComposeOptionPersistence,
} from "../../assets/src/compose/emailHelpers/composeOptionsPersistence.mjs";

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
