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
    document.cookie = `textareaHeight=${textarea.getBoundingClientRect().height }; expires=${formattedCookieExpirationDate}; path=/`;
    }, 200);

  });
  observer.observe(textarea)


}

export function expandInstructionHeightBasedOnInput(){
  const textarea = document.getElementById('aic-instruction');
  textarea.style.overflow = "hidden";

  const hiddenDiv = document.createElement('div');
  hiddenDiv.style.position = 'absolute';
  hiddenDiv.style.visibility = 'hidden';
  hiddenDiv.style.whiteSpace = 'pre-wrap';
  hiddenDiv.style.overflowWrap = 'break-word';
  document.body.appendChild(hiddenDiv);


  const textareaLineHeight = parseFloat(window.getComputedStyle(textarea).lineHeight);

  textarea.addEventListener('input', (event) => {
    syncStyles(textarea, hiddenDiv);
    const textareaHeight = textarea.getBoundingClientRect().height;

    const value = textarea.value;
    const cursorPosition = textarea.selectionStart;

    hiddenDiv.textContent = value.substring(0, cursorPosition);

    const totalLines = Math.floor(textarea.scrollHeight / textareaLineHeight);
    if(totalLines === 2 && textareaShouldExpand(textareaLineHeight,textareaHeight)){
      textarea.style.height = textareaHeight + textareaLineHeight  + `px`;
      textarea.style.overflow = "auto";
    }
    else if(totalLines === 1){
      textarea.style.overflow = "hidden";
    }
  });
}

function textareaShouldExpand(lineHeight, textareaHeight){
  return textareaHeight <= lineHeight * 2;
}

function syncStyles(source, target) {
  const computedStyles = window.getComputedStyle(source);
  target.style.font = computedStyles.font;
  target.style.padding = computedStyles.padding;
  target.style.border = computedStyles.border;
  target.style.width = `${source.offsetWidth}px`;
  target.style.lineHeight = computedStyles.lineHeight;
}
