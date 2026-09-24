# Agent instructions — AIComposePlugin

This document applies to the entire repository. Before making changes, inspect the existing implementation and preserve the Roundcube, PHP, and JavaScript contracts described below. Update this file when code changes make any instruction inaccurate.

## Project overview

- `AIComposePlugin` is a Roundcube plugin for composing and revising email with an AI service. It runs in the `mail` and `settings` tasks, and its HTML targets Roundcube's `elastic` skin.
- PHP code uses the `HercegDoo\AIComposePlugin\` namespace, mapped to `src/` by Composer PSR-4 autoloading. The root `AIComposePlugin.php` file is the entry point loaded by Roundcube.
- Roundcube 1.6.11 is a development dependency. `composer.json` declares PHP `>=7.4`, but the existing code uses PHP 8.0 functions such as `str_contains` and `str_starts_with`; GitHub Actions run on PHP 8.0. Do not claim PHP 7.4 compatibility without resolving and verifying this discrepancy.
- Real provider requests require `ext-curl` and `php-curl-class/php-curl-class`. Frontend development requires Node, npm, and webpack.
- To install the plugin in Roundcube, place it in `plugins/AIComposePlugin` and add `AIComposePlugin` to Roundcube's plugin list. See `README.md` for installation and usage.

## Repository map

| Path | Purpose |
| --- | --- |
| `AIComposePlugin.php`, `src/AbstractAIComposePlugin.php` | Entry point, task selection, and plugin initialization. |
| `src/Tasks/` | Roundcube hooks, action registration, configuration, preferences, and resource loading. |
| `src/Actions/Mail/`, `src/Actions/Settings/` | HTTP actions for email generation and saved-instruction CRUD. |
| `src/AIEmailService/` | Request/response models, shared prompt builder, defaults, provider interface, and OpenAI implementation. |
| `src/Utilities/` | HTML template injection, Roundcube element construction, and translations. |
| `skins/elastic/templates/` | Roundcube HTML templates and fragments for compose and settings screens. |
| `assets/src/` | JavaScript and CSS sources for compose and settings. |
| `assets/dist/` | Tracked webpack bundles loaded by PHP in production. |
| `src/localization/labels/`, `src/localization/messages/` | Translations under the `AIComposePlugin.` prefix. |
| `tests/AIEmailService/` | PHPUnit tests for models, settings, and providers. |
| `config.inc.php.dist` | Administrator configuration template; its blank values make it invalid PHP until filled in. |
| `.github/workflows/` | CI for PHPUnit, PHPStan, and PHP-CS-Fixer. |

## Execution flow and contracts

### Startup and settings

1. `AIComposePlugin.php` loads Composer's autoloader and extends `AbstractAIComposePlugin`.
2. `AbstractAIComposePlugin::init()` selects `MailTask` or `SettingsTask` from the Roundcube task and sets the plugin reference used by actions.
3. The `AbstractTask` constructor loads configuration into static `AIEmailService\Settings`, registers actions from the matching directory, and calls the task's `init()`. Currently, `AbstractAIComposePlugin::init()` then calls that task's `init()` again. Check for duplicate hook registration when changing initialization.
4. `Settings` reads user defaults from the Roundcube `aicDefaults` preference (`style`, `length`, `creativity`, `language`, `pluginVisibility`). Saved instructions use a separate `predefinedInstructions` preference; each record has `id`, `title`, and `message`. Use Roundcube's `get_prefs()` and `save_prefs()` and keep data scoped to the signed-in user.
5. `Settings::setProvider()` supports `OpenAI` and the test `DummyProvider`. The production provider and its `apiKey`/`model` come from the Roundcube `aiComposeProvider` and `aiProviderOpenAIConfig` configuration keys.

### Email generation

1. `MailTask` loads `assets/dist/compose.bundle.js` through Roundcube hooks, sets `rcmail.env.aiPluginOptions` and `aiPredefinedInstructions`, and injects templates through `ContentInjector`.
2. `assets/src/compose.js` initializes commands. `assets/src/compose/commands/sendPostRequest.js` collects data through `emailHelpers/`, sends an `rcmail.http_post` request, and inserts the response into the plain-text or TinyMCE editor.
3. The Roundcube action is **`plugin.AIComposePlugin_GenereteEmailAction`**. The misspelling `Generete` is part of the existing public contract. Renaming the PHP class or file requires updating registration and JavaScript calls, as well as checking compatibility.
4. `GenereteEmailAction::validate()` checks POST data on the server and then builds `RequestData`. `AIEmail::generate()` builds one `EmailPrompt` with `Prompt/EmailPromptBuilder` and passes it with `RequestData` to the selected provider. `OpenAI` maps the system and user instructions to chat messages and sends them to the chat completions endpoint or the configured `apiUrl`.
5. A successful action response is JSON with `status` and `respond`. Preserve the shape expected by JavaScript, or update error handling on both sides if you change it. Requests can include previous conversation content, selected text, and signature information; treat these as private data.

### Preferences and saved instructions

- `SettingsTask` adds the `aic` preference section and the `plugin.basepredefinedinstructions` page. The `AddInstruction`, `SaveInstruction`, and `DeleteInstruction` actions handle the form and Roundcube preferences.
- `AbstractTask::autoRegisterActions()` derives each action name from its PHP class name: `plugin.AIComposePlugin_<ClassName>`. When adding or renaming an action, update the URL in `assets/src/` and the corresponding template.
- `assets/src/settings.js` registers the Roundcube commands `updateinstructionlist`, `addinstructiontemplate`, and `deleteinstruction`. PHP calls them through `output->command()`; keep command names and arguments aligned.
- `aiMaxPredefinedInstructions` limits the number of records that can be added, defaulting to 20. When changing the CRUD flow, check validation, record identification, and the resulting list state.

## Change guidelines

### PHP and Roundcube

- Make changes in the layer responsible for the behavior: hooks in `Tasks`, HTTP input and validation in `Actions`, AI logic in `AIEmailService`, and HTML helpers in `Utilities`.
- Change shared system and user instructions in `src/AIEmailService/Prompt/EmailPromptBuilder.php`. Providers implement `InterfaceProvider::generateEmail(RequestData, EmailPrompt)` and adapt the supplied prompt to their API format; do not duplicate prompt wording inside providers. `PromptBuilderInterface` lets the service use a different builder without changing provider code.
- PHP formatting follows `.php-cs-fixer.dist.php` (a PSR-12/Symfony combination with four-space indentation). PHPStan analyzes `src/` at `max` level with the existing `phpstan-baseline.neon`. Fix new findings in code unless there is a specific reason to update the baseline.
- Validate new user input on the server. Frontend validation provides user feedback but does not replace server validation. Escape user text when building HTML, and keep field names aligned across `RequestData` and the JavaScript POST object.
- In provider tests, mock cURL or use `DummyProvider`; do not send real API requests or require a real API key. `Settings` holds static state, so explicitly set values that each test depends on.
- Do not log API keys, prompts, previous conversations, email content, or full provider responses. When working on `OpenAI`, review transport security: the existing code disables cURL SSL verification and uses a fixed 60-second timeout. Do not extend that pattern.

### Frontend, templates, and translations

- Edit sources in `assets/src/`, not generated files in `assets/dist/` by hand. After a frontend change, run a production build and include the changed bundles because `MailTask` and `SettingsTask` load them directly.
- Webpack entry points are `assets/src/compose.js` and `assets/src/settings.js`. JavaScript uses the Roundcube `rcmail`/`rcube_webmail` globals, and compose code also uses TinyMCE. Check both plain-text and HTML editor behavior when changing insertion or text revision.
- HTML templates use Roundcube `<roundcube:...>` tags. `ContentInjector` looks for specific IDs in rendered `elastic` HTML (`composebodycontainer`, `compose-options`, `headers-menu`, `layout-content`). JavaScript expects IDs including `aic-instruction`, `aic-generate-email-button`, `aic_style_select`, `aic_length_select`, `aic_creativity_select`, `aic_language_select`, `composebody`, and `responses-table`. Carry ID or selector changes through PHP, HTML, JavaScript, and a manual Roundcube check.
- Reuse existing translation keys or add new ones to the relevant files in `src/localization/labels/` and `src/localization/messages/`. PHP and JavaScript look up keys with the `AIComposePlugin.` prefix. Existing locale filenames are inconsistent (`labels/bs_BA.inc`, `messages/ba_BA.inc`); verify the mapping before renaming them.
- `package.json` contains Prettier settings (two spaces, double quotes, semicolons), but existing JavaScript is not consistently formatted. Format the changed code without reformatting unrelated files.

### Configuration and tracked dependencies

- `config.inc.php` is ignored and may contain secrets; never add it to git. `config.inc.php.dist` has blank placeholders, so do not run PHP lint on it until those values are filled in.
- The code reads `aiComposeCreativity`, while the template names `aiComposeDefaultCreativity`. `Settings` also loads `aiDefaultTimeout` and `aiDefaultInputChars`, but `OpenAI::sendRequest()` currently uses a fixed timeout and does not enforce the input limit. When changing configuration, align behavior and documentation instead of assuming the template already controls these settings.
- This repository tracks `vendor/` for runtime dependencies, as well as `composer.lock`, `package-lock.json`, and `assets/dist/`. Do not edit vendor code by hand. Installing development dependencies can create many changes in tracked `vendor/`; inspect `git status` and include only intentional changes.
- `npm test` in `package.json` is a placeholder that exits with an error. Do not report it as a real test check.
- `README.md` suggests `npm install --omit=dev` for rebuilding, but webpack and its loaders are in `devDependencies`. Include development packages for a build, for example with `npm ci`.

## Local development and checks

Run commands from the repository root. Standalone PHP development requires Composer development dependencies; the currently tracked `vendor/` contains the runtime package but lacks `vendor/bin/phpunit`, `phpstan`, and `php-cs-fixer`.

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
