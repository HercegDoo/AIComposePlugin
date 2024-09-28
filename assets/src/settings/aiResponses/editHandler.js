
export function handleEdit(id){
  document.getElementById('hidden-input').value  = rcmail.env.request_token;
  const editIdInput = document.getElementById('edit-id');
  editIdInput.value = id;

}