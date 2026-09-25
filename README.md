
# AI Email Generator Plugin for Roundcube

## Overview

AIComposePlugin adds AI-assisted email writing, subject suggestions, incoming-message summaries, translation, and reply drafts to Roundcube.

**Current Version:** v3.0.0

**Changelog:** [v3.0.0 and previous releases](CHANGELOG.md)

### Features

- **AI email writing and revision:** Generate a new email, draft a reply, or revise selected text from instructions in Roundcube Compose. Output follows the editor's plain-text or HTML mode; generated HTML is sanitized before insertion.
- **Compose controls that persist:** Choose style, length, creativity, and language, then click **Save as default** in AI Mail Options. All four choices are saved to the signed-in user's Roundcube preferences and restored in new compose windows. Users can also save reusable instructions for common requests.
- **Subject suggestions:** A blank Subject field can be filled when an email is generated. The icon beside Subject requests another suggestion without changing the message body.
- **Personal writing style:** The plugin can use up to three short examples from the signed-in user's Sent folder, preferring messages to the current recipient, to guide the tone and phrasing of new text. Administrators can disable this feature.
- **Incoming email summaries and translation:** Enable hover previews in Settings for a short summary, or open a long message for a summary that adapts to its length. The opened-message summary can be hidden, limited to long messages, or shown for every message. The plugin detects the source language and can show the summary in the active Roundcube language, another installed language, or the original language. Users can reveal the original summary or refresh it; results are cached per user for seven days.
- **Full email translation:** In an opened message, choose a target language and click **Translate full email**. The complete text appears above the unchanged original, with progress for long messages and controls to hide or retry the translation. Users can show or hide this control in Settings independently of summaries.
- **Suggested replies:** When an opened message clearly calls for a response, the plugin offers up to three suggestions. Clicking one opens an editable reply draft; the email is never sent automatically.
- **Provider choices:** OpenAI and Gemini support email composition, subjects, summaries, and full-message translation. Ollama is available for local incoming-message summaries and translation. OpenAI requests include model-specific handling for GPT-5 and GPT-6.
- **Roundcube integration:** The interface uses the Elastic skin, localized labels, and light and dark theme styling. Compose options, summary and translation visibility, and summary language preferences belong to the signed-in user.
- **Optional diagnostics:** Administrators can enable AI request logging with prompts, model details, timing, and provider-reported token usage. Logging is off by default and can contain private email text when enabled.

## Install

### With Composer (recommended)

Run this command from the **Roundcube root directory**:

```bash
composer require hercegdoo/aicomposeplugin
```

On a Composer-managed Roundcube installation with `roundcube/plugin-installer` enabled, the installer places the package at `plugins/aicomposeplugin/`, where the entry point is `aicomposeplugin.php`. Accept the installer's activation prompt, or add `aicomposeplugin` to `config/config.inc.php`:

```php
$config['plugins'] = ['aicomposeplugin'];
```

The installer copies `plugins/aicomposeplugin/config.inc.php.dist` to `plugins/aicomposeplugin/config.inc.php`. Edit the copied file, choose `aiComposeProvider` (`OpenAI` or `Gemini`), and enter the matching API key. Keep other enabled plugins in the `plugins` array when editing Roundcube's configuration.

### Manually

Place the repository in `plugins/aicomposeplugin/`, install PHP dependencies with `composer install --no-dev` in that directory, copy `config.inc.php.dist` to `config.inc.php`, enter your API key, and enable `aicomposeplugin` in Roundcube's plugin list. The included frontend bundles are ready to use. To rebuild them from source, run `npm ci` and `npm run build:prod` in the plugin directory.

For an existing installation in `plugins/AIComposePlugin/`, rename the directory to `plugins/aicomposeplugin/` and change the plugin name in Roundcube's `config/config.inc.php` to `aicomposeplugin` when upgrading. Keep your existing `config.inc.php` and API key.

### Plesk Roundcube

