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
| Social Login  | Laravel Socialite (Google, GitHub, Facebook, Apple)         |
| Roles         | spatie/laravel-permission (admin, agent, client)            |
| Media         | spatie/laravel-medialibrary (property images, conversions)  |
| Money         | elegantly/laravel-money + brick/money (integer cents)       |
| Encryption    | spatie/laravel-ciphersweet (PII encrypted at rest)          |
| Routes        | Laravel Wayfinder (typed route generation)                 |
| Maps          | Leaflet + vue-leaflet (CartoDB Positron tiles, no API key)  |
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
- **PII Encryption** — Email, name, and phone encrypted at rest via CipherSweet with blind indexes for searchable lookups
- **Roles & Permissions** — Admin, Agent, Client roles via spatie/laravel-permission
- **Property Models** — Complete schema: Property, PropertyType, City, AgentProfile, Inquiry, Favorite, PropertyView
- **Lookup Tables** — PropertyStatus, ListingType, InquiryStatus — admin-manageable, no deploy needed for new values
- **Money Handling** — Integer cents via elegantly/laravel-money + brick/money (no float precision issues)
- **Database Seeders** — 30 properties, 8 Canadian cities, 9 property types, 8 agents with realistic bios, 10 clients, inquiries, favorites, views
- **Landing Page** — Fully database-driven via Inertia props and API Resources:
    - Hero: full-screen video loop with serif headline and CTA
    - Value Proposition: glassmorphism cards with scroll-triggered animations
    - Property Search: video background with glass filter container
    - Featured Properties: dynamic blur background on hover, listing/type badges, deep teal palette
    - Neighborhoods: full-screen carousel with property counts per city from DB
    - Meet Our Team: carousel with dialog (split photo + bio layout), all data from DB
    - About: video side + animated counters with real stats from DB
    - Office Location: interactive Leaflet map (CartoDB Positron tiles, Downtown Vancouver)
    - Footer: company branding, Psalm 127:1 dedication, open-source template credit
- **Interactive Map** — Leaflet + vue-leaflet, lazy-loaded via `defineAsyncComponent` + `<Suspense>` for SSR safety
- **API Resources** — `FeaturedPropertyResource`, `CityResource`, `TeamMemberResource` with `whenLoaded()` and `whenCounted()`
- **Property Listings** — `/properties` page with:
    - Grid view and map view (split layout: scrollable cards sidebar + interactive Leaflet map)
    - Multi-select filters: city, type, bedrooms, amenities (Popover + Checkbox pattern from shadcn-vue)
    - Unit amenities vs building amenities split (JSONB columns with GIN indexes for fast containment queries)
    - `pg_trgm` extension for fast `ILIKE` search on property titles/descriptions
    - Pagination with Inertia
    - Form Request validation with `prepareForValidation` for query param normalization
    - Rate limiting on public routes (`throttle:120,1`)
