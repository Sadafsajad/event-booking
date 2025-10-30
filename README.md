
# EventBook – Event Booking Manager (Laravel)

Plan events, book seats without overbooking, and view admin reports (top events, power users, occupancy%).

## Features

* Email/password auth + Social login (Google & GitHub via Socialite)
* Events: create (admin), list, search, filter (date range), paginate
* Bookings: safe DB **transactions + row locking** (no overbooking)
* Cached listings with version bump on create/update/booking
* Admin reports:

  * Top 5 events by bookings in last 30 days
  * Users who booked > 3 events last month
  * % occupancy for each event


## 1) Requirements

* PHP 8.2+
* Composer
* MySQL 8+ (or MariaDB)

## 2) Setup

```bash
git clone <your-repo> event-booking
cd event-booking

cp .env.example .env
composer install
php artisan key:generate
```

Edit **.env** (DB + app URL):

```ini
APP_NAME="EventBook"
APP_URL=http://127.0.0.1:8000

DB_DATABASE=event_booking
DB_USERNAME=root
DB_PASSWORD=

# Mail (local dev)
MAIL_MAILER=log   # emails will be written to storage/logs/laravel.log
```

Create DB, then migrate:

```bash
php artisan migrate
```

> Optional: seed some demo events via Tinker

```bash
php artisan tinker
>>> \App\Models\Event::factory()?->count(0); // if you have a factory
>>> \App\Models\Event::create(['title'=>'Tech Conf','venue'=>'Hall A','capacity'=>100,'event_at'=>'2025-09-01 18:00']);
>>> \App\Models\Event::create(['title'=>'Marketing Conf','venue'=>'City Mall','capacity'=>5,'event_at'=>'2025-09-10 16:30']);
```

Run the app:

```bash
php artisan serve
```

Open: **[http://127.0.0.1:8000](http://127.0.0.1:8000)**

---

## 3) Social login configuration (Google & GitHub)

You MUST use **your own client keys**.

### Google

1. Go to **Google Cloud Console → Credentials → Create OAuth client**
   Type: **Web application**

2. Authorized redirect URIs (add exactly):

```
http://127.0.0.1:8000/auth/google/callback
```

3. Put credentials in **.env**:

```ini
GOOGLE_CLIENT_ID=your-google-client-id.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=your-google-client-secret
GOOGLE_REDIRECT_URI=http://127.0.0.1:8000/auth/google/callback
```

### GitHub

1. Go to **[https://github.com/settings/developers](https://github.com/settings/developers) → OAuth Apps → New OAuth App**

2. Homepage URL:

```
http://127.0.0.1:8000
```

Authorization callback URL:

```
http://127.0.0.1:8000/auth/github/callback
```

3. Put credentials in **.env**:

```ini
GITHUB_CLIENT_ID=your-github-client-id
GITHUB_CLIENT_SECRET=your-github-client-secret
GITHUB_REDIRECT_URI=http://127.0.0.1:8000/auth/github/callback
```

### services.php

`config/services.php` already expects env keys like:

```php
'google' => [
    'client_id' => env('GOOGLE_CLIENT_ID'),
    'client_secret' => env('GOOGLE_CLIENT_SECRET'),
    'redirect' => env('GOOGLE_REDIRECT_URI'),
],
'github' => [
    'client_id' => env('GITHUB_CLIENT_ID'),
    'client_secret' => env('GITHUB_CLIENT_SECRET'),
    'redirect' => env('GITHUB_REDIRECT_URI'),
],
```

---

## 4) Authentication / Roles

* Register or login via email, Google, or GitHub.
* To make an **admin**, set `is_admin = 1` for a user:

```bash
php artisan tinker
>>> \App\Models\User::where('email','admin@example.com')->update(['is_admin' => 1]);
```

Middleware alias is `admin` (registered in `bootstrap/app.php`).
Admin-only routes live under `/admin/*`.

---

## 5) How to use

### Public / User

* **Browse events**: `GET /events`

  * Search by title/venue, filter by date (from/to), paginate.
* **Book an event**: `POST /events/{event}/book`

  * Uses transaction + `lockForUpdate()` to prevent overbooking.
  * A user can only have one booking row per event (`unique [user_id,event_id]`).
  * If capacity is reached, the “Book” button turns into **“Sold out”**.

### Admin

* **Events list (admin)**: `GET /admin/events`
* **Create event**: `GET /admin/events/create`, `POST /admin/events`

  * Creating/updating bumps a cache version key so public list refreshes.
* **Reports dashboard**: `GET /admin/reports`

  * Top 5 events (last 30 days)
  * Users who booked > 3 events (last month)
  * Occupancy % per event

---

## 6) Caching

Event list is cached for **5 minutes**.
Whenever events are created/updated/deleted **or a booking is made**, a single
`events:version` cache key is incremented to invalidate all list caches instantly.

You can clear the app cache anytime:

```bash
php artisan optimize:clear
```

---

## 7) Email (Booking confirmation)

By default, **MAIL\_MAILER=log** writes emails to `storage/logs/laravel.log`.
Configure SMTP in `.env` if you want real emails.

---

## 8) Routes quick map

```
/                       Welcome page
/login, /register       Auth (also Google/GitHub)
/events                 Public listing & booking (logged-in users)
/events/{event}         Event details (JSON)
/admin/events           Admin listing
/admin/events/create    Admin create form
/admin/reports          Admin reports dashboard

# API-ish (JSON) reports also exist:
reports/top5-last30
reports/power-users-last-month
reports/occupancy
```

List them:

```bash
php artisan route:list
```

---

* If you change Google/GitHub callback URLs, **update both** the provider console and `.env`.
* Clear caches after changing `.env` or routes:

  ```bash
  php artisan optimize:clear
  ```

Thank you 
Sadaf Sajad
