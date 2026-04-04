# Real State - Free Template

A complete, production-ready real estate website built with **Laravel 13**, **Vue 3**, **Inertia.js v3**, and **shadcn-vue**. Designed as a learning resource and starter template for the developer community.

> Built with best practices: SOLID principles, DRY code, Eloquent query optimization, caching strategies, TypeScript strict mode, and modern frontend patterns.

## Foundation

This project was bootstrapped using the official **Laravel Installer** with the **Vue Starter Kit** (Split auth layout, Header navigation, SSR enabled, Pest testing, PostgreSQL). It also includes **Laravel Boost** for AI-assisted development with MCP server integration, guidelines, and skills.

## Tech Stack

| Layer         | Technology                                                 |
| ------------- | ---------------------------------------------------------- |
| Backend       | Laravel 13 (PHP 8.4)                                       |
| Frontend      | Vue 3 (Composition API + `<script setup>`)                 |
| Language      | TypeScript (strict mode)                                   |
| UI Components | shadcn-vue                                                 |
| Bridge        | Inertia.js v3 (with SSR)                                   |
| Styling       | Tailwind CSS 4                                             |
| Database      | PostgreSQL 16 (SQLite for dev/testing)                     |
| Cache         | Redis                                                      |
| Auth          | Laravel Fortify (login, register, 2FA, email verification) |
| Routes        | Laravel Wayfinder (typed route generation)                 |
| Testing       | Pest PHP 4 + Vitest                                        |
| Code Style    | Laravel Pint + ESLint + Prettier                           |
| AI Dev        | Laravel Boost (MCP server + guidelines)                    |

## Features

### Current

