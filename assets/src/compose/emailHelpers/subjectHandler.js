export function getSubject() {
  const subjectInputElement = document.getElementById("compose-subject");
  return subjectInputElement.value;
}

export function setSubject(subject) {
  const input = document.getElementById("compose-subject");
  input.value = subject;
  input.dispatchEvent(new Event("input", { bubbles: true }));
  input.dispatchEvent(new Event("change", { bubbles: true }));
}
