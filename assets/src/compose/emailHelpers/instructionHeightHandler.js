export function handleInstructionHeight(){
  const textarea = document.getElementById('aic-instruction');

  let timeoutId;
  const cookieExpirationDate = new Date();
  cookieExpirationDate.setFullYear(cookieExpirationDate.getFullYear() + 1);
  const formattedCookieExpirationDate = new Date(cookieExpirationDate).toUTCString();

  const observer = new ResizeObserver(()=>{

    if (timeoutId) {
      clearTimeout(timeoutId);
    }

    timeoutId = setTimeout(()=>{
    document.cookie = `textareaxHeight=${textarea.getBoundingClientRect().height }; expires=${formattedCookieExpirationDate}; path=/`;
    }, 200);

  });
  observer.observe(textarea)


}
