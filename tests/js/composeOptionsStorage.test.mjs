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

test("remembers all visible choices when a new compose window opens", () => {
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
    {
      aic_style_select: "professional",
      aic_length_select: "medium",
      aic_creativity_select: "high",
    }
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
  assert.equal(nextLength.value, "medium");
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

test("restores local choices after a successful Roundcube save", async () => {
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
  length.change("long");
  creativity.change("high");
  language.change("german");
  await Promise.resolve();
  assert.deepEqual(saved[3], {
    aic_style_select: "professional",
    aic_length_select: "long",
    aic_creativity_select: "high",
    aic_language_select: "german",
  });
  // Older versions set this marker and then skipped local choices on reload.
  store.setItem("aicomposeplugin.composeOptions.server.v1.42", "1");

  const serverStyle = select("aic_style_select", "casual", [
    "casual",
    "professional",
  ]);
  const serverLength = select("aic_length_select", "medium", [
    "medium",
    "long",
  ]);
  const serverCreativity = select("aic_creativity_select", "medium", [
    "medium",
    "high",
  ]);
  const serverLanguage = select("aic_language_select", "bosnian", [
    "bosnian",
    "german",
  ]);
  initComposeOptionPersistence(
    root(serverStyle, serverLength, serverCreativity, serverLanguage),
    store,
    "42"
  );
  assert.equal(serverStyle.value, "professional");
  assert.equal(serverLength.value, "long");
  assert.equal(serverCreativity.value, "high");
  assert.equal(serverLanguage.value, "german");
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

  const nextStyle = select("aic_style_select", "casual", [
    "casual",
    "professional",
  ]);
  initComposeOptionPersistence(root(nextStyle), store, "42");
  assert.equal(nextStyle.value, "professional");
});

test("keeps the latest choices while Roundcube saves are still pending", async () => {
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
  const nextStyle = select("aic_style_select", "casual", [
    "casual",
    "professional",
  ]);
  const nextLength = select("aic_length_select", "medium", [
    "medium",
    "long",
  ]);
  initComposeOptionPersistence(root(nextStyle, nextLength), store, "42");
  assert.equal(nextStyle.value, "professional");
  assert.equal(nextLength.value, "long");

  complete[0]();
  complete[1]();
  await Promise.resolve();
  assert.equal(nextStyle.value, "professional");
  assert.equal(nextLength.value, "long");
});
