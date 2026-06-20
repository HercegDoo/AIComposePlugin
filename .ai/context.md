# AI Context – AIComposePlugin

## Project Overview

**AIComposePlugin** is a Roundcube webmail plugin (version 2.0.0) that integrates AI-based email generation directly into the Roundcube compose window. It allows users to generate professional, personalised emails from short instructions, with configurable style, length, creativity level, and language.

- **Package name:** `hercegdoo/aicomposeplugin`
- **Type:** `roundcube-plugin`
- **License:** MIT
- **Authors:** Herceg Doo (amel.junuzovic@dooherceg.ba, harun.duranovic@dooherceg.ba)
- **Namespace:** `HercegDoo\AIComposePlugin`

---

## Repository Structure

```
AIComposePlugin/
├── AIComposePlugin.php          # Plugin entry point (extends AbstractAIComposePlugin)
├── config.inc.php.dist          # Configuration template
├── composer.json                # PHP dependencies & scripts
├── package.json                 # JS/frontend dependencies
├── webpack.config.js            # Frontend bundling
├── phpstan.neon.dist            # PHPStan static analysis config
├── phpunit.xml.dist             # PHPUnit test config
├── .php-cs-fixer.dist.php       # PHP CS Fixer config
│
├── src/
│   ├── AbstractAIComposePlugin.php      # Base plugin class (extends rcube_plugin)
│   ├── Actions/
│   │   ├── AbstractAction.php
│   │   ├── ValidateAction.php
│   │   ├── Mail/
│   │   │   └── GenereteEmailAction.php  # Handles generate-email AJAX action
│   │   └── Settings/
│   │       ├── AddInstruction.php
│   │       ├── DeleteInstruction.php
│   │       └── SaveInstruction.php
│   ├── Tasks/
│   │   ├── AbstractTask.php
│   │   ├── MailTask.php                 # Hooks into Roundcube mail/compose task
│   │   └── SettingsTask.php            # Hooks into Roundcube settings task
│   ├── AIEmailService/
│   │   ├── AIEmail.php                  # Static facade: AIEmail::generate(RequestData)
│   │   ├── Settings.php                 # Runtime settings (provider, styles, lengths…)
│   │   ├── Request.php
│   │   ├── Entity/
│   │   │   ├── RequestData.php          # DTO for AI generation request
│   │   │   └── Respond.php             # DTO for AI response
│   │   ├── Exceptions/
│   │   │   └── ProviderException.php
│   │   └── Providers/
│   │       ├── InterfaceProvider.php
│   │       ├── AbstractProvider.php
│   │       ├── OpenAI.php               # OpenAI Chat Completions integration
│   │       └── DummyProvider.php        # Used in tests
│   ├── Utilities/
│   │   ├── ContentInjector.php          # Injects HTML into Roundcube templates
│   │   ├── TemplateObjectFiller.php     # Renders select fields, buttons, dropdowns
│   │   └── TranslationTrait.php
│   └── localization/
│       ├── labels/                      # Translation labels (per-language)
│       └── messages/                    # Translation messages
│
├── assets/
│   ├── src/
│   │   ├── compose.js                   # Frontend entry: compose window logic
│   │   ├── settings.js                  # Frontend entry: settings page logic
│   │   ├── utils.js
│   │   ├── compose/                     # Compose UI modules
│   │   └── settings/                    # Settings UI modules
│   └── dist/                            # Webpack-compiled bundles (committed)
│       ├── compose.bundle.js
│       └── settings.bundle.js
│
├── skins/
│   └── elastic/                         # Roundcube Elastic skin templates & CSS
│
├── tests/
│   └── AIEmailService/                  # PHPUnit tests
│
└── .github/
    ├── dependabot.yml
    └── workflows/
        ├── codeStyleCheck.yml
        ├── phpStanAnalysis.yml
        └── unitTestRunning.yml
```

---

## Key Concepts

### Plugin Bootstrap
The plugin is activated by Roundcube via `AIComposePlugin.php`, which extends `AbstractAIComposePlugin`. On `init()`, the base class detects the current Roundcube task (`mail` or `settings`) and instantiates the corresponding Task class (`MailTask` or `SettingsTask`).

### Task / Action Pattern
- **Tasks** register Roundcube hooks and output handlers for a given task context.
- **Actions** handle individual AJAX requests (e.g., generate email, save/add/delete predefined instruction).

### AI Email Generation Flow
1. User clicks **Generate** in the compose window.
2. Frontend sends AJAX request to `GenereteEmailAction`.
3. `AIEmail::generate(RequestData)` is called.
4. `Settings::getProvider()` returns the configured provider (currently `OpenAI`).
5. Provider builds a structured prompt and calls the OpenAI Chat Completions API.
6. The generated text is returned to the frontend and inserted into the compose body.

### Configuration (`config.inc.php`)
| Key | Description |
|-----|-------------|
| `aiComposeProvider` | AI provider (`OpenAI`) |
| `aiProviderOpenAIConfig` | `apiKey`, `model`, optional `apiUrl` |
| `aiComposeStyles` | Available email styles + default |
| `aiComposeLengths` | Available lengths + default |
| `aiComposeDefaultCreativity` | `low` / `medium` / `high` |
| `aiComposeLanguages` | Available languages + default |
| `aiDefaultMaxTokens` | Max tokens per request (recommended: 2000) |
| `aiDefaultTimeout` | Request timeout in seconds (recommended: 60) |
| `aiDefaultInputChars` | Max instruction input chars (recommended: 500) |
| `aiMaxPredefinedInstructions` | Max saved predefined instructions (recommended: 20) |

### User Settings
Users can override default style, length, creativity, and language through the Roundcube **Settings → AI Compose** panel. Values are stored in Roundcube user preferences under `aicDefaults`. Users can also manage predefined instructions (up to `aiMaxPredefinedInstructions`).

---

## Development

### Requirements
- PHP ≥ 7.4, `ext-curl`
- Node.js (for frontend build)
- Roundcube ≥ 1.6 (dev dependency pinned to 1.6.5)

### Commands
```bash
# PHP
composer install
composer run test          # PHPUnit (no coverage)
composer run cs            # PHP CS Fixer dry-run
composer run cs-fixer      # PHP CS Fixer fix
composer run phpstan       # PHPStan static analysis

# Frontend
npm install
npm run build:prod         # Production Webpack build
```

### CI Workflows
| Workflow | Trigger |
|----------|---------|
| `codeStyleCheck.yml` | Push / PR |
| `phpStanAnalysis.yml` | Push / PR |
| `unitTestRunning.yml` | Push / PR |

### Adding a New AI Provider
1. Create a class in `src/AIEmailService/Providers/` implementing `InterfaceProvider`.
2. Register it in `Settings::setProvider()` switch statement.
3. Document the new config keys in `config.inc.php.dist`.

---

## Coding Conventions
- PSR-4 autoloading under `HercegDoo\AIComposePlugin\` → `src/`
- PHP CS Fixer with CodeIgniter coding standard
- PHPStan level configured via `phpstan.neon.dist`
- All PHP files use `declare(strict_types=1)` (except the plugin entry point)
- Frontend bundled with Webpack; compiled assets committed to `assets/dist/`
