import assert from "node:assert/strict";
import test from "node:test";
import { initComposeOptionPersistence } from "../../assets/src/compose/emailHelpers/composeOptionsPersistence.mjs";

function select(id, value) {
  const listeners = new Map();
  return {
    id,
    tagName: "SELECT",
    value,
    addEventListener(name, listener) {
      listeners.set(name, listener);
    },
    change(nextValue) {
      this.value = nextValue;
      listeners.get("change")();
    },
  };
}

function root(...selects) {
  const elements = new Map(selects.map((element) => [element.id, element]));
  return {
    querySelector(selector) {
      return elements.get(selector.slice(1)) ?? null;
    },
  };
}

test("keeps Roundcube-rendered defaults until the user changes a select", () => {
  const style = select("aic_style_select", "professional");
  const saved = [];
  initComposeOptionPersistence(root(style), (options) => saved.push(options));

  assert.equal(style.value, "professional");
  assert.deepEqual(saved, []);
});

test("sends the complete selection on each change", async () => {
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
    { aic_style_select: "professional", aic_language_select: "bosnian" },
    { aic_style_select: "professional", aic_language_select: "german" },
  ]);
});

test("coalesces rapid changes and saves the latest complete selection last", async () => {
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
  assert.deepEqual(saved, [
    { aic_style_select: "professional", aic_length_select: "medium" },
  ]);

  finishFirst();
  await new Promise(setImmediate);
  assert.deepEqual(saved, [
    { aic_style_select: "professional", aic_length_select: "medium" },
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
