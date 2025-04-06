# Blueridge Project

A web application built with Laravel and Vue.js that manages barangay official requests and procurement processes.

## Prerequisites

- PHP 8.2 or higher
- Node.js 16.0 or higher (use Node Version Manager) | [https://github.com/nvm-sh/nvm](https://github.com/coreybutler/nvm-windows)
- Composer
- npm 
- MySQL (for development)
- TablePlus

## Installation

1. Clone the repository
```sh
git clone <repository-url>
cd blueridge-new
```

2. Install PHP dependencies
```sh
composer install
```

3. Install JavaScript dependencies
```sh
npm install
```

4. Environment Setup
```sh
cp .env.example .env
php artisan key:generate
```

5. Database Setup
php artisan migrate
```

## Development

To start the development server, run:
```sh
# Start Laravel server, queue worker, and Vite dev server
composer run dev
```

## Available Scripts

- `npm run dev` - Start Vite development server
- `php artisan serve` - Start the backend server

## Tech Stack

- Laravel 12.0
- Vue.js 3
- TypeScript
- Tailwind CSS
- Inertia.js
- SQLite (Development)
