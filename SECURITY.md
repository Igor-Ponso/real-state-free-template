# Security Policy

## Reporting a Vulnerability

If you discover a security vulnerability within this project, please report it responsibly.

**Do NOT open a public issue.**

Instead, send an email to the project maintainer or use GitHub's private vulnerability reporting feature.

We will acknowledge your report within 48 hours and provide a detailed response within 5 business days.

## Supported Versions

| Version | Supported |
|---------|-----------|
| latest  | Yes       |

## Best Practices

This template follows Laravel's security best practices:
- CSRF protection on all forms
- SQL injection prevention via Eloquent ORM and parameter binding
- XSS prevention via Vue's auto-escaping and Inertia SSR
- Mass assignment protection via `$fillable`
- Authentication via Laravel Fortify (with 2FA support)
- Authorization via Policies
- Input validation via Form Requests
- File upload validation and sanitization
- Rate limiting on authentication and contact routes
- Sensitive data excluded from Inertia shared props