- **Authentication** — Split layout auth pages (image + form) with Fortify
    - Login and registration
    - Password reset and email verification
    - Two-factor authentication (2FA)
    - Rate limiting on auth endpoints
    - Strong password policy enforced in all environments (min 12 chars, mixed case, numbers, symbols, [`uncompromised`](https://laravel.com/docs/13.x/validation#validating-passwords) — checks against known data breaches via Have I Been Pwned)
    - Social login with Google, GitHub, Facebook, and Apple (via [Laravel Socialite](https://laravel.com/docs/13.x/socialite)) — buttons only appear when credentials are configured
    - Real-time password strength indicator and confirmation match
- **User Settings** — Profile, password, appearance, and security management
- **SSR** — Server-side rendering for SEO via Inertia
- **shadcn-vue Components** — Button, Card, Dialog, Input, Dropdown, Sidebar, Skeleton, Tooltip, and more
- **TypeScript** — Strict mode across the entire frontend
- **Wayfinder** — Type-safe route functions (no hardcoded URLs)
- **PII Encryption** — Email and name encrypted at rest via CipherSweet with blind index for searchable lookups
- **Test Suite** — 54 passing Pest tests covering auth, social login, and settings
- **Dev Tools** — Vue DevTools (Vite plugin) + [Laravel Debugbar](https://github.com/barryvdh/laravel-debugbar) (queries, N+1 detection, cache, request time — dev only, disable via `DEBUGBAR_ENABLED=false`)
- **Code Style** — Laravel Pint + ESLint + Prettier preconfigured

### Planned

- Luxury landing page with full-screen hero video
- Minimalist, transparent header (sticky on scroll)
- Property listings with advanced filters (price, location, type, bedrooms, area)
- Property detail pages with image gallery
- Contact form with email notifications
- Mobile-first responsive design (vertical content ready)
- Admin panel with dashboard, CRUD, image upload, and user management
- Repository pattern with Redis caching
- Database seeders with realistic fake data

## Requirements

- PHP >= 8.4
- Composer >= 2.x
- Node.js >= 22.x
- PostgreSQL 16.x (or SQLite for quick setup)
- Redis (optional, recommended for production)

> **Why PHP 8.4 and not 8.5?** PHP 8.5 introduces features like the pipe operator (`|>`), `clone with`, and `array_first()` / `array_last()`. However, Laravel 13 already provides equivalent functionality through its own abstractions — `Collection::pipe()`, `Pipeline`, `Arr::first()`, `replicate()`, and fluent APIs. Since this project focuses on teaching the Laravel way, I chose PHP 8.4 as the minimum to maximize accessibility while losing nothing in practice.

> **Why PostgreSQL and not MySQL?** A real estate website benefits directly from PostgreSQL-specific features: **PostGIS** for geospatial queries ("properties within 5km"), **native full-text search** with ranking for property search, **JSONB columns** for flexible property amenities without extra tables, **partial indexes** for faster queries on active listings, and **materialized views** for dashboard analytics. SQLite is still supported for quick local setup and CI testing.

## Installation

### Option 1: With Laravel Herd (recommended)

[Laravel Herd](https://herd.laravel.com/) is a native macOS/Windows development environment for Laravel. It provides PHP, Nginx, and database services with zero configuration.

```bash
# Clone the repository
git clone git@github.com:Igor-Ponso/real-state-free-template.git
cd real-state-free-template

# Install dependencies
composer install
npm install

# Environment setup
cp .env.example .env
php artisan key:generate
php artisan ciphersweet:generate-key

# Database (use Herd's built-in PostgreSQL or SQLite)
php artisan migrate --seed

# Build frontend assets
npm run build
```

Herd automatically serves the site at `http://real-state-free-template.test`. No need to run `php artisan serve`.

To start the frontend dev server with hot reload:

```bash
npm run dev
```

> **Tip:** Use Herd Pro to manage PostgreSQL and Redis services directly from the menu bar. Run `herd services` to see available services.

### Option 2: Without Herd

```bash
# Clone the repository
git clone git@github.com:Igor-Ponso/real-state-free-template.git
cd real-state-free-template

# Install dependencies
composer install
npm install

# Environment setup
cp .env.example .env
php artisan key:generate
php artisan ciphersweet:generate-key

# Database
php artisan migrate --seed

# Build frontend assets
npm run build

# Start the development server
composer run dev
```

Visit `http://localhost:8000` to see the application.

### AI-Assisted Development (optional)

This project supports [Laravel Boost](https://laravel.com/docs/boost) for AI-assisted development with Claude Code, Cursor, or GitHub Copilot:

```bash
composer require laravel/boost --dev
php artisan boost:install
```

Boost provides your AI agent with project-specific tools, skills, and guidelines.

### Social Login Setup (optional)

Social login buttons (Google, GitHub, Apple) only appear when credentials are configured. Without credentials, the login/register pages work normally with email + password only.

#### Google

1. Go to [Google Cloud Console → Credentials](https://console.cloud.google.com/apis/credentials)
2. Create a project (or select existing)
3. Click **Create Credentials → OAuth Client ID**
4. Application type: **Web application**
5. Add Authorized redirect URI: `https://your-domain.test/auth/google/callback`
6. Copy the Client ID and Client Secret to your `.env`:

```env
GOOGLE_CLIENT_ID=your-client-id
GOOGLE_CLIENT_SECRET=your-client-secret
```

#### GitHub

1. Go to [GitHub → Settings → Developer Settings → OAuth Apps](https://github.com/settings/developers)
2. Click **New OAuth App**
3. Homepage URL: `https://your-domain.test`
4. Authorization callback URL: `https://your-domain.test/auth/github/callback`
5. Register, then copy Client ID and generate a Client Secret to your `.env`:

```env
GITHUB_CLIENT_ID=your-client-id
GITHUB_CLIENT_SECRET=your-client-secret
```

#### Facebook

1. Go to [Facebook Developers](https://developers.facebook.com/apps/)
2. Click **Create App → Consumer**
3. Add **Facebook Login** product
4. In Settings → Basic: copy App ID and App Secret
5. In Facebook Login → Settings: add Valid OAuth Redirect URI: `http://localhost:8000/auth/facebook/callback`
6. Add to your `.env`:

```env
FACEBOOK_CLIENT_ID=your-app-id
FACEBOOK_CLIENT_SECRET=your-app-secret
```

#### Apple

1. Requires an [Apple Developer Program](https://developer.apple.com/programs/) membership ($99/year)
2. Go to [Certificates, Identifiers & Profiles](https://developer.apple.com/account/resources)
3. Create a **Services ID** and configure a **Sign in with Apple** key
4. Add the credentials to your `.env`:

```env
APPLE_CLIENT_ID=your-service-id
APPLE_CLIENT_SECRET=your-generated-secret
```

> For detailed Apple Sign In setup, see the [socialiteproviders/apple documentation](https://socialiteproviders.com/Apple/).

## PII Encryption at Rest

This project encrypts personally identifiable information (PII) in the database using [spatie/laravel-ciphersweet](https://github.com/spatie/laravel-ciphersweet), which implements searchable field-level encryption via [CipherSweet](https://ciphersweet.paragonie.com/) by Paragon Initiative Enterprises.

**What's encrypted:**
- `users.name` — encrypted at rest, decrypted automatically when loaded
- `users.email` — encrypted at rest, searchable via blind index

**How search works:** A blind index (deterministic hash) is generated for the email field, allowing exact-match lookups (`WHERE email_index = hash(input)`) without exposing the actual value. The real email is only decrypted in PHP when the model is loaded.

**What this protects against:** If someone gains access to your database (SQL injection, leaked backup, compromised server), they see encrypted blobs instead of plain text emails and names.

**Setup:** The encryption key is generated during installation (`php artisan ciphersweet:generate-key`) and stored in `CIPHERSWEET_KEY` in your `.env`. Keep this key safe — without it, encrypted data cannot be recovered.

> **A note on this approach:** I implemented PII encryption because it makes sense for a project handling user data, and it showcases a powerful but underused Laravel ecosystem feature. That said, I'm not a security expert, and this may not be the optimal approach for every use case. If you have expertise in data protection and see room for improvement, I'd love a PR — please follow the [Contributing Guide](CONTRIBUTING.md) and [Code of Conduct](CODE_OF_CONDUCT.md).

## Project Structure

```
app/
├── Actions/
│   └── Fortify/        # Auth actions (CreateNewUser, ResetUserPassword...)
├── Concerns/           # Shared traits
├── Http/
│   ├── Controllers/    # Thin controllers
│   │   └── Settings/   # User settings controllers
│   ├── Middleware/      # Custom middleware (HandleAppearance...)
│   └── Requests/       # Form Request validation
├── Models/             # Eloquent models with scopes & relationships
└── Providers/          # Service providers (Fortify)
resources/
├── js/
│   ├── components/     # Reusable Vue components
│   │   └── ui/         # shadcn-vue components (button, card, dialog...)
│   ├── composables/    # Vue composables (shared logic)
│   ├── layouts/        # Page layouts (app, auth, settings)
│   ├── lib/            # Utilities and configuration
│   ├── pages/          # Inertia pages (auth, settings...)
│   └── types/          # TypeScript interfaces
├── css/                # Tailwind CSS entry point
└── views/              # Blade template (app.blade.php)
database/
├── factories/          # Model factories
├── migrations/         # Database migrations
└── seeders/            # Data seeders
tests/
├── Feature/            # Feature tests (Pest)
│   ├── Auth/           # Authentication tests
│   └── Settings/       # Settings tests
└── Unit/               # Unit tests (Pest)
```

## Key Architectural Decisions

### Backend (Laravel)

- **Actions Pattern**: Business logic lives in Action classes (e.g., `Actions/Fortify/CreateNewUser`), keeping controllers thin.
- **Form Requests**: Validation is handled by dedicated Form Request classes.
- **Fortify Authentication**: Full auth backend with login, registration, 2FA, email verification, and password reset.
- **Wayfinder**: Auto-generated TypeScript functions for routes — no hardcoded URLs in the frontend.
- **SSR**: Server-side rendering enabled via Inertia for SEO.

### Frontend (Vue 3 + TypeScript)

- **Composition API**: All components use `<script setup lang="ts">`.
- **TypeScript**: Full type safety across the frontend.
- **shadcn-vue**: High-quality, accessible UI components (button, card, dialog, input, dropdown, sidebar, etc.).
- **Composables**: Shared logic extracted into reusable composables.
- **Layouts**: Separate layouts for app, auth (split), and settings pages.
- **Inertia Forms**: Form handling with automatic validation error binding.

### Planned (as the project evolves)

- Repository pattern for data access with Redis caching
- Eloquent scopes, enums, and API Resources
- Property models, migrations, and seeders

## Testing

```bash
# Run PHP tests
php artisan test --compact

# Run with coverage
php artisan test --coverage

# Run specific test
php artisan test --compact --filter=testName

# Run JS tests
npm run test
```

## Code Style

```bash
# PHP formatting (Laravel Pint)
./vendor/bin/pint

# JS/TS linting (ESLint)
npx eslint .

# JS/TS formatting (Prettier)
npx prettier --write .
```

## Contributing

Contributions are welcome! Please read the [Contributing Guide](CONTRIBUTING.md) before submitting a PR.

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'feat: add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## Support the Project

If this template helped you learn or saved you time, consider supporting its development:

[![Buy Me A Coffee](https://img.shields.io/badge/Buy%20Me%20A%20Coffee-FFDD00?style=for-the-badge&logo=buy-me-a-coffee&logoColor=black)](https://buymeacoffee.com/igorponso)

## License

This project is licensed under the GNU General Public License v3.0 - see the [LICENSE](LICENSE) file for details.

## Author

**Igor Ponso** - [GitHub](https://github.com/Igor-Ponso)

---

Made with dedication for the Laravel & Vue community.