For a Plesk installation, copy the complete plugin directory (including `src/` and `assets/dist/`) to `/usr/share/psa-roundcube/plugins/aicomposeplugin/`. Run `composer install --no-dev` **inside that plugin directory** if its `vendor/` dependencies are not included. Enable `aicomposeplugin` in `/usr/share/psa-roundcube/config/config.local.php` while retaining the other enabled plugins. Plesk's host Composer autoloader may not know classes from manually copied plugins; the plugin entry point registers its own `src/` mapping to handle that layout. See [Plesk's Roundcube plugin instructions](https://support.plesk.com/hc/en-us/articles/24152701483799-How-to-enable-Roundcube-plugins-in-Plesk-for-Linux).
 
## Usage
1. **Compose a New Email:**

    - Open Roundcube and start composing a new email.
    - A new button labeled **Generate** will appear on the Compose page.

2. **Open the AI Prompt:**

    - Click on the **Generate Email** button.
    - A prompt will appear where you can:
        - Enter instructions for the email.
        - Choose the style, length, creativity, and language of the email.

3. **Generate Email:**

    - Provide the necessary details, and click **Generate**.
    - The AI will generate an email based on the provided input.
    - The generated email can be inserted into the Compose window, ready for further editing or immediate sending.
    - If the Subject field is empty, the plugin also suggests a subject and fills that field. An existing subject is preserved.

    - After changing AI style, length, creativity, or language in Compose, click **Save as default** in AI Mail Options. A confirmation appears only after the server accepts all four choices. New compose windows and page reloads use the saved server preferences.

4. **Suggest a New Subject:**

    - Click the lightbulb icon beside the Subject field to generate another suggestion from the current email text. Hover over it to see the translated **Suggest subject** tooltip. If the editor is empty, the plugin uses the entered instructions.
    - This updates only the Subject field. Each suggestion can require another provider request.
    - The button label follows the Roundcube interface language. The language selected in AI Mail Options controls the generated subject text.
  
<img  alt="image" src="https://github.com/user-attachments/assets/e452b8df-b4da-4268-aaf0-3e7f14f0c1f5" />
<img  alt="image" src="https://github.com/user-attachments/assets/ba9aa09f-6ce5-4d0c-ac21-cf993d74952d" />



## Maintaining prompts and providers

Prompt wording lives in `src/AIEmailService/Prompt/EmailPromptBuilder.php`. It builds both the system instruction and the user instruction for new emails and selected-text revisions. Change wording there and update `tests/AIEmailService/Prompt/EmailPromptBuilderTest.php`.

When generating a reply, Roundcube's existing quoted conversation stays in the editor. The prompt uses it only as context and asks the provider to return new reply text. The compose frontend removes a copied quote from the generated fragment before inserting it; when the current signature is already present, it also removes any duplicated closing greeting. The existing Roundcube quote and signature remain in place.

By default, email generation also reads up to three short examples from the signed-in user's configured Sent folder. Messages sent from the currently selected compose identity to the current recipient are preferred; if there are too few, recent messages from the same identity fill the remaining slots. Quoted reply chains and common signatures are removed, attachments are not read, and each example is limited to 1,200 characters. The examples are sent to the compose AI provider as style context only. The prompt tells the model to match tone and phrasing habits without copying old facts or names. Set `aiComposeSentStyleEnabled = false` in the plugin configuration to disable this lookup. An unavailable Sent folder does not block generation. Sent examples are not cached; they appear in the debug log only when `aiDebugLogging` is enabled.

Subject wording lives in `src/AIEmailService/Prompt/SubjectPromptBuilder.php`. The same provider handles both email and subject prompts; `AIEmail::generateSubject()` cleans the returned line before it reaches Roundcube.

`AIEmail::generate()` builds an `EmailPrompt` before calling the configured provider. It also accepts a `PromptBuilderInterface` implementation as an optional second argument when a different prompt strategy is needed. A new provider implements `InterfaceProvider::generateEmail(RequestData $requestData, EmailPrompt $prompt)` and translates those instructions into its API's request format. Provider classes handle transport and responses; they do not need their own copy of the email prompt. Register a new provider in `Settings::setProvider()`, then configure `aiComposeProvider` and `aiProvider<ProviderName>Config`; task initialization loads that configuration by provider name.

Incoming summary wording lives in `src/AIEmailService/Summary/SummaryPromptBuilder.php`. Full-message translation instructions live in `src/AIEmailService/Translation/TranslationService.php`. These providers implement `CompletionProviderInterface`; OpenAI and Gemini are shared with compose, while Ollama is available for local incoming-message AI requests.

## AI request diagnostics

Set `$config['aiDebugLogging'] = true;` in the plugin's `config.inc.php` to record AI requests in Roundcube's `aicomposeplugin_ai.log` (or the configured Roundcube log driver). The setting is `false` by default. Each provider call produces a `request` record with a generated `request_id`, user ID, operation (`email`, `subject`, or `summary`), model, request options, and the complete system and user prompts. A matching `result` record includes status, duration, HTTP status when available, finish reason, and token counts reported by the provider. OpenAI and Gemini report prompt, completion, and total tokens; Gemini can also report thinking tokens. Ollama reports prompt and completion counts, from which the plugin computes a total. Missing provider usage is left empty, not estimated. A summary served from cache does not create a provider request or token record.

The log contains private email text, previous conversation, and possibly Sent style examples. A generated email reused as input for subject generation can also appear in the subsequent subject prompt. Restrict access to Roundcube's log destination and disable `aiDebugLogging` after troubleshooting. Configured API credentials and full provider response payloads are not dumped. For file logging, the destination follows Roundcube's `log_dir` and `log_file_ext` settings; with the default extension the filename is `aicomposeplugin_ai.log`.

## Incoming email summaries

With `aiSummaryEnabled = true`, opened-message summaries adapt to the message length: messages under 100 words get one sentence, messages from 100 to 299 words get up to two, and messages of 300 words or more get up to three. By default, the summary appears only when the extracted message body contains at least 300 words, including quoted conversation when present. Shorter messages show no summary card and are not sent to the AI provider for summarization. Enable the hover preview in Settings to show a short translated summary when hovering over a message row. The opened-message summary includes earlier conversation context only when needed to explain the latest request or decision. **Show original** reveals the summary in the detected source language, and **Translate again** requests a fresh result. By default, the plugin uses the active Roundcube session language; changing the UI language yields a new cached translation. Summaries are generated on demand and cached for seven days in Roundcube's per-user database cache. Refresh bypasses that cache.

In **Settings → AICompose Settings**, set **AI summary on message hover** to Show or Hide; it defaults to Hide. Set **AI summary in opened messages** to **Hide**, **Only long messages/conversations**, or **Always show**. **Only long messages/conversations** is the default for users without a saved choice. Previously saved Show and Hide choices remain in effect. Hiding the opened-message summary also removes its reply suggestions; the long-message mode offers suggestions only for qualifying messages. You can choose the active Roundcube interface language (default), any language installed in that Roundcube instance, or the message's original language without translation. The language choice applies to both enabled views, and the cache separates these choices. The former style, length, creativity, and compose-language controls have moved out of Settings; choose them in AI Mail Options and click **Save as default** to save them to your Roundcube account. The saved choices are used on the next compose page load, including on another browser or device.

When an opened message clearly calls for a reply, the same AI request may return one to three short reply suggestions in the summary's target language (or the interface language when translation is off). Informational or ambiguous messages show no suggestions. Clicking a suggestion opens Roundcube's normal reply composer and generates a draft with the selected instruction; the user can edit it before sending. The suggestion is passed through a short-lived, one-time token in the Roundcube session. The reply language follows the incoming message when that language is available in the compose language selector.

By default, summaries use the configured OpenAI key and model. Set `aiSummaryProvider = 'Gemini'` to use Gemini instead; `aiSummaryGeminiConfig` inherits `aiProviderGeminiConfig` and can override `model`, `apiKey`, and `maxTokens`. `aiSummaryOpenAIConfig` similarly overrides the compose OpenAI configuration. Set `aiSummaryProvider = 'Ollama'` and configure `aiSummaryOllamaConfig['model']` to use a local Ollama server; its default URL is `http://127.0.0.1:11434/api/chat`. The selected provider receives up to 12,000 characters from the incoming message plus its subject, so choose a local provider if the message must stay on your server. The cache stores only the generated summaries, detected language, and translation. Set `aiSummaryEnabled = false` to disable this feature.

## Full email translation

Opened messages have a separate **Translate full email** card. Choose any language installed in Roundcube and click the button. The initial choice follows the summary language setting when it names a specific language; otherwise it follows the active Roundcube interface language. The original email remains visible underneath, and **Hide translation** lets you collapse the translated text. Changing the target language clears the previous result. Translation starts only when clicked, independently of summary visibility and `aiSummaryEnabled`.

In **Settings → AICompose Settings**, set **Full email translation in opened messages** to **Show** or **Hide**. It is shown by default, including for existing users. Hiding it removes the card and blocks translation requests for that user; it does not change either summary view. The administrator can disable it for everyone with `aiTranslationEnabled = false`.

The action reads the signed-in user's message from Roundcube, converts HTML email to readable text, and translates the subject and message text in 3,500-character parts. Progress is shown while the parts are processed; the translated result appears only when every part succeeds. The action rejects messages over 60,000 text characters or 1 MB of raw body data instead of showing an incomplete translation. Images and attachments are not translated. Full translations are kept only in the open browser page, not in the summary cache. With `aiDebugLogging` enabled, translation prompts containing email text are written to the AI log. The selected `aiSummaryProvider` (OpenAI, Gemini, or Ollama) handles translation; set `aiTranslationEnabled = false` in `config.inc.php` to remove the control.

## OpenAI models

Set `aiProviderOpenAIConfig['model']` in `config.inc.php` to an API model ID. Supported examples are `gpt-4.1`, `gpt-5`, `gpt-6-astra`, `gpt-6-sol`, and `gpt-6-luna`. The ChatGPT product names and plain `gpt-6` are not valid API model IDs. Your OpenAI API project must have access to the selected model.

The OpenAI provider uses the Chat Completions API for all of these models. GPT-5 and GPT-6 requests send shared instructions in a `developer` message, use `max_completion_tokens`, and omit `temperature`, which those reasoning models may reject. For `gpt-5`, the provider requests `minimal` reasoning effort; for the three GPT-6 models, it requests `low`. The creativity setting controls temperature only for older models such as `gpt-4.1`; it has no effect with GPT-5 or GPT-6. A custom `apiUrl` must point to a Chat Completions-compatible endpoint.

`aiDefaultMaxTokens` limits both visible output and reasoning tokens on GPT-5 and GPT-6. If generation stops before returning an email, increase this value. The provider reports this case when the API returns a `length` finish reason.

## Gemini provider

Set `aiComposeProvider = 'Gemini'` in `config.inc.php`, then put a Google AI Studio API key and a model ID such as `gemini-3.8-flash` in `aiProviderGeminiConfig`. Email generation and subject suggestions use the same shared prompt builders as OpenAI. Set `aiSummaryProvider = 'Gemini'` to use it for incoming summaries as well. The provider calls Google's `generateContent` REST endpoint with the API key in the `x-goog-api-key` header. It validates model IDs before adding them to the URL and verifies TLS certificates.

Gemini 3 requests use low thinking by default to leave room for the visible email within `aiDefaultMaxTokens`; set `thinkingLevel` to `medium` or `high` in the Gemini provider configuration if needed. The creativity control changes temperature only for older Gemini models, since Google recommends default sampling for Gemini 3. The Gemini response's `usageMetadata` is included in the optional AI debug log when available. A blocked or empty response is reported as a provider error.

## HTML email generation

When Roundcube's compose editor is in HTML mode, the plugin asks the AI for an HTML fragment and inserts its formatting into TinyMCE. The requested short, medium, or long length refers to visible words only; HTML tags and attributes are excluded from that word range. This is a prompt instruction, so the model may still occasionally miss the range. The API output token limit still includes markup.

Generated HTML is limited to common TinyMCE email formatting: paragraphs, line breaks, emphasis, underline, lists, quotes, headings, links, and simple tables. Only `http`, `https`, and `mailto` link destinations are kept. Scripts, images, styles, and other markup are removed before insertion. In plain-text mode the AI is asked for plain text, and any HTML response is converted to text if the editor mode changes while a request is running.
