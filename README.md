# Lafins - Laravel Finaces.

A modern full-stack web application built with Laravel 12 and React 19, leveraging Inertia.js for seamless server-side rendering and a rich component library for exceptional user experience.

## Tech Stack

### Backend

- **Laravel 12** - PHP framework for robust server-side logic
- **PHP 8.2+** - Modern PHP runtime
- **Laravel Fortify** - Authentication and registration
- **Laravel Sanctum** - API authentication
- **Inertia.js** - Modern monolith architecture

### Frontend

- **React 19** - Modern UI library
- **TypeScript** - Type-safe JavaScript
- **Tailwind CSS 4** - Utility-first CSS framework
- **Radix UI** - Accessible component primitives
- **Lucide React** - Icon library
- **Chart.js** - Data visualization
- **Vite** - Fast build tool and dev server

## Features

- Server-side rendering (SSR) support with Inertia.js
- Modern React components with Radix UI primitives
- Type-safe development with TypeScript
- Responsive design with Tailwind CSS 4
- Authentication system with Laravel Fortify
- API support with Laravel Sanctum
- Code quality tools (ESLint, Prettier, Laravel Pint)
- Automated testing with PHPUnit

## Requirements

- PHP >= 8.2
- Composer
- Node.js >= 18.x
- npm or yarn
- Database (MySQL, PostgreSQL, or SQLite)

## Installation

### 1. Clone the repository

```bash
git clone <repository-url>
cd server
```

### 2. Install PHP dependencies

```bash
composer install
```

### 3. Install JavaScript dependencies

```bash
npm install
```

### 4. Environment setup

```bash
cp .env.example .env
php artisan key:generate
```

Configure your database credentials in the `.env` file:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 5. Run migrations

```bash
php artisan migrate
```

## Development

### Standard development mode

Run the development server with hot module replacement:

```bash
composer dev
```

This command concurrently starts:

- Laravel development server (port 8000)
- Queue worker
- Vite dev server with HMR

Access the application at `http://localhost:8000`

### SSR development mode

For server-side rendering:

```bash
composer dev:ssr
```

This command starts:

- Laravel development server
- Queue worker
- Pail log viewer
- Inertia SSR server

### Manual development

Alternatively, run services separately:

```bash
# Terminal 1: Laravel server
php artisan serve

# Terminal 2: Vite dev server
npm run dev

# Terminal 3: Queue worker (optional)
php artisan queue:listen
```

## Building for Production

### Client-side only

```bash
npm run build
```

### With SSR support

```bash
npm run build:ssr
```

## Testing

Run the test suite:

```bash
composer test
```

Or directly with PHPUnit:

```bash
php artisan test
```

## Code Quality

### Format code

```bash
# PHP (Laravel Pint)
./vendor/bin/pint

# JavaScript/TypeScript (Prettier)
npm run format
```

### Lint code

```bash
# JavaScript/TypeScript (ESLint)
npm run lint

# Check formatting
npm run format:check
```

### Type checking

```bash
npm run types
```

## Project Structure

```
.
├── app/                    # Laravel application code
│   ├── Http/              # Controllers, middleware, requests
│   ├── Models/            # Eloquent models
│   ├── Observers/         # Model observers
│   └── Providers/         # Service providers
├── bootstrap/             # Framework bootstrap files
├── config/                # Configuration files
├── database/              # Migrations, factories, seeders
├── public/                # Public assets and entry point
├── resources/             # Views, React components, assets
├── routes/                # Application routes
│   ├── web.php           # Web routes
│   ├── auth.php          # Authentication routes
│   └── settings.php      # Settings routes
├── storage/               # Logs, cache, uploaded files
├── tests/                 # Automated tests
└── vendor/                # Composer dependencies
```

## Key Scripts

| Command             | Description                                  |
| ------------------- | -------------------------------------------- |
| `composer dev`      | Start development server with queue and Vite |
| `composer dev:ssr`  | Start development server with SSR support    |
| `composer test`     | Run test suite                               |
| `npm run dev`       | Start Vite dev server only                   |
| `npm run build`     | Build for production                         |
| `npm run build:ssr` | Build with SSR support                       |
| `npm run lint`      | Lint and fix JavaScript/TypeScript           |
| `npm run format`    | Format code with Prettier                    |
| `npm run types`     | Run TypeScript type checking                 |

## IDE Support

This project includes Laravel IDE Helper for improved autocomplete and code intelligence. Regenerate helper files:

```bash
php artisan ide-helper:generate
php artisan ide-helper:models
```

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
