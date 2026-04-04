# Real State - Free Template

A complete, production-ready real estate website built with **Laravel 12**, **Vue 3**, and **Inertia.js v3**. Designed as a learning resource and starter template for the developer community.

> Built with best practices: SOLID principles, DRY code, Eloquent query optimization, caching strategies, and modern frontend patterns.

## Tech Stack

| Layer | Technology |
|-------|-----------|
| Backend | Laravel 12 (PHP 8.4) |
| Frontend | Vue 3 (Composition API + `<script setup>`) |
| Bridge | Inertia.js v3 |
| Styling | Tailwind CSS 4 |
| Database | MySQL 8 / PostgreSQL 16 |
| Cache | Redis |
| Search | Laravel Scout (Meilisearch) |
| Auth | Laravel Breeze |
| Testing | Pest PHP + Vitest |

## Features

### Public Website
- Property listings with advanced filters (price, location, type, bedrooms, area)
- Interactive map integration
- Property detail pages with image gallery
- Contact form with email notifications
- SEO-friendly URLs and meta tags
- Responsive design (mobile-first)
- Favorites / Wishlist (session-based for guests, persisted for users)

### Admin Panel
- Dashboard with analytics (listings, views, inquiries)
- Full CRUD for properties
- Image upload with optimization and thumbnails
- Manage property types, amenities, and locations
- Inquiry management
- User management with roles (admin, agent)

### Developer Experience
- Clean architecture following SOLID principles
- Repository pattern for data access
- Form Request validation
- API Resources for consistent responses
- Eloquent best practices (eager loading, scopes, accessors)
- Redis caching with smart invalidation
- Comprehensive test suite (Feature + Unit)
- Database seeders with realistic fake data
- IDE helper annotations

## Requirements

- PHP >= 8.4
- Composer >= 2.x
- Node.js >= 22.x
- MySQL 8.x or PostgreSQL 16.x
- Redis

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

# Build assets
npm run dev

# Start the server
php artisan serve
```

Visit `http://localhost:8000` to see the application.

## Project Structure

```
app/
├── Actions/            # Single-purpose action classes
├── Enums/              # PHP enums (PropertyType, PropertyStatus...)
├── Http/
│   ├── Controllers/    # Thin controllers
│   ├── Requests/       # Form Request validation
│   └── Resources/      # API Resources (Inertia data)
├── Models/             # Eloquent models with scopes & relationships
├── Policies/           # Authorization policies
├── Repositories/       # Data access layer
└── Services/           # Business logic
resources/
├── js/
│   ├── Components/     # Reusable Vue components
│   ├── Composables/    # Vue composables (shared logic)
│   ├── Layouts/        # Page layouts
│   ├── Pages/          # Inertia pages
│   └── types/          # TypeScript interfaces
└── css/
database/
├── factories/          # Model factories
├── migrations/         # Database migrations
└── seeders/            # Realistic data seeders
tests/
├── Feature/            # Feature tests
└── Unit/               # Unit tests
```

## Key Architectural Decisions

### Backend (Laravel)
- **Thin Controllers**: Controllers only handle HTTP concerns. Business logic lives in Actions/Services.
- **Repository Pattern**: Database queries are encapsulated in repositories with caching support.
- **Eager Loading**: All relationships are eager-loaded to prevent N+1 queries.
- **Query Scopes**: Reusable query filters via Eloquent local scopes.
- **Enums**: PHP 8.4 enums for type-safe constants (property types, statuses).
- **Form Requests**: All validation is handled by dedicated Form Request classes.
- **API Resources**: Consistent data transformation between backend and frontend.
- **Cache Strategy**: Redis caching with tag-based invalidation on model events.

### Frontend (Vue 3)
- **Composition API**: All components use `<script setup>` syntax.
- **Composables**: Shared logic extracted into reusable composables.
- **TypeScript**: Full type safety across the frontend.
- **Component Design**: Small, focused, reusable components.
- **Inertia Forms**: Form handling with automatic validation error binding.

## Testing

```bash
# Run PHP tests
php artisan test

# Run with coverage
php artisan test --coverage

# Run JS tests
npm run test

# Run all
composer test && npm run test
```

## Contributing

Contributions are welcome! Please read the [Contributing Guide](CONTRIBUTING.md) before submitting a PR.

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
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
