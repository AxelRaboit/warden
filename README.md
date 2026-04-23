# Warden

> A self-hosted password manager with end-to-end encryption.

Warden is a password manager designed to let individuals and small teams store, organize and share credentials safely. The goal: a modern, fast interface with strong client-side cryptography — never trust the server with plaintext secrets.

## Planned features

### Vault
- Encrypted password entries (login, URL, notes, custom fields)
- Folder / tag organization
- Search and filter
- Trash and restore

### Cryptography
- Client-side encryption (AES-256-GCM) derived from the user's master password (Argon2id)
- Server stores ciphertext only — zero-knowledge architecture
- Master password never transmitted

### Password generator
- Configurable length and character sets
- Passphrase mode (diceware)
- Strength meter based on zxcvbn

### Sharing
- Per-entry sharing with other users
- Team / organization vaults with role-based access
- Secure one-time sharing links (time-limited)

### Sync & access
- Web interface (responsive, dark/light theme)
- Import/export (CSV, 1Password/Bitwarden JSON)
- Browser extension (future)
- Mobile apps (future)

### Admin
- User management (roles: user, admin, dev)
- Invitations and access requests
- Application parameters (registration, maintenance, etc.)
- Audit log (future)

## Tech stack

- **Backend**: Symfony 7.4, PostgreSQL, Doctrine ORM
- **Frontend**: Vue 3 (Composition API) via Symfony UX Vue, Vite, Tailwind CSS v4
- **Crypto**: Web Crypto API (browser-side), libsodium-php (server-side key handling)
- **i18n**: FR / EN / ES / DE

## Project status

🚧 **Early development.** The base scaffolding (users, auth, admin, i18n, theme) is in place. Vault/encryption/sharing modules are not yet implemented.

## Getting started

```bash
cp .env.local.example .env.local       # set DATABASE_URL
composer install
pnpm install
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
php bin/console warden:ap               # sync application parameters
php bin/console doctrine:fixtures:load --no-interaction
pnpm run dev                            # Vite dev server
symfony server:start                    # Symfony server
```

Default admin account (from fixtures): `admin@warden.app`

## License

Proprietary.
