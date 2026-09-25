const action = "plugin.aicomposeplugin_SummarizeMessageAction";

export function checkMessageEligibility(uid, mailbox) {
  return new Promise((resolve, reject) => {
    rcmail
      .http_post(action, { uid, mailbox, view: "message", check: "1" })
      .done((data) => {
        if (data?.status === "success" && typeof data.eligible === "boolean") {
          resolve(data.eligible);
        } else {
          reject(new Error("Summary eligibility unavailable"));
        }
      })
      .fail(reject);
  });
}
