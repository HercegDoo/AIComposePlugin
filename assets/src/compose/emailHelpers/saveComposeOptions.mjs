import { composeOptionsPostData } from "./composeOptionsPersistence.mjs";

const action = "plugin.aicomposeplugin_SaveComposeOptionsAction";

export async function postComposeOptions(rcmail, fetchRequest, options) {
  const fields = composeOptionsPostData(options).data.aic;
  const body = new URLSearchParams({ _remote: "1" });
  for (const [field, value] of Object.entries(fields)) {
    body.append(`data[aic][${field}]`, value);
  }

  const response = await fetchRequest(rcmail.url(action), {
    method: "POST",
    credentials: "same-origin",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8",
      "X-Roundcube-Request": rcmail.env.request_token,
    },
    body: body.toString(),
  });
  if (!response.ok) {
    throw new Error(`Could not save compose options (${response.status})`);
  }

  const result = await response.json();
  if (result?.status !== "success") {
    throw new Error("Could not save compose options");
  }
}
