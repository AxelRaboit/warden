# Warden

> A self-hosted password manager with end-to-end encryption.

Warden lets individuals and small teams store, organize and share credentials securely. Strong client-side cryptography ensures the server never sees plaintext secrets — zero-knowledge architecture.

## Features

### Vault
- Encrypted password entries (login, URL, notes, custom fields)
- Client-side AES-256-GCM encryption, Argon2id key derivation
- Folder / tag organization
- Search and filter
- Trash and restore

### Cryptography
- Master password never transmitted — derived locally via Argon2id
- Web Crypto API (browser) + libsodium-php (server-side key handling)
- Server stores ciphertext only

### Password generator
- Configurable length and character sets
- Passphrase mode (diceware)
- Strength meter based on zxcvbn

### Sharing *(planned)*
- Per-entry sharing with other users
- Team / organization vaults with role-based access
- Secure one-time sharing links (time-limited)

### Sync & access
- Web interface (responsive, dark/light theme)
- Import/export (CSV, 1Password/Bitwarden JSON) *(planned)*
- Browser extension *(future)*
- Mobile apps *(future)*

### Admin
- User management (roles: user, admin, dev)
- Invitations and access requests
- Application parameters (registration, maintenance, etc.)
- Audit log *(planned)*

## Tech stack

- **Backend**: Symfony 7.4, PostgreSQL, Doctrine ORM
- **Frontend**: Vue 3 (Composition API) via Symfony UX Vue, Vite, Tailwind CSS v4
- **Crypto**: Web Crypto API (browser-side), libsodium-php (server-side)
- **i18n**: FR / EN / ES / DE

## Project status

Base scaffolding (auth, admin, i18n, theme) and vault MVP (encrypted CRUD, unlock flow) are implemented. Sharing and import/export are not yet built.

## Getting started

```bash
make setup-env      # create .env.local from template — set DATABASE_URL inside
make install-dev    # install dependencies, run migrations, load fixtures, start Vite
make start          # start Symfony server + Vite (Docker DB spun up automatically)
```

Default admin account (from fixtures): `admin@warden.app`

### Useful commands

| Command | Description |
|---|---|
| `make test` | Run all tests (frontend + backend) |
| `make fix` | Auto-fix PHP, JS and Twig code style |
| `make stan` | PHPStan static analysis |
| `make fixtures` | Reset DB and reload fixtures |
| `make start-dev-worker` | Start async Messenger worker |
| `make install-prod` | Production build (deps + assets) |

Run `make help` for the full list.

## License

Proprietary.
