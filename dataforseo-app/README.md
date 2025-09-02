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
   git clone <your-repo-url>
   cd dataforseo-app
   composer install

2. Require the DataForSEO client:
    ```bash
    composer require jovix/dataforseo-clientv3

3. Configure environment variables in your .env file:
    ```.env
    DATAFORSEO_LOGIN=your_login@email.com
    DATAFORSEO_PASSWORD=your_api_password
    DATAFORSEO_URL=https://api.dataforseo.com
    DATAFORSEO_VERSION=v3
    DATAFORSEO_TIMEOUT=120

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