- **PropertyObserver** — Cache invalidation (`home_stats`) on property create/update/delete
- **Strict Mode** — `Model::shouldBeStrict()` in dev (catches N+1, lazy loading, missing attributes, silently discarded attributes)
- **Test Suite** — 54 passing Pest tests covering auth, social login, and settings
- **Dev Tools** — Vue DevTools (Vite plugin) + [Laravel Debugbar](https://github.com/barryvdh/laravel-debugbar) (queries, N+1 detection, cache, request time — dev only, disable via `DEBUGBAR_ENABLED=false`)
- **Code Style** — Laravel Pint + ESLint + Prettier preconfigured

### Planned

- Property detail pages with image gallery
- Contact form with email notifications
- Admin panel with dashboard, CRUD, image upload, and user management
- Input masks for currency, phone, and ZIP code fields (`maska` already installed)
- Repository pattern with Redis caching (`Cache::tags`, `Cache::flexible`)
- Spatie MediaLibrary image uploads (installed, not yet wired to UI)

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
- `agent_profiles.phone` — encrypted at rest
- `inquiries.name`, `inquiries.email`, `inquiries.phone` — encrypted at rest, email searchable via blind index

**How search works:** A blind index (deterministic hash) is generated for the email field, allowing exact-match lookups (`WHERE email_index = hash(input)`) without exposing the actual value. The real email is only decrypted in PHP when the model is loaded.

**What this protects against:** If someone gains access to your database (SQL injection, leaked backup, compromised server), they see encrypted blobs instead of plain text emails and names.

**Setup:** The encryption key is generated during installation (`php artisan ciphersweet:generate-key`) and stored in `CIPHERSWEET_KEY` in your `.env`. Keep this key safe — without it, encrypted data cannot be recovered.

> **A note on this approach:** I implemented PII encryption because it makes sense for a project handling user data, and it showcases a powerful but underused Laravel ecosystem feature. That said, I'm not a security expert, and this may not be the optimal approach for every use case. If you have expertise in data protection and see room for improvement, I'd love a PR — please follow the [Contributing Guide](CONTRIBUTING.md) and [Code of Conduct](CODE_OF_CONDUCT.md).

## Project Structure

```
app/
├── Actions/
│   ├── Auth/           # Social login action (HandleSocialLoginAction)
│   └── Fortify/        # Auth actions (CreateNewUser, ResetUserPassword...)
├── Auth/               # Custom auth provider (CipherSweet)
├── Concerns/           # Shared validation traits
├── Http/
│   ├── Controllers/    # Thin controllers
│   │   ├── Auth/       # Social auth controller
│   │   └── Settings/   # User settings controllers
│   ├── Middleware/      # Custom middleware
│   └── Requests/       # Form Request validation
├── Models/             # Eloquent models
│   ├── Property.php    # Core: belongs to agent, type, city, status
│   ├── PropertyType.php    # Lookup: House, Apartment, Villa...
│   ├── PropertyStatus.php  # Lookup: Draft, Active, Sold...
│   ├── ListingType.php     # Lookup: For Sale, For Rent
│   ├── City.php            # Canadian cities with coordinates
│   ├── AgentProfile.php    # Agent bio, phone (encrypted), license
│   ├── Inquiry.php         # Contact form (PII encrypted)
│   ├── InquiryStatus.php   # Lookup: New, Read, Replied...
│   ├── Favorite.php        # User favorites a property
│   ├── PropertyView.php    # View analytics
│   ├── User.php            # Auth + roles + encrypted PII
│   └── SocialAccount.php   # OAuth provider links
├── Providers/          # Service providers
└── Rules/              # Custom validation (UniqueEncryptedEmail)
resources/
├── js/
│   ├── components/     # Reusable Vue components
│   │   ├── landing/    # Landing page sections (Hero, Team, Search...)
│   │   └── ui/         # shadcn-vue components (button, card, carousel...)
│   ├── composables/    # Vue composables (scroll, fade, password validation)
│   ├── layouts/        # Page layouts (app, auth, settings)
│   ├── lib/            # Utilities and configuration
│   ├── pages/          # Inertia pages
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

- **Actions Pattern**: Business logic lives in Action classes (e.g., `HandleSocialLoginAction`), keeping controllers thin.
- **Lookup Tables over Enums**: PropertyStatus, ListingType, InquiryStatus are database tables — admin can add new values without deploy. PHP enums only for truly immutable code-level constants.
- **Money as Integer Cents**: All monetary values stored as `bigInteger` (cents) with `MoneyCast` from elegantly/laravel-money. No floats, no precision issues. Formatted in API Resources via `intdiv(minorAmount, 100)` + `number_format()`.
- **PII Encryption**: All personal data (email, name, phone) encrypted at rest via CipherSweet. Custom auth provider for encrypted email lookups.
- **API Resources with `.resolve()`**: `JsonResource::collection()` wraps data in `{ "data": [...] }` by default. When passing to Inertia, always call `.resolve()` to get a flat array — otherwise Vue components receive an object instead of an array.
- **Lazy Inertia Props**: All WelcomeController props use closures (`fn () =>`) for Inertia's lazy evaluation — queries only execute when the prop is needed by the frontend.
- **Roles & Permissions**: spatie/laravel-permission with admin, agent, client roles.
- **Eloquent Scopes**: Reusable query scopes on all models (published, featured, forSale, forRent, byCity, priceRange, etc.).
- **Fortify + Socialite**: Email auth with 2FA + social login (Google, GitHub, Facebook, Apple).
- **Wayfinder**: Auto-generated TypeScript functions for routes.
- **SSR**: Server-side rendering via Inertia for SEO.
- **Model::shouldBeStrict()**: Enabled in dev — catches N+1 (lazy loading), access to missing attributes, and silently discarded mass-assignment. Replaces standalone `preventLazyLoading()`.
- **PropertyObserver**: Flushes `home_stats` cache on property create/update/delete.
- **pg_trgm Extension**: PostgreSQL trigram indexes for fast `ILIKE` search on property text fields.
- **Rate Limiting**: Public listing routes use `throttle:120,1` middleware.
- **Unit vs Building Amenities**: Two JSONB columns (`unit_amenities`, `building_amenities`) with GIN indexes. Constants defined on `Property` model, used by controller and factory.
- **Form Request Normalization**: `ListPropertyRequest` uses `prepareForValidation()` to normalize multi-value query string params (string to array) before validation.

### Frontend (Vue 3 + TypeScript)

- **Composition API**: All components use `<script setup lang="ts">`.
- **TypeScript Strict**: No `any` types, full type safety. Shared interfaces in `types/landing.ts`.
- **shadcn-vue**: UI foundation (Button, Card, Carousel, Select, Input, Sheet, Dialog, etc.).
- **Composables**: Shared logic (useScrollHeader, useFadeInOnScroll, usePasswordValidation).
- **Separation of Concerns**: All dynamic data comes from backend via Inertia props. Frontend only handles presentation.
- **Scroll Animations**: CSS transitions + `useIntersectionObserver` from VueUse. No heavy animation libs.
- **`defineAsyncComponent`**: Heavy components (Leaflet map) are lazy-loaded for SSR safety and smaller initial bundles.
- **`<Suspense>`**: Used with async components to show loading states (spinner) while the component loads.
- **Multi-select Filters**: Popover + Checkbox pattern from shadcn-vue for city, type, bedrooms, and amenities filters.
- **Map View**: Split layout with scrollable property cards sidebar + interactive Leaflet map (reuses lazy-loading pattern from landing page).

### Maps — Why Leaflet, Not Google Maps

We chose [Leaflet](https://leafletjs.com/) + [vue-leaflet](https://github.com/vue-leaflet/vue-leaflet) over Google Maps for these reasons:

| | Leaflet | Google Maps |
|---|---|---|
| **Cost** | Free, open-source | Requires billing account |
| **API Key** | None required (with CartoDB/OSM tiles) | Required |
| **Bundle size** | ~148KB (lazy-loaded) | ~200KB+ |
| **SSR** | Works with `defineAsyncComponent` | Same challenge |
| **Customization** | Full control over tiles and styling | Limited free tier styling |

For a **free community template**, zero-config dependencies are critical. Developers should be able to clone, install, and run without signing up for external services. CartoDB Positron tiles provide clean, minimal maps at no cost.

> **Important**: Leaflet requires browser APIs (`window`, `document`) and **will crash during SSR**. Always load via `defineAsyncComponent()` — never import Leaflet components at the top level of a page.

### Color Palette

Three-color luxury palette inspired by high-end real estate brands:

| Color | Variable | Hex | Usage |
|---|---|---|---|
| **Gold** | `--landing-gold` | `hsl(38 60% 58%)` | Accents, CTAs, badges, hover states |
| **Charcoal** | `--landing-charcoal` | `hsl(220 10% 12%)` | Primary dark backgrounds |
| **Deep Teal** | `--landing-deep-teal` | `#1c4051` | Secondary depth, gradients, card hovers |
| **Warm Beige** | `--landing-warm-beige` | `hsl(30 25% 96%)` | Light mode backgrounds |

All colors have light + dark mode variants defined in `resources/css/app.css`.

### Planned (as the project evolves)

- Repository pattern for data access with Redis caching
- Admin panel with CRUD, image upload, user management
- Property detail pages with image gallery
- Spatie MediaLibrary for actual image uploads

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

> *"Unless the Lord builds the house, the builders labor in vain."* — Psalm 127:1

Made with dedication for the Laravel & Vue community.
