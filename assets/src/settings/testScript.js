
console.log('tu');
document.addEventListener('DOMContentLoaded', ()=>{
  console.log('tu');
  document.querySelector('a.create').addEventListener('click', ()=>{
    console.log('tu');
    rcmail.http_get('plugin.customcreate', {}).done(()=>{

    })
  })
})