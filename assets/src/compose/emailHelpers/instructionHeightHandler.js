export function handleInstructionHeight(){
  const textarea = document.getElementById('aic-instruction');

  const observer = new ResizeObserver((entries)=>{

    const diff = textarea.getBoundingClientRect().height - entries[0].contentRect.height;
    if(diff >= 0){
    document.cookie = `textareaxHeight=${entries[0].contentRect.height + diff}; expires=Thu, 18 Dec 2025 12:00:00 UTC; path=/mail`;
    }
  });
  observer.observe(textarea)


}
