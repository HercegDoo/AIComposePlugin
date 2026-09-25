# Agent instructions — AIComposePlugin

This document applies to the entire repository. Before making changes, inspect the existing implementation and preserve the Roundcube, PHP, and JavaScript contracts described below. Update this file when code changes make any instruction inaccurate.

## Project overview

- `aicomposeplugin` is a Roundcube plugin for composing and revising email and translating incoming summaries with an AI service. It runs in the `mail` and `settings` tasks, and its HTML targets Roundcube's `elastic` skin.
- PHP code uses the `HercegDoo\AIComposePlugin\` namespace, mapped to `src/` by Composer PSR-4 autoloading. Roundcube loads `plugins/aicomposeplugin/aicomposeplugin.php` and instantiates class `aicomposeplugin`; the PHP namespace retains its existing casing.
- Roundcube 1.6.11 is the version pinned for development; `roundcube/plugin-installer` is a development dependency in this repository, so a Composer-managed Roundcube root needs its own installer. `composer.json` declares PHP `>=7.4`; `symfony/polyfill-php80` supplies PHP 8 string helpers on PHP 7.4 after a Composer autoloader loads. Keep the entry point's Composer autoloaders before its first polyfilled call, and avoid PHP 8 syntax that a polyfill cannot provide. GitHub Actions run on PHP 8.0, so verify on PHP 7.4 before claiming complete runtime compatibility.
- Real provider requests require `ext-curl` and `php-curl-class/php-curl-class`. Frontend development requires Node, npm, and webpack.
- To install the plugin in Roundcube, run `composer require hercegdoo/aicomposeplugin` from the Roundcube root or place it in `plugins/aicomposeplugin`; enable `aicomposeplugin` in Roundcube's plugin list. See `README.md` for migration from the former uppercase folder.

## Repository map

| Path | Purpose |
| --- | --- |
| `aicomposeplugin.php`, `src/AbstractAIComposePlugin.php` | Entry point, task selection, and plugin initialization. |
| `src/Tasks/` | Roundcube hooks, action registration, configuration, preferences, and resource loading. |
| `src/Actions/Mail/`, `src/Actions/Settings/` | HTTP actions for email generation and saved-instruction CRUD. |
| `src/AIEmailService/` | Request/response models, shared prompt builder, defaults, provider interface, and OpenAI implementation. |
| `src/Utilities/` | HTML template injection, Roundcube element construction, and translations. |
| `skins/elastic/templates/` | Roundcube HTML templates and fragments for compose and settings screens. |
| `assets/src/` | JavaScript and CSS sources for compose, settings, and incoming summaries. |
| `assets/dist/` | Tracked webpack bundles loaded by PHP in production. |
| `src/localization/labels/`, `src/localization/messages/` | Translations under the `aicomposeplugin.` prefix. |
| `tests/AIEmailService/` | PHPUnit tests for models, settings, and providers. |
| `config.inc.php.dist` | Valid administrator configuration template copied by Roundcube's installer; its empty API key must be filled in before AI requests work. |
| `.github/workflows/` | CI for PHPUnit, PHPStan, and PHP-CS-Fixer. |

## Execution flow and contracts

### Startup and settings

1. `aicomposeplugin.php` loads any available Roundcube root and plugin-local Composer autoloaders, then registers a source-local PSR-4 fallback for `HercegDoo\AIComposePlugin\`. This is needed for Plesk and other manually copied plugin directories whose host autoloader has no mapping for the plugin. It extends `AbstractAIComposePlugin`.
2. `AbstractAIComposePlugin::init()` selects `MailTask` or `SettingsTask` from the Roundcube task and sets the plugin reference used by actions.
3. The `AbstractTask` constructor loads configuration into static `AIEmailService\Settings`, registers actions from the matching directory, and calls the task's `init()` once. `AbstractAIComposePlugin::init()` creates the task handler and does not call `init()` again.
4. `Settings` reads user defaults from the Roundcube `aicDefaults` preference (`style`, `length`, `creativity`, `language`, `pluginVisibility`). Saved instructions use a separate `predefinedInstructions` preference; each record has `id`, `title`, and `message`. Use Roundcube's `get_prefs()` and `save_prefs()` and keep data scoped to the signed-in user.
5. `Settings::setProvider()` supports `OpenAI` and the test `DummyProvider`. The production provider and its `apiKey`/`model` come from the Roundcube `aiComposeProvider` and `aiProviderOpenAIConfig` configuration keys.

### Email generation

1. `MailTask` loads `assets/dist/compose.bundle.js` through Roundcube hooks, sets `rcmail.env.aiPluginOptions` and `aiPredefinedInstructions`, and injects templates through `ContentInjector`.
2. `assets/src/compose.js` initializes commands. `assets/src/compose/commands/sendPostRequest.js` collects data through `emailHelpers/`, sends an `rcmail.http_post` request, and inserts the response into the plain-text or TinyMCE editor. The request sends `htmlMode` from `rcmail.editor.is_html()`; `RequestData` carries it to the shared prompt builder. HTML responses are sanitized to a small TinyMCE-compatible tag set in `emailHelpers/htmlEmail.js` before insertion. Requested length counts visible words, while the API token cap still includes markup.
3. The Roundcube action is **`plugin.aicomposeplugin_GenereteEmailAction`**. The misspelling `Generete` is part of the existing public contract. Renaming the PHP class or file requires updating registration and JavaScript calls, as well as checking compatibility.
4. `GenereteEmailAction::validate()` checks POST data on the server and then builds `RequestData`. `AIEmail::generate()` builds one `EmailPrompt` with `Prompt/EmailPromptBuilder` and passes it with `RequestData` to the selected provider. When the compose subject is empty, the action uses the provider's subject if available or asks for a subject through `AIEmail::generateSubject()` and `Prompt/SubjectPromptBuilder`. `OpenAI` maps the shared instructions to chat messages and sends them to the chat completions endpoint or the configured `apiUrl`. It uses a `developer` role, `max_completion_tokens`, and no `temperature` for GPT-5/6, and retains a `system` role, `max_tokens`, and `temperature` for older models. It requests `minimal` reasoning effort for base GPT-5 and `low` for GPT-6 Astra/Sol/Luna.
5. A successful email action response is JSON with `status`, `respond`, `subject`, and `subjectError`. JavaScript fills the subject only while the field is still empty. The separate `GenerateSubjectAction` canonicalizes the lowercase language values emitted by the compose select through `Settings::resolveLanguage()`, validates the draft and language, then returns JSON with `status` and a new `subject`; validation failures also return JSON with `status: error` and a localized `message`, never the generic iframe response from `ValidateAction`. Its button must not alter the body or overwrite a subject changed while the request was pending. Requests can include previous conversation content, selected text, and signature information; treat these as private data.

### Preferences and saved instructions

- `SettingsTask` adds the `aic` preference section and the `plugin.basepredefinedinstructions` page. The `AddInstruction`, `SaveInstruction`, and `DeleteInstruction` actions handle the form and Roundcube preferences.
- `AbstractTask::autoRegisterActions()` derives each action name from its PHP class name: `plugin.aicomposeplugin_<ClassName>`. When adding or renaming an action, update the URL in `assets/src/` and the corresponding template.
- `assets/src/settings.js` registers the Roundcube commands `updateinstructionlist`, `addinstructiontemplate`, and `deleteinstruction`. PHP calls them through `output->command()`; keep command names and arguments aligned.
- `aiMaxPredefinedInstructions` limits the number of records that can be added, defaulting to 20. When changing the CRUD flow, check validation, record identification, and the resulting list state.

### Incoming summaries

- `MailTask` loads `assets/dist/summary.bundle.js` on the Elastic mail list and message pages when `aiSummaryEnabled` is true. It also loads translated labels on mail pages. The frontend shows a delayed hover preview and inserts a summary card above the opened message body.
- `plugin.aicomposeplugin_SummarizeMessageAction` accepts a message UID, mailbox, and optional refresh flag. The target language comes only from Roundcube's session, never from the client. It reads the message through Roundcube's signed-in mail storage, strips HTML, and sends up to 12,000 characters plus subject to the selected provider.
- `SummaryPromptBuilder` supplies shared detection, summarization, and translation instructions. `SummaryService` parses plain-text JSON with `source_language`, `original_summary`, and `translated_summary`. `CompletionProviderInterface` is implemented by OpenAI and Ollama for this use case. Compose still uses `InterfaceProvider`.
- Summaries are cached in Roundcube's per-user database cache for seven days, keyed by message identity, locale, provider configuration, and prompt version. Refresh bypasses the cache. Do not store raw incoming content in cache or log it.
- `config.inc.php.dist` exposes `aiSummaryEnabled`, `aiSummaryProvider`, `aiSummaryOpenAIConfig`, and `aiSummaryOllamaConfig`. OpenAI inherits compose configuration unless overridden. Ollama is only used for summaries.


## Change guidelines

### PHP and Roundcube

- Make changes in the layer responsible for the behavior: hooks in `Tasks`, HTTP input and validation in `Actions`, AI logic in `AIEmailService`, and HTML helpers in `Utilities`.
- Change shared system and user instructions in `src/AIEmailService/Prompt/EmailPromptBuilder.php`. Providers implement `InterfaceProvider::generateEmail(RequestData, EmailPrompt)` and adapt the supplied prompt to their API format; do not duplicate prompt wording inside providers. `PromptBuilderInterface` lets the service use a different builder without changing provider code.
- PHP formatting follows `.php-cs-fixer.dist.php` (a PSR-12/Symfony combination with four-space indentation). PHPStan analyzes `src/` at `max` level with the existing `phpstan-baseline.neon`. Fix new findings in code unless there is a specific reason to update the baseline.
- Validate new user input on the server. Frontend validation provides user feedback but does not replace server validation. Escape user text when building HTML, and keep field names aligned across `RequestData` and the JavaScript POST object.
- In provider tests, mock cURL or use `DummyProvider`; do not send real API requests or require a real API key. `Settings` holds static state, so explicitly set values that each test depends on. Define `PHPUNIT_RUNNING` in standalone tests that call `RequestData::make()` so settings do not initialize Roundcube.
- Do not log API keys, prompts, previous conversations, email content, or full provider responses. The OpenAI transport verifies TLS certificates and uses a fixed 60-second timeout; preserve certificate verification when changing providers.

### Frontend, templates, and translations

- Edit sources in `assets/src/`, not generated files in `assets/dist/` by hand. After a frontend change, run a production build and include the changed bundles because `MailTask` and `SettingsTask` load them directly.
- Webpack entry points are `assets/src/compose.js`, `assets/src/settings.js`, and `assets/src/summary.js`. JavaScript uses the Roundcube `rcmail`/`rcube_webmail` globals, and compose code also uses TinyMCE. Check both plain-text and HTML editor behavior when changing insertion or text revision.
- HTML templates use Roundcube `<roundcube:...>` tags. `ContentInjector` looks for specific IDs in rendered `elastic` HTML (`composebodycontainer`, `compose-options`, `headers-menu`, `layout-content`). JavaScript expects IDs including `aic-instruction`, `aic-generate-email-button`, `aic_style_select`, `aic_length_select`, `aic_creativity_select`, `aic_language_select`, `composebody`, and `responses-table`. Carry ID or selector changes through PHP, HTML, JavaScript, and a manual Roundcube check.
- Reuse existing translation keys or add new ones to the relevant files in `src/localization/labels/` and `src/localization/messages/`. PHP and JavaScript look up keys with the `aicomposeplugin.` prefix. Bosnian labels and messages use Roundcube's `bs_BA` locale name.
- `package.json` contains Prettier settings (two spaces, double quotes, semicolons), but existing JavaScript is not consistently formatted. Format the changed code without reformatting unrelated files.

### Configuration and tracked dependencies

- `config.inc.php` is ignored and may contain secrets; never add it to git. `config.inc.php.dist` is valid PHP with a deliberately empty API key and is copied by the Roundcube installer.
- `Settings` loads `aiDefaultTimeout` and `aiDefaultInputChars`, but `OpenAI::sendRequest()` currently uses a fixed timeout and does not enforce the input limit. When changing configuration, align behavior and documentation instead of assuming the template already controls these settings.
- This repository tracks `vendor/` for runtime dependencies, as well as `composer.lock`, `package-lock.json`, and `assets/dist/`. Do not edit vendor code by hand. Installing development dependencies can create many changes in tracked `vendor/`; inspect `git status` and include only intentional changes.
- `npm test` in `package.json` is a placeholder that exits with an error. Do not report it as a real test check.
- Webpack and its loaders are in `devDependencies`. Include development packages for a build, for example with `npm ci`.

## Local development and checks

Run commands from the repository root. Standalone PHP development requires Composer development dependencies; install them if `vendor/bin/phpunit`, `phpstan`, or `php-cs-fixer` is missing.

```bash
composer install
npm ci
npm run build:prod
```

After a relevant change, run the applicable checks:

```bash
composer test       # PHPUnit; requires Composer development dependencies
composer phpstan    # static analysis of src/
composer cs         # PHP-CS-Fixer, dry run
npm run build:prod  # when assets/src/ changes
git diff --check
git status --short
```

- `composer cs-fixer` modifies files; use it deliberately and review the diff. CI runs PHPUnit, PHPStan, and PHP-CS-Fixer on PHP 8.0. For an isolated PHP syntax check, use `php -l path/to/changed.php`.
- PHPUnit configuration is in `phpunit.xml.dist`; tests are in `tests/AIEmailService/`, with reflection helpers in `_support/`. Prompt wording tests belong in `tests/AIEmailService/Prompt/`, while provider tests check API mapping and responses. Extend tests when changing models, prompts, settings, or providers. This repository has no automated frontend test suite.
- Changes to Roundcube hooks, DOM selectors, or generation require a manual check in a Roundcube instance using the `elastic` skin: compose screen, plain-text and HTML editors, successful and failed requests, add/edit/delete saved instructions, visibility, and default preferences. The repository does not contain a running Roundcube server.
- At the end, report which checks you actually ran, their results, and anything you could not verify in the current environment. Do not present missing tools or a missing server as a successful test.
