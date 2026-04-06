# Deployment Guide — Laravel Cloud

This guide walks through deploying **Sovereign Estates** to [Laravel Cloud](https://cloud.laravel.com), the first-party Laravel hosting platform.

Laravel Cloud's Starter plan offers auto-hibernation (scale to zero when idle) and $5 free compute credit, making it ideal for a demo environment.

---

## Prerequisites

- GitHub repository pushed with this codebase
- Laravel Cloud account (no credit card required for Starter plan)
- 5–10 minutes

---

## Step 1 — Create a new application

1. Sign in at [cloud.laravel.com](https://cloud.laravel.com)
2. Click **New Application**
3. Connect your GitHub repository
4. Select the `main` branch
5. Choose a region close to your audience (e.g., **US East Ohio** or **EU Central Frankfurt**)

---

## Step 2 — Provision resources

In the application dashboard, add:

| Resource | Type | Why |
|----------|------|-----|
| **Database** | Serverless Postgres | Primary database (matches the project's PostgreSQL convention) |
| **Cache** | Valkey (Redis-compatible) | Session, cache, queue backend |
| **Storage** | Object Storage | Optional — for media uploads if scaling beyond local disk |

Laravel Cloud auto-wires these into env vars (`DB_*`, `REDIS_*`, `AWS_*`) — no manual config needed.

---

## Step 3 — Environment variables

Set these in the Laravel Cloud UI (Environment → Variables):

### Required

```bash
APP_NAME="Sovereign Estates"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-app.laravel.cloud

# Generate locally with: php artisan key:generate --show
APP_KEY=base64:GENERATE_A_NEW_ONE

# Generate locally with: php artisan ciphersweet:generate-key
# CRITICAL: Once set, NEVER regenerate — encrypted PII becomes unrecoverable.
CIPHERSWEET_KEY=GENERATE_A_NEW_ONE

# Cache + Session + Queue driven by Redis/Valkey
CACHE_STORE=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

# Nominatim geocoding — set a real contact email per their usage policy
NOMINATIM_ENABLED=true
NOMINATIM_USER_AGENT="SovereignEstates/1.0 (contact@yourdomain.com)"
```

### Auto-wired by Laravel Cloud

These are populated automatically when you attach the Postgres and Valkey resources:

- `DB_CONNECTION=pgsql`
- `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`
- `REDIS_HOST`, `REDIS_PORT`, `REDIS_PASSWORD`

### Optional (leave empty to disable)

Social login will automatically hide in the UI when these are empty:

```bash
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GITHUB_CLIENT_ID=
GITHUB_CLIENT_SECRET=
FACEBOOK_CLIENT_ID=
FACEBOOK_CLIENT_SECRET=
APPLE_CLIENT_ID=
APPLE_CLIENT_SECRET=
```

---

## Step 4 — Generate sensitive keys

Run these **locally** and copy the output into Laravel Cloud's UI. Do not commit them.

```bash
# APP_KEY
php artisan key:generate --show

# CIPHERSWEET_KEY
php artisan ciphersweet:generate-key
```

> **Security warning**: The CipherSweet key encrypts PII (user emails, names, phones). If you lose it, all encrypted data is permanently unrecoverable. Store a backup in a password manager.

---

## Step 5 — Deploy command

In the Laravel Cloud UI, set the **Deploy Command** to:

```bash
composer run deploy
```

This runs the `deploy` script defined in `composer.json`:

```json
"deploy": [
    "@php artisan optimize",
    "@php artisan migrate --force",
    "@php artisan db:seed --force"
]
```

What it does:
1. **`optimize`** — caches config, routes, views, events
2. **`migrate --force`** — runs pending migrations
3. **`db:seed --force`** — seeds lookup tables and (on first run) demo data

---

## Step 6 — Demo credentials

The `PropertySeeder` creates demo users for exploration:

| Role | Email | Password |
|------|-------|----------|
| Admin | `admin@luxuryestate.com` | `T3st@Secure!99` |
| Agents | `agent1@luxuryestate.com` – `agent8@luxuryestate.com` | `T3st@Secure!99` |

These are **known credentials** meant for a public demo. If you deploy this template for anything beyond a demo:

- Delete these users
- Create new users via the registration flow
- Change the demo data strategy

---

## Step 7 — Post-deploy checklist

After the first deploy completes:

- [ ] Visit `https://your-app.laravel.cloud` and confirm the landing page renders
- [ ] Log in with `admin@luxuryestate.com` / `T3st@Secure!99`
- [ ] Create a test property — confirm geocoding resolves lat/lng
- [ ] Submit a test inquiry from a property detail page
- [ ] Check `/admin/inquiries` to see the inquiry was received
- [ ] Confirm social login buttons are **hidden** (keys are empty)

---

## Maintenance

### Reset demo data

To wipe and reseed from scratch:

```bash
# Via Laravel Cloud's console
php artisan migrate:fresh --seed --force
```

### Scheduled reset (optional)

To auto-reset the demo every 24h, add to `routes/console.php`:

```php
Schedule::command('migrate:fresh --seed --force')->daily();
```

Laravel Cloud runs the scheduler automatically — no cron config needed.

### Monitor costs

Laravel Cloud's dashboard shows compute credit remaining. With auto-hibernation enabled, a low-traffic demo should consume minimal credit.

---

## Troubleshooting

### "CipherSweet key not set"

Generate a new key (`php artisan ciphersweet:generate-key`) and set `CIPHERSWEET_KEY` in the Laravel Cloud env. Redeploy.

### "Unable to locate file in Vite manifest"

Asset build failed. Check the build logs in Laravel Cloud for errors. Usually a missing `npm install` step or TypeScript error.

### Social login buttons appear but don't work

You set a `CLIENT_ID` but not `CLIENT_SECRET` (or vice versa). Set both or leave both empty.

### Geocoding not working

Check `NOMINATIM_USER_AGENT` is set to a meaningful value (Nominatim blocks generic User-Agents). See `config/services.php` comment.

---

## References

- [Laravel Cloud docs](https://cloud.laravel.com/docs)
- [Laravel Cloud pricing](https://cloud.laravel.com/pricing)
- [Nominatim usage policy](https://operations.osmfoundation.org/policies/nominatim/)
- Project conventions: [`CLAUDE.md`](./CLAUDE.md)
