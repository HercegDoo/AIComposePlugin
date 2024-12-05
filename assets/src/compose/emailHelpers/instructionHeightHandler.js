export function handleInstructionHeight(){
  const textarea = document.getElementById('aic-instruction');

  const observer = new ResizeObserver(()=>{


    document.cookie = `textareaxHeight=${textarea.getBoundingClientRect().height }; expires=Thu, 18 Dec 2025 12:00:00 UTC; path=/mail`;

  });
  observer.observe(textarea)


}
