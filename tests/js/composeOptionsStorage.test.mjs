import assert from "node:assert/strict";
import test from "node:test";
import { initComposeOptionPersistence } from "../../assets/src/compose/emailHelpers/composeOptionsStorage.mjs";

function select(id, value, values) {
  const listeners = new Map();
  return {
    id,
    tagName: "SELECT",
    value,
    options: values.map((option) => ({ value: option })),
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

function storage() {
  const values = new Map();
  return {
    getItem(key) {
      return values.get(key) ?? null;
    },
    setItem(key, value) {
      values.set(key, value);
    },
    removeItem(key) {
      values.delete(key);
    },
  };
}

test("remembers only changed options and leaves other preference defaults intact", () => {
  const store = storage();
  const style = select("aic_style_select", "casual", [
    "casual",
    "professional",
  ]);
  const length = select("aic_length_select", "medium", ["medium", "long"]);
  const creativity = select("aic_creativity_select", "medium", [
    "medium",
    "high",
  ]);
  initComposeOptionPersistence(root(style, length, creativity), store, "42");

  style.change("professional");
  creativity.change("high");
  assert.deepEqual(
    JSON.parse(store.getItem("aicomposeplugin.composeOptions.v1.42")),
    { aic_style_select: "professional", aic_creativity_select: "high" }
  );

  const nextStyle = select("aic_style_select", "casual", [
    "casual",
    "professional",
  ]);
  const nextLength = select("aic_length_select", "long", ["medium", "long"]);
  const nextCreativity = select("aic_creativity_select", "medium", [
    "medium",
    "high",
  ]);
  initComposeOptionPersistence(
    root(nextStyle, nextLength, nextCreativity),
    store,
    "42"
  );
  assert.equal(nextStyle.value, "professional");
  assert.equal(nextLength.value, "long");
  assert.equal(nextCreativity.value, "high");
});

test("ignores removed options and keeps different Roundcube users separate", () => {
  const store = storage();
  store.setItem(
    "aicomposeplugin.composeOptions.v1.42",
    JSON.stringify({
      aic_style_select: "removed",
      aic_language_select: "german",
    })
  );
  const style = select("aic_style_select", "casual", [
    "casual",
    "professional",
  ]);
  const language = select("aic_language_select", "bosnian", [
    "bosnian",
    "german",
  ]);
  initComposeOptionPersistence(root(style, language), store, "42");
  assert.equal(style.value, "casual");
  assert.equal(language.value, "german");

  const otherLanguage = select("aic_language_select", "bosnian", [
    "bosnian",
    "german",
  ]);
  initComposeOptionPersistence(root(otherLanguage), store, "43");
  assert.equal(otherLanguage.value, "bosnian");
});

test("storage errors do not prevent changing compose options", () => {
  const blocked = {
    getItem() {
      throw new Error("Storage unavailable");
    },
    setItem() {
      throw new Error("Storage unavailable");
    },
  };
  const style = select("aic_style_select", "casual", [
    "casual",
    "professional",
  ]);
  initComposeOptionPersistence(root(style), blocked, "42");
  assert.doesNotThrow(() => style.change("professional"));
  assert.equal(style.value, "professional");
});

test("saves all compose choices to Roundcube and then uses server defaults", async () => {
  const store = storage();
  const style = select("aic_style_select", "casual", [
    "casual",
    "professional",
  ]);
  const length = select("aic_length_select", "medium", ["medium", "long"]);
  const creativity = select("aic_creativity_select", "medium", [
    "medium",
    "high",
  ]);
  const language = select("aic_language_select", "bosnian", [
    "bosnian",
    "german",
  ]);
  const saved = [];
  initComposeOptionPersistence(
    root(style, length, creativity, language),
    store,
    "42",
    (options) => {
      saved.push(options);
      return Promise.resolve();
    }
  );

  style.change("professional");
  await Promise.resolve();
  assert.deepEqual(saved[0], {
    aic_style_select: "professional",
    aic_length_select: "medium",
    aic_creativity_select: "medium",
    aic_language_select: "bosnian",
  });
  assert.equal(
    store.getItem("aicomposeplugin.composeOptions.server.v1.42"),
    "1"
  );

  const serverStyle = select("aic_style_select", "casual", [
    "casual",
    "professional",
  ]);
  initComposeOptionPersistence(root(serverStyle), store, "42");
  assert.equal(serverStyle.value, "casual");
});

test("keeps local choice when saving to Roundcube fails", async () => {
  const store = storage();
  const style = select("aic_style_select", "casual", [
    "casual",
    "professional",
  ]);
  const length = select("aic_length_select", "medium", ["medium"]);
  const creativity = select("aic_creativity_select", "medium", ["medium"]);
  const language = select("aic_language_select", "bosnian", ["bosnian"]);
  initComposeOptionPersistence(
    root(style, length, creativity, language),
    store,
    "42",
    () => Promise.reject(new Error("Unavailable"))
  );

  style.change("professional");
  await Promise.resolve();
  assert.equal(
    store.getItem("aicomposeplugin.composeOptions.server.v1.42"),
    null
  );

  const nextStyle = select("aic_style_select", "casual", [
    "casual",
    "professional",
  ]);
  initComposeOptionPersistence(root(nextStyle), store, "42");
  assert.equal(nextStyle.value, "professional");
});

test("waits for the latest option change before trusting server defaults", async () => {
  const store = storage();
  const style = select("aic_style_select", "casual", [
    "casual",
    "professional",
  ]);
  const length = select("aic_length_select", "medium", ["medium", "long"]);
  const creativity = select("aic_creativity_select", "medium", ["medium"]);
  const language = select("aic_language_select", "bosnian", ["bosnian"]);
  const complete = [];
  initComposeOptionPersistence(
    root(style, length, creativity, language),
    store,
    "42",
    () => new Promise((resolve) => complete.push(resolve))
  );

  style.change("professional");
  length.change("long");
  complete[0]();
  await Promise.resolve();
  assert.equal(
    store.getItem("aicomposeplugin.composeOptions.server.v1.42"),
    null
  );

  complete[1]();
  await Promise.resolve();
  assert.equal(
    store.getItem("aicomposeplugin.composeOptions.server.v1.42"),
    "1"
  );
});
