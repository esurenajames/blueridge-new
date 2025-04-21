# Blueridge Project

A web application built with Laravel and Vue.js that manages barangay official requests and procurement processes.

## Prerequisites

- PHP 8.2 or higher
- Node.js 16.0 or higher  
- Use Node Version Manager:  
    - [nvm for macOS/Linux](https://github.com/nvm-sh/nvm)  
    - [nvm for Windows](https://github.com/coreybutler/nvm-windows)
- Composer
- npm 
- MySQL (for development)
- TablePlus
- [shadcn/ui](https://www.shadcn-vue.com/) – used for building modern, accessible UI components

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
```
php artisan migrate
```
### 6. Git Branching

For better organization and collaboration, use the following branch naming convention:  
feat/your-name (replace your-name with your actual name or identifier)

```bash
git switch -c feat/james
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
- `npm run start` - Start both the backend and vite development server 

## Tech Stack

- Laravel 12.0
- Vue.js 3
- TypeScript
- Tailwind CSS
- Inertia.js
- MySQL
