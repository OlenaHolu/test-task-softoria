# Laravel DataForSEO Test App

This is a minimal Laravel application for working with the DataForSEO API (SERP → Google → Organic).  
The app provides a simple UI form to check the ranking of a domain for a given keyword, location, and language.

---

## Requirements

- PHP 8.1 or later  
- Laravel 12.x  
- Composer  
- DataForSEO account with valid API credentials  

---

## Installation

1. Clone the repository and install dependencies:
   ```bash
   git clone https://github.com/OlenaHolu/test-task-softoria.git
   cd dataforseo-app
   composer install

2. Require the DataForSEO client:
    ```bash
    composer require jovix/dataforseo-clientv3

3. Copy .env.example to .env and update it with your DataForSEO credentials:
    ```.env
    DATAFORSEO_LOGIN=your_login@email.com
    DATAFORSEO_PASSWORD=your_api_password
    DATAFORSEO_URL=https://api.dataforseo.com
    DATAFORSEO_VERSION=v3
    DATAFORSEO_TIMEOUT=120

    ⚠️ The API password is generated automatically in your DataForSEO dashboard

4. Generate application key (if not set):
    ```bash
    php artisan key:generate

5. Run migrations (for sessions, cache, etc.):
    ```bash
    php artisan migrate

6. Start the development server:
    ```bash
    php artisan serve

---

## Usage

1. Open the app in your browser at [http://127.0.0.1:8000](http://127.0.0.1:8000).  
2. Enter:
   - Keyword (e.g., "laravel")  
   - Domain (e.g., "laravel.com")  
   - Location (choose from dropdown)  
   - Language (choose from dropdown)  
3. Click **Search**.  
4. The app will display the organic ranking of the domain in Google SERP.

---

## How it works (overview)

First tries with the SDK (jovix/dataforseo-clientv3) against the Google Organic Live Advanced endpoint.

If the SDK fails, it falls back to a direct HTTP request using Http::withBasicAuth(...).

The form uses a small default list of locations and languages to avoid huge payloads and timeouts locally.

Domains are normalized before comparison (protocol, path, port, www, IDN → ASCII are stripped).

---

## Why a minimal list of locations/languages?

The Google SERP locations endpoint can return tens of MB of JSON and cause timeouts on localhost.
For this test task, the app ships with a reduced map (US, UK, CA, etc.) that satisfies the requirements while keeping it stable.

---

## Future improvements (optional)

Fetch real locations by country (/v3/serp/google/locations?country=us) with caching (24h).

Add autocomplete for location/language dropdowns.

Artisan command to preload locations into SQLite/JSON.

Polish UI with Tailwind (dropdown search, better feedback).

---

## License

MIT