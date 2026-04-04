# Contributing to Real State - Free Template

First off, thank you for considering contributing! This project exists to help the Laravel & Vue community learn and build better applications.

## How Can I Contribute?

### Reporting Bugs

Before creating a bug report, please check existing issues to avoid duplicates.

When filing an issue, include:
- A clear and descriptive title
- Steps to reproduce the behavior
- Expected behavior vs actual behavior
- PHP, Node, and Laravel versions
- Screenshots if applicable

### Suggesting Features

Feature suggestions are welcome! Open an issue with:
- A clear description of the feature
- Why it would be useful for the community
- Example use cases

### Pull Requests

1. **Fork & Clone** the repository
2. **Create a branch** from `main`:
   ```bash
   git checkout -b feature/your-feature
   ```
3. **Install dependencies**:
   ```bash
   composer install && npm install
   ```
4. **Make your changes** following the coding standards below
5. **Write tests** for any new functionality
6. **Run the test suite** to make sure nothing is broken:
   ```bash
   php artisan test && npm run test
   ```
7. **Commit** with a clear message
8. **Push** and open a PR

## Coding Standards

### PHP / Laravel

- Follow [PSR-12](https://www.php-fig.org/psr/psr-12/) coding style
- Use Laravel Pint for code formatting: `./vendor/bin/pint`
- Write Form Requests for validation (no inline validation in controllers)
- Use Eloquent scopes for reusable query logic
- Eager load relationships — no N+1 queries
- Add return types to all methods
- Use PHP enums instead of constants or magic strings

### Vue / JavaScript

- Use `<script setup>` with Composition API
- Use TypeScript for type safety
- Extract shared logic into composables (`use*.ts`)
- Keep components small and focused
- Use `defineProps` and `defineEmits` with type declarations

### Testing

- Write Feature tests for HTTP endpoints and user flows
- Write Unit tests for Actions, Services, and complex logic
- Use Pest PHP syntax
- Factories should produce realistic data

### Git Commit Messages

- Use present tense ("Add feature" not "Added feature")
- Use imperative mood ("Move cursor to..." not "Moves cursor to...")
- Keep the first line under 72 characters
- Reference issues when applicable (`fixes #123`)

## Project Architecture

Please maintain the established architecture:

```
Controllers → Actions/Services → Repositories → Models
```

- **Controllers**: Handle HTTP, call Actions/Services, return Inertia responses
- **Actions**: Single-purpose operations (e.g., `CreatePropertyAction`)
- **Services**: Complex business logic involving multiple operations
- **Repositories**: Database queries and caching
- **Models**: Relationships, scopes, accessors, casts

## Questions?

Open an issue with the `question` label and we'll be happy to help.
