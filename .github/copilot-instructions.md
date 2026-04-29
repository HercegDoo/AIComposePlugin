# GitHub Copilot Instructions – AIComposePlugin

## Project Summary
AIComposePlugin is a **Roundcube webmail plugin** that adds AI-powered email generation to the compose window. It is written in **PHP (backend)** and **JavaScript (frontend)**, uses **OpenAI Chat Completions** as the AI provider, and follows the Roundcube plugin API conventions.

---

## Language & Style

### PHP
- PHP ≥ 7.4; prefer PHP 8.x features only when the minimum version is raised.
- Always add `declare(strict_types=1);` at the top of every PHP file (except the plugin entry point `AIComposePlugin.php`).
- Follow the **CodeIgniter coding standard** enforced by PHP CS Fixer (`.php-cs-fixer.dist.php`).
- Use PSR-4 namespacing: `HercegDoo\AIComposePlugin\` maps to `src/`.
- Annotate every method with proper PHPDoc `@param` / `@return` / `@throws` tags where types cannot be inferred.
- Use `@var array<KeyType, ValueType>` annotations for typed arrays.
- Avoid adding inline comments unless they explain non-obvious logic.

### JavaScript
- Vanilla JS / ES6+ modules bundled with Webpack.
- Entry points: `assets/src/compose.js` and `assets/src/settings.js`.
- After any JS change run `npm run build:prod` and commit the updated `assets/dist/` bundles.

---

## Architecture Rules

### Plugin Task / Action Pattern
- **Tasks** (`src/Tasks/`) register Roundcube hooks and output handlers. There is one task per Roundcube task context (`MailTask` → `mail`, `SettingsTask` → `settings`).
- **Actions** (`src/Actions/`) handle individual AJAX requests. Each action is a single-responsibility class.
- When adding new functionality: create an Action class, register it in the appropriate Task, and route it through Roundcube's `register_action` / `add_hook` API.

### AI Provider Extensibility
- All providers must implement `InterfaceProvider` (`src/AIEmailService/Providers/InterfaceProvider.php`).
- Register new providers in `Settings::setProvider()`.
- Provider configuration is read from Roundcube config via `Settings::getProviderConfig()`.

### Data Flow
- Use `RequestData` DTO to carry generation parameters to a provider.
- Use `Respond` DTO to carry the generated email text back to the caller.
- Wrap all provider errors in `ProviderException`.

---

## Testing
- **PHPUnit** tests live in `tests/`. Run with `composer run test`.
- Use `DummyProvider` for unit tests; never call live AI APIs in tests.
- Mock Roundcube classes using `mockery/mockery` and `dg/bypass-finals`.
- Do **not** remove or skip existing tests.

---

## Configuration
- New config keys must be documented in `config.inc.php.dist` with a comment explaining accepted values and the recommended default.
- Never hard-code API keys or secrets; always read them from Roundcube config.

---

## Security
- Validate all user-supplied values against allowed lists before saving to preferences (see `SettingsTask::validateSettingsValues()`).
- Do not expose API keys or internal errors to the frontend; catch provider exceptions and return safe error messages.
- Do not disable SSL verification in production code (the existing `CURLOPT_SSL_VERIFYPEER => false` is a known issue to address, not a pattern to copy).

---

## CI / Quality Gates
All pull requests must pass:
1. **PHP CS Fixer** – `composer run cs`
2. **PHPStan** – `composer run phpstan`
3. **PHPUnit** – `composer run test`

Run these locally before pushing.
