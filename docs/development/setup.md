# Development Setup Guide

## Prerequisites

Before setting up the development environment, ensure you have the following installed:

- **PHP 8.2+** with extensions: mbstring, dom, fileinfo, mysql, sqlite, pgsql, redis, memcached, zip, bcmath, soap, intl, gd, exif, iconv, imagick
- **Composer** 2.0+
- **Node.js** 18+ and npm
- **MySQL** 8.0+ or **PostgreSQL** 13+
- **Redis** (optional, for caching and queues)
- **Git**

## Installation

### 1. Clone the Repository

```bash
git clone https://github.com/your-username/coprra.git
cd coprra
```

### 2. Install PHP Dependencies

```bash
composer install
```

### 3. Install Node.js Dependencies

```bash
npm install
```

### 4. Environment Configuration

```bash
cp .env.example .env
php artisan key:generate
```

### 5. Database Setup

```bash
# Create database
mysql -u root -p -e "CREATE DATABASE coprra;"

# Run migrations
php artisan migrate

# Seed database
php artisan db:seed
```

### 6. Build Assets

```bash
npm run dev
# or for production
npm run build
```

### 7. Start Development Server

```bash
php artisan serve
```

## Development Workflow

### Code Quality Tools

The project includes several code quality tools:

```bash
# Run all quality checks
composer quality

# Individual tools
composer fix              # Laravel Pint
composer analyse          # PHPStan
composer analyse:psalm    # Psalm
composer analyse:phpmd    # PHPMD
composer analyse:security # Security audit
```

### Testing

```bash
# Run all tests
php artisan test

# Run specific test suites
php artisan test --testsuite=Unit
php artisan test --testsuite=Feature
php artisan test --testsuite=Performance

# Run with coverage (requires Xdebug)
php artisan test --coverage
```

### Frontend Development

```bash
# Start Vite dev server
npm run dev

# Build for production
npm run build

# Preview production build
npm run preview

# Run frontend tests
npm run test:frontend
```

## Project Structure

```
coprra/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/           # API controllers
│   │   │   └── Web/           # Web controllers
│   │   ├── Middleware/        # Custom middleware
│   │   └── Requests/          # Form request validation
│   ├── Models/                # Eloquent models
│   ├── Policies/              # Authorization policies
│   ├── Services/              # Business logic services
│   └── View/
│       └── Composers/         # View composers
├── config/                    # Configuration files
├── database/
│   ├── factories/             # Model factories
│   ├── migrations/            # Database migrations
│   └── seeders/               # Database seeders
├── resources/
│   ├── views/
│   │   ├── components/        # Blade components
│   │   └── layouts/           # Layout templates
│   ├── js/                    # JavaScript files
│   └── css/                   # CSS files
├── routes/
│   ├── api.php               # API routes
│   └── web.php               # Web routes
├── tests/
│   ├── Unit/                 # Unit tests
│   ├── Feature/              # Feature tests
│   ├── Integration/          # Integration tests
│   └── Performance/          # Performance tests
└── docs/                     # Documentation
```

## Database Schema

### Core Tables

- **users** - User accounts
- **products** - Product catalog
- **categories** - Product categories
- **brands** - Product brands
- **stores** - Store information
- **price_offers** - Price offers from stores
- **reviews** - Product reviews
- **wishlists** - User wishlists
- **price_alerts** - Price drop alerts

### Relationships

- Products belong to Categories and Brands
- Price Offers belong to Products and Stores
- Reviews belong to Products and Users
- Wishlists belong to Users and Products
- Price Alerts belong to Users and Products

## API Documentation

The API is documented using OpenAPI/Swagger. Access the documentation at:

- Development: `http://localhost:8000/api/documentation`
- Production: `https://api.coprra.com/api/documentation`

### Key Endpoints

- `GET /api/products` - List products
- `GET /api/products/{id}` - Get product details
- `POST /api/products` - Create product (Admin)
- `PUT /api/products/{id}` - Update product (Admin)
- `DELETE /api/products/{id}` - Delete product (Admin)

## Configuration

### Environment Variables

Key environment variables:

```env
APP_NAME=COPRRA
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=coprra
DB_USERNAME=root
DB_PASSWORD=

CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

### Service Configuration

- **Cache**: Redis (production) / Array (local)
- **Queue**: Redis (production) / Sync (local)
- **Session**: Redis (production) / File (local)
- **Mail**: SMTP (production) / Log (local)

## Docker Development

### Using Docker Compose

```bash
# Start all services
docker-compose up -d

# View logs
docker-compose logs -f

# Stop services
docker-compose down
```

### Services

- **app** - Laravel application
- **mysql** - MySQL database
- **redis** - Redis cache/queue
- **nginx** - Web server

## Troubleshooting

### Common Issues

1. **Permission Errors**
   ```bash
   chmod -R 755 storage bootstrap/cache
   ```

2. **Composer Memory Issues**
   ```bash
   composer install --no-dev --optimize-autoloader
   ```

3. **Node.js Version Issues**
   ```bash
   nvm use 18
   ```

4. **Database Connection Issues**
   - Check MySQL/PostgreSQL is running
   - Verify credentials in .env
   - Check database exists

### Debug Mode

Enable debug mode for development:

```env
APP_DEBUG=true
LOG_LEVEL=debug
```

### Logs

View application logs:

```bash
tail -f storage/logs/laravel.log
```

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Run tests and quality checks
5. Submit a pull request

### Commit Convention

Use conventional commits:

- `feat:` - New features
- `fix:` - Bug fixes
- `docs:` - Documentation
- `style:` - Code style
- `refactor:` - Code refactoring
- `test:` - Tests
- `chore:` - Maintenance

## Support

For development support:

- Check the documentation in `/docs`
- Review existing issues on GitHub
- Contact the development team
