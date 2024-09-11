export function getPreviousConversation(){
    let previousConversation = "";
    //Uzimanje prethodnog maila ako postoji
    const previousConversationTextareaElement = document.querySelector(
        "#composebodycontainer textarea"
    );
    if (previousConversationTextareaElement.value.trim() !== "") {
        previousConversation = previousConversationTextareaElement.value.trim();
    }

    return previousConversation;
}