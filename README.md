# Real State - Free Template

A complete, production-ready real estate website built with **Laravel 13**, **Vue 3**, **Inertia.js v3**, and **shadcn-vue**. Designed as a learning resource and starter template for the developer community.

> Built with best practices: SOLID principles, DRY code, Eloquent query optimization, caching strategies, TypeScript strict mode, and modern frontend patterns.

## Foundation

This project was bootstrapped using the official **Laravel Installer** with the **Vue Starter Kit** (Split auth layout, Header navigation, SSR enabled, Pest testing, PostgreSQL). It also includes **Laravel Boost** for AI-assisted development with MCP server integration, guidelines, and skills.

## Tech Stack

| Layer | Technology |
|-------|-----------|
| Backend | Laravel 13 (PHP 8.4) |
| Frontend | Vue 3 (Composition API + `<script setup>`) |
| Language | TypeScript (strict mode) |
| UI Components | shadcn-vue |
| Bridge | Inertia.js v3 (with SSR) |
| Styling | Tailwind CSS 4 |
| Database | PostgreSQL 16 (SQLite for dev/testing) |
| Cache | Redis |
| Auth | Laravel Fortify (login, register, 2FA, email verification) |
| Routes | Laravel Wayfinder (typed route generation) |
| Testing | Pest PHP 4 + Vitest |
| Code Style | Laravel Pint + ESLint + Prettier |
| AI Dev | Laravel Boost (MCP server + guidelines) |

## Features

### Public Website
- Luxury landing page with full-screen hero video
- Minimalist, transparent header (sticky on scroll)
- Property listings with advanced filters (price, location, type, bedrooms, area)
- Property detail pages with image gallery
- Contact form with email notifications
- SEO-friendly with Inertia SSR
- Mobile-first responsive design (vertical content ready)

### Authentication
- Split layout auth pages (image + form)
- Login and registration
- Password reset and email verification
- Two-factor authentication (2FA)
- Rate limiting on auth endpoints

### Admin Panel (planned)
- Dashboard with analytics (listings, views, inquiries)
- Full CRUD for properties
- Image upload with optimization
- Manage property types, amenities, and locations
- Inquiry management
- User management with roles (admin, agent)

### Developer Experience
- Clean architecture following SOLID principles
- TypeScript strict mode across the entire frontend
- Repository pattern for data access
- Form Request validation
- API Resources for consistent responses
- Eloquent best practices (eager loading, scopes, accessors)
- Redis caching with smart invalidation
- Comprehensive test suite (Feature + Unit)
- Database seeders with realistic fake data
- Laravel Wayfinder for type-safe route functions
- Laravel Boost for AI-assisted development

## Requirements

- PHP >= 8.4
- Composer >= 2.x
- Node.js >= 22.x
- PostgreSQL 16.x (or SQLite for quick setup)
- Redis (optional, recommended for production)

## Installation

```bash
# Clone the repository
git clone git@github.com:Igor-Ponso/real-state-free-template.git
cd real-state-free-template

# Install PHP dependencies
composer install

# Install Node dependencies
npm install

# Environment setup
cp .env.example .env
php artisan key:generate

# Database
php artisan migrate --seed

# Build frontend assets
npm run build

# Start the development server
composer run dev
```

Visit `http://localhost:8000` to see the application.

## Project Structure

```
app/
├── Actions/            # Single-purpose action classes
├── Enums/              # PHP enums (PropertyType, PropertyStatus...)
├── Http/
│   ├── Controllers/    # Thin controllers
│   ├── Middleware/      # Custom middleware
│   └── Requests/       # Form Request validation
├── Models/             # Eloquent models with scopes & relationships
├── Policies/           # Authorization policies
├── Repositories/       # Data access layer
└── Services/           # Business logic
resources/
├── js/
│   ├── components/     # Reusable Vue components (shadcn-vue based)
│   ├── composables/    # Vue composables (shared logic)
│   ├── layouts/        # Page layouts
│   ├── lib/            # Utilities and configuration
│   ├── pages/          # Inertia pages
│   └── types/          # TypeScript interfaces
└── css/
database/
├── factories/          # Model factories
├── migrations/         # Database migrations
└── seeders/            # Realistic data seeders
tests/
├── Feature/            # Feature tests (Pest)
└── Unit/               # Unit tests (Pest)
```

## Key Architectural Decisions

### Backend (Laravel)
- **Thin Controllers**: Controllers only handle HTTP concerns. Business logic lives in Actions/Services.
- **Eager Loading**: All relationships are eager-loaded to prevent N+1 queries.
- **Query Scopes**: Reusable query filters via Eloquent local scopes.
- **Enums**: PHP 8.4 enums for type-safe constants (property types, statuses).
- **Form Requests**: All validation is handled by dedicated Form Request classes.
- **API Resources**: Consistent data transformation between backend and frontend.
- **Cache Strategy**: Redis caching with tag-based invalidation on model events.
- **Wayfinder**: Auto-generated TypeScript functions for routes — no hardcoded URLs.

### Frontend (Vue 3 + TypeScript)
- **Composition API**: All components use `<script setup lang="ts">`.
- **TypeScript Strict**: No `any` types, full type safety.
- **Composables**: Shared logic extracted into reusable composables.
- **shadcn-vue**: High-quality, accessible UI components as the design foundation.
- **Inertia SSR**: Server-side rendering for SEO.
- **Inertia Forms**: Form handling with automatic validation error binding.

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
