import {
  composeOptionsPostData,
  initComposeOptionPersistence,
} from "./compose/emailHelpers/composeOptionsPersistence.mjs";
import { translation } from "./utils";

let optionSaveWarningShown = false;

function showOptionSaveWarning() {
  if (!optionSaveWarningShown) {
    optionSaveWarningShown = true;
    rcmail.display_message(translation("ai_options_save_error"), "warning");
  }
}

function loadComposeOptions() {
  return new Promise((resolve, reject) => {
    const request = rcmail.http_get(
      "plugin.aicomposeplugin_GetComposeOptionsAction",
      {}
    );
    if (!request) {
      reject(new Error("Could not load compose options"));
      return;
    }

    request
      .done((result) => {
        if (result?.status === "success" && result.options) {
          resolve(result.options);
        } else {
          reject(new Error("Could not load compose options"));
        }
      })
      .fail(reject);
  }).catch((error) => {
    rcmail.display_message(translation("ai_options_load_error"), "warning");
    throw error;
  });
}

function saveComposeOptions(options) {
  const data = composeOptionsPostData(options);
  return new Promise((resolve, reject) => {
    rcmail
      .http_post("plugin.aicomposeplugin_SaveComposeOptionsAction", data)
      .done((result) => {
        if (result?.status === "success") {
          optionSaveWarningShown = false;
          resolve();
        } else {
          reject(new Error("Could not save compose options"));
        }
      })
      .fail(reject);
  }).catch(() => {
    showOptionSaveWarning();
  });
}

let initialized = false;

function initComposeOptions() {
  if (initialized) return;
  initialized = true;
  initComposeOptionPersistence(
    document,
    saveComposeOptions,
    loadComposeOptions
  );
}

if (typeof rcmail !== "undefined" && rcmail.addEventListener) {
  rcmail.addEventListener("init", initComposeOptions);
}

if (document.readyState === "complete") {
  initComposeOptions();
} else {
  window.addEventListener("load", initComposeOptions, { once: true });
}
