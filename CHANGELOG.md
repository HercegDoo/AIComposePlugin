# Changelog

## [3.0.0] - 2026-09-25

### Upgrade notes

- **Roundcube plugin name changed:** The plugin directory, entry point, and Roundcube plugin identifier are now lowercase `aicomposeplugin` ([#167](https://github.com/HercegDoo/AIComposePlugin/pull/167)). For an existing manual installation, rename `plugins/AIComposePlugin/` to `plugins/aicomposeplugin/` and update the enabled plugin name in Roundcube's configuration. Keep your existing plugin `config.inc.php` and API key. Composer installations use the lowercase directory automatically.
- **Plugin autoloading:** The entry point now loads plugin classes from its own `src/` directory when the host Composer autoloader does not know about the plugin. Composer autoload dependencies were also adjusted and unused classmap entries removed ([autoload fix](https://github.com/HercegDoo/AIComposePlugin/commit/6f7c069), [dependency cleanup](https://github.com/HercegDoo/AIComposePlugin/commit/6c2558d)).

### New features

- **Gemini provider:** Google Gemini can generate emails and subjects and can also provide incoming-message summaries. Compose and summary configurations can use separate models and API keys; the provider includes response validation and tests ([#180](https://github.com/HercegDoo/AIComposePlugin/pull/180)).
- **Translated incoming summaries:** Hovering over an inbox row shows a short AI summary, while opening a message displays one above the message body. The plugin detects the message language, translates into the selected target language, supports OpenAI or local Ollama for summaries, and caches results per user for seven days ([#168](https://github.com/HercegDoo/AIComposePlugin/pull/168)).
- **Context-aware summaries and reply suggestions:** Opened-message summaries can use one to three sentences according to message length. When the message has a clear reply intent, the AI offers up to three short suggestions. Selecting one opens an editable Roundcube reply draft through a short-lived, one-time session token; it does not send the reply ([#174](https://github.com/HercegDoo/AIComposePlugin/pull/174)).
- **AI-generated subjects:** Generating an email fills an empty Subject field. A separate subject button requests a new suggestion without changing the body or replacing a subject edited while the request is pending ([#166](https://github.com/HercegDoo/AIComposePlugin/pull/166)).
- **HTML email generation:** In Roundcube's HTML editor, generated text can retain supported email formatting after sanitization. Requested email length counts visible words rather than HTML tags; plain-text mode continues to receive plain text ([#165](https://github.com/HercegDoo/AIComposePlugin/pull/165)).
- **Writing-style examples:** Compose can sample up to three messages from the signed-in user's Sent folder, preferring messages to the current recipient, to guide tone and phrasing. This is configurable and does not block generation if Sent is unavailable ([#176](https://github.com/HercegDoo/AIComposePlugin/pull/176)).
- **Optional AI diagnostics:** An administrator can enable request logging for operation, model, duration, provider token usage, and prompts. Logging is disabled by default. Enabled logs contain private email text and must be protected ([#179](https://github.com/HercegDoo/AIComposePlugin/pull/179)).

### Preferences and interface

- **Summary language settings:** Users can keep the active Roundcube interface language, choose another installed language, or show summaries in the message's original language. Compose options now save from the compose screen instead of the old default-option fields in Settings ([#177](https://github.com/HercegDoo/AIComposePlugin/pull/177)).
- **Compose option persistence:** Changes to style, length, creativity, and language are saved to Roundcube account preferences as they are selected and restored on the next compose page load. Frontend persistence tests were added in [#172](https://github.com/HercegDoo/AIComposePlugin/pull/172), with follow-up handling for newly opened compose windows in [#178](https://github.com/HercegDoo/AIComposePlugin/pull/178).
- **Subject button usability:** The subject suggestion control now uses an icon and a Roundcube tooltip, with improved label handling ([#173](https://github.com/HercegDoo/AIComposePlugin/pull/173)).
- **Summary appearance:** Summary cards and hover previews received dark-mode styling ([#171](https://github.com/HercegDoo/AIComposePlugin/pull/171)), then AI icons, a clearer hover popup, and a separate panel for reply suggestions ([#181](https://github.com/HercegDoo/AIComposePlugin/pull/181)).

### Generation quality, compatibility, and maintenance

- **Shared prompts:** Email generation and text revision now use a common `EmailPromptBuilder`; providers adapt the same instructions to their APIs instead of maintaining separate prompt wording ([#161](https://github.com/HercegDoo/AIComposePlugin/pull/161)).
- **Cleaner replies:** Plain-text signature and closing detection reduces duplicate greetings and signatures in generated replies ([#163](https://github.com/HercegDoo/AIComposePlugin/pull/163)). Additional conversation-stripping tests and sanitization fixes reduce the chance of copying the quoted thread into the new reply ([#175](https://github.com/HercegDoo/AIComposePlugin/pull/175)).
- **OpenAI GPT-5 and GPT-6 API handling:** Requests for these models use `developer` instructions and `max_completion_tokens` without unsupported temperature settings. Model-specific reasoning effort and clearer output-limit errors were added, along with tests and documentation; older models retain their existing request format ([#164](https://github.com/HercegDoo/AIComposePlugin/pull/164)).
- **PHP 7.4 function support:** Symfony's PHP 8 polyfill supplies newer string helpers when Composer autoloading is available on PHP 7.4. Error logging was also clarified. End-to-end PHP 7.4 runtime compatibility has not been verified by the project's PHP 8.0 CI ([#170](https://github.com/HercegDoo/AIComposePlugin/pull/170)).
- **Bosnian localization:** The messages file now uses Roundcube's `bs_BA` locale name, with a test for locale file consistency ([#169](https://github.com/HercegDoo/AIComposePlugin/pull/169)).
- **Contributor documentation:** Added `AGENTS.md` with repository structure, runtime contracts, and development checks ([#159](https://github.com/HercegDoo/AIComposePlugin/pull/159)).

## [2.0.0] - 2025-12-14

- Added 53 language translations for the AI Compose interface ([#125](https://github.com/HercegDoo/AIComposePlugin/pull/125)).
- Made the OpenAI API endpoint configurable for compatible deployments ([#128](https://github.com/HercegDoo/AIComposePlugin/pull/128)).
- Bundled the built JavaScript assets and Composer runtime dependencies so the plugin could be installed without a separate frontend build ([#129](https://github.com/HercegDoo/AIComposePlugin/pull/129)).
- Added Dependabot updates for Composer packages and GitHub Actions, Composer dependency caching in CI, and an `actions/checkout` update ([#126](https://github.com/HercegDoo/AIComposePlugin/pull/126), [#130](https://github.com/HercegDoo/AIComposePlugin/pull/130), [#127](https://github.com/HercegDoo/AIComposePlugin/pull/127)).
- Updated project version metadata for v2.0.0 ([#131](https://github.com/HercegDoo/AIComposePlugin/pull/131)).

[GitHub release](https://github.com/HercegDoo/AIComposePlugin/releases/tag/v2.0.0) · [Full comparison with v1.0.5](https://github.com/HercegDoo/AIComposePlugin/compare/v1.0.5...v2.0.0)

## [1.0.5] - 2025-02-19

- Released the project under the MIT License, allowing personal and commercial use, modification, and redistribution under its license terms.

[GitHub release](https://github.com/HercegDoo/AIComposePlugin/releases/tag/v1.0.5)
