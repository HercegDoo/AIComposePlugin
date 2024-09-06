export function createHelpSection() {
  const helpDiv = document.createElement("div");
  helpDiv.id = "aic-compose-help";
  helpDiv.setAttribute("hidden", "true");

  helpDiv.innerHTML = `<div class="help-header">
      <button type="button" class="btn btn-primary" id="help-back-btn">&lt; Back</button>
      <h3>Help & Examples</h3>
  </div>
<div class="help-content">
  <p>Tell the AI about the email you'd like to write. Include specifics, like names, dates, locations, reasons, etc. You can also ask for creative ideas or explanations. After the email is generated, you will be able to tweak it and insert it into the compose page. Here are a few sample instructions to get you started:</p>
   <ul>
    <li id="xai-help-example-1">
    <span>say hello</span> <a href="javascript:void(0)" class="help-a">(Use this)</a>
    </li>
    <li id="xai-help-example-2">
    <span>today' meeting has been canceled</span> 
    <a href="javascript:void(0)" class="help-a">(Use this)</a>
    </li>
    <li id="xai-help-example-3">
    <span>I set up a meeting with Margy on Wed 3 P.M., prepare a presentation on advantages of using AI in our marketing campaigns to convince her</span>
     <a href="javascript:void(0)" class="help-a">(Use this)</a>
     </li>
    <li id="xai-help-example-4"><span>we decided to add support for AI generated content, explain shortly what it is</span>
     <a href="javascript:void(0)" class="help-a">(Use this)</a>
     </li>
     <li id="xai-help-example-5">
     <span>follow-up on the work assignment from last week, I need it tomorrow</span> 
     <a href="javascript:void(0)" class="help-a">(Use this)</a>
     </li>
     <li id="xai-help-example-6">
     <span>ask for a raise, I didn't get one for over a year</span> 
     <a href="javascript:void(0)" class="help-a">(Use this)</a>
     </li>
     <li id="xai-help-example-7">
     <span>remind to submit the report tomorrow or the team will get in trouble with Margy who is stressed</span> 
     <a href="javascript:void(0)" class="help-a">(Use this)</a>
     </li>
     <li id="xai-help-example-8">
     <span>write the customers about the 20% discount on all products till Tuesday, we sell clothes</span>
      <a href="javascript:void(0)" class="help-a">(Use this)</a>
      </li><li id="xai-help-example-9">
      <span>ask to write Laura and tell her to organize a get-together for Kevin's birthday this weekend</span>
       <a href="javascript:void(0)" class="help-a">(Use this)</a>
       </li>
       <li id="xai-help-example-10">
       <span>ask about the progress with the new website, include 5 ideas for marketing slogans for the front page, we do car repairs</span> 
       <a href="javascript:void(0)" class="help-a" >(Use this)</a>
       </li>
       <li id="xai-help-example-11">
       <span>newsletter about the new vpn features we added: wireguard protocol, port forwarding, kill switch, dedicated IP; explain what they are</span> 
       <a href="javascript:void(0)"  class="help-a">(Use this)</a>
       </li>
       <li id="xai-help-example-12">
       <span>newsletter about a new loyalty program, initial cost is $10 for the card and then up to 20% discounts on most products for a year</span> 
       <a href="javascript:void(0)" class="help-a" >(Use this)</a>
       </li>
       <li id="xai-help-example-13">
       <span>networking email to someone I admire and want to connect with professionally</span> 
       <a href="javascript:void(0)"  class="help-a">(Use this)</a>
       </li>
       <li id="xai-help-example-14">
       <span>a professional thank you for the help with the project yesterday</span>
        <a href="javascript:void(0)" class="help-a" >(Use this)</a>
        </li>
        <li id="xai-help-example-15">
        <span>come up with 10 ideas of strategies for crafting an effective email pitch to the investors, she'll be working on it tomorrow</span> 
        <a href="javascript:void(0)"  class="help-a">(Use this)</a>
        </li>
        <li id="xai-help-example-16">
        <span>a professional resignation email, giving 1 month notice as per contract, the company is called ACME</span> 
        <a href="javascript:void(0)" class="help-a" >(Use this)</a>
       </li>
        <li id="xai-help-example-17">
        <span>compelling email to request a promotion, hinting that I might resign if I don't get it because there are other opportunities</span> 
        <a href="javascript:void(0)" class="help-a" >(Use this)</a>
        </li>
        <li id="xai-help-example-18">
        <span>it was good to meet you on the conference yesterday, I enjoyed our chat, let's stay in touch</span>
         <a href="javascript:void(0)"  class="help-a">(Use this)</a>
         </li>
         </ul>
 </div>`;

  return helpDiv;
}
