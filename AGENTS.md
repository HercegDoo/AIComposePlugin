# AGENTS.md – AIComposePlugin

This file provides guidance for AI coding agents (Copilot, Claude, Codex, etc.) working in this repository.

---

## Repository Overview

**AIComposePlugin** is a **Roundcube webmail plugin** (v2.0.0) that integrates AI email generation into the compose window. The backend is PHP; the frontend is JavaScript bundled with Webpack.

- **Roundcube plugin namespace / task:** `mail|settings`
- **PHP namespace:** `HercegDoo\AIComposePlugin\`
- **AI provider:** OpenAI Chat Completions (extensible via `InterfaceProvider`)

See `.ai/context.md` for a full structural overview.

---

## Environment Setup

```bash
# PHP dependencies
composer install

# Frontend dependencies & build
npm install
npm run build:prod
```

---

## Key Commands

| Purpose | Command |
|---------|---------|
| Run tests | `composer run test` |
| Lint (dry-run) | `composer run cs` |
| Auto-fix lint | `composer run cs-fixer` |
| Static analysis | `composer run phpstan` |
| Production build | `npm run build:prod` |

---

## Where Things Live

| What | Where |
|------|-------|
| Plugin entry point | `AIComposePlugin.php` |
| Base plugin class | `src/AbstractAIComposePlugin.php` |
| Task handlers | `src/Tasks/` |
| AJAX action handlers | `src/Actions/` |
| AI service layer | `src/AIEmailService/` |
| AI providers | `src/AIEmailService/Providers/` |
| Runtime settings | `src/AIEmailService/Settings.php` |
| Request/Response DTOs | `src/AIEmailService/Entity/` |
| UI utilities | `src/Utilities/` |
| Translations | `src/localization/` |
| Frontend source | `assets/src/` |
| Frontend bundles | `assets/dist/` (committed) |
| Skin templates | `skins/elastic/` |
| Tests | `tests/` |
| Config template | `config.inc.php.dist` |

---

## Coding Conventions

1. **`declare(strict_types=1);`** at the top of every PHP file (except `AIComposePlugin.php`).
2. **PSR-4** autoloading: namespace `HercegDoo\AIComposePlugin\` → `src/`.
3. **PHP CS Fixer** (CodeIgniter standard) – always run `composer run cs` before committing.
4. **PHPDoc** `@param`/`@return`/`@throws` for all non-trivial methods; use `array<K, V>` generics.
5. **No inline comments** unless explaining non-obvious logic.
6. **JS bundles** – after any change to `assets/src/`, rebuild with `npm run build:prod` and commit `assets/dist/`.

---

## Task / Action Pattern

- **Tasks** register Roundcube hooks. Add new hooks inside the appropriate Task class.
- **Actions** are single-responsibility AJAX handlers. Create a new class in `src/Actions/Mail/` or `src/Actions/Settings/` and register it in the corresponding Task via `register_action`.

## Adding a New AI Provider

1. Create `src/AIEmailService/Providers/MyProvider.php` implementing `InterfaceProvider`.
2. Extend the `switch` in `Settings::setProvider()` to handle the new provider name.
3. Add config keys to `config.inc.php.dist` with full documentation comments.

---

## Testing Guidelines

- All tests use **PHPUnit** (`tests/`).
- Use `DummyProvider` instead of live AI calls in tests.
- Mock Roundcube internals with **Mockery** and **bypass-finals**.
- **Do not remove or skip existing tests.**
- Confirm `composer run test` passes before opening a PR.

---

## Security Checklist

- [ ] Validate user input against allowed lists (see `validateSettingsValues()`).
- [ ] Never hard-code or log API keys.
- [ ] Return safe, generic error messages to the frontend on provider failure.
- [ ] Do not copy the `CURLOPT_SSL_VERIFYPEER => false` pattern; new code should verify TLS certificates.

---

## Pull Request Requirements

1. All CI checks must pass: CS Fixer, PHPStan, PHPUnit.
2. If frontend files changed: rebuilt `assets/dist/` bundles must be committed.
3. New config keys must be documented in `config.inc.php.dist`.
4. New providers must implement `InterfaceProvider` and be tested with a mock.
