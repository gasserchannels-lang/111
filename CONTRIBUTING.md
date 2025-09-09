# Contributing to COPRRA

Thank you for your interest in contributing to COPRRA! This document provides guidelines and information for contributors.

## Table of Contents

- [Code of Conduct](#code-of-conduct)
- [Getting Started](#getting-started)
- [Development Setup](#development-setup)
- [Contributing Process](#contributing-process)
- [Coding Standards](#coding-standards)
- [Testing](#testing)
- [Documentation](#documentation)
- [Pull Request Process](#pull-request-process)
- [Issue Reporting](#issue-reporting)

## Code of Conduct

This project and everyone participating in it is governed by our Code of Conduct. By participating, you are expected to uphold this code.

## Getting Started

### Prerequisites

- PHP 8.2 or higher
- Composer
- Node.js 18 or higher
- MySQL 8.0 or higher
- Redis (optional, for caching and queues)
- Git

### Development Setup

1. **Fork the repository**
   ```bash
   git clone https://github.com/your-username/coprra.git
   cd coprra
   ```

2. **Install dependencies**
   ```bash
   composer install
   npm install
   ```

3. **Environment setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Database setup**
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

5. **Build assets**
   ```bash
   npm run dev
   ```

6. **Run tests**
   ```bash
   php artisan test
   ```

## Contributing Process

### 1. Create a Feature Branch

```bash
git checkout -b feature/your-feature-name
# or
git checkout -b bugfix/your-bug-description
```

### 2. Make Your Changes

- Write clean, readable code
- Follow the coding standards
- Add tests for new functionality
- Update documentation as needed

### 3. Test Your Changes

```bash
# Run all tests
php artisan test

# Run specific test suite
php artisan test --testsuite=Unit
php artisan test --testsuite=Feature

# Run with coverage
php artisan test --coverage
```

### 4. Code Quality Checks

```bash
# Run PHPStan
./vendor/bin/phpstan analyse

# Run Rector (dry run)
./vendor/bin/rector process app --dry-run

# Run Pint
./vendor/bin/pint

# Run PHP CS Fixer
./vendor/bin/php-cs-fixer fix --dry-run
```

### 5. Commit Your Changes

```bash
git add .
git commit -m "feat: add new feature description"
```

Use conventional commit messages:
- `feat:` for new features
- `fix:` for bug fixes
- `docs:` for documentation changes
- `style:` for formatting changes
- `refactor:` for code refactoring
- `test:` for test additions/changes
- `chore:` for maintenance tasks

### 6. Push and Create Pull Request

```bash
git push origin feature/your-feature-name
```

Then create a pull request on GitHub.

## Coding Standards

### PHP

- Follow PSR-12 coding standards
- Use type hints for all parameters and return types
- Write PHPDoc comments for all public methods
- Use meaningful variable and method names
- Keep methods small and focused
- Use dependency injection

### JavaScript

- Use ES6+ features
- Follow ESLint rules
- Use meaningful variable names
- Write JSDoc comments for functions

### CSS

- Use BEM methodology
- Follow Stylelint rules
- Use meaningful class names
- Keep styles organized

## Testing

### Test Structure

```
tests/
├── Unit/           # Unit tests
├── Feature/        # Feature tests
├── Integration/    # Integration tests
├── Security/       # Security tests
└── Benchmarks/     # Performance tests
```

### Writing Tests

- Write tests for all new functionality
- Aim for high test coverage
- Use descriptive test names
- Test both success and failure cases
- Mock external dependencies

### Example Test

```php
<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Product;
use Tests\TestCase;

class ProductTest extends TestCase
{
    public function test_can_create_product(): void
    {
        $product = Product::factory()->create([
            'name' => 'Test Product',
            'price' => 99.99,
        ]);

        $this->assertInstanceOf(Product::class, $product);
        $this->assertEquals('Test Product', $product->name);
        $this->assertEquals(99.99, $product->price);
    }
}
```

## Documentation

### API Documentation

- Use OpenAPI/Swagger annotations
- Document all endpoints
- Include request/response examples
- Update schemas when models change

### Code Documentation

- Write clear PHPDoc comments
- Explain complex logic
- Update README when needed
- Keep CHANGELOG.md updated

## Pull Request Process

### Before Submitting

- [ ] Code follows coding standards
- [ ] All tests pass
- [ ] Code quality checks pass
- [ ] Documentation is updated
- [ ] CHANGELOG.md is updated
- [ ] No merge conflicts

### PR Template

```markdown
## Description
Brief description of changes

## Type of Change
- [ ] Bug fix
- [ ] New feature
- [ ] Breaking change
- [ ] Documentation update

## Testing
- [ ] Unit tests added/updated
- [ ] Feature tests added/updated
- [ ] All tests pass

## Checklist
- [ ] Code follows style guidelines
- [ ] Self-review completed
- [ ] Documentation updated
- [ ] CHANGELOG.md updated
```

## Issue Reporting

### Bug Reports

When reporting bugs, please include:

- Clear description of the issue
- Steps to reproduce
- Expected behavior
- Actual behavior
- Environment details
- Screenshots (if applicable)

### Feature Requests

When requesting features, please include:

- Clear description of the feature
- Use case and motivation
- Proposed implementation (if any)
- Additional context

## Development Guidelines

### Git Workflow

1. Create feature branch from `main`
2. Make changes and commit
3. Push branch to origin
4. Create pull request
5. Address review feedback
6. Merge after approval

### Branch Naming

- `feature/description` - New features
- `bugfix/description` - Bug fixes
- `hotfix/description` - Critical fixes
- `docs/description` - Documentation updates
- `refactor/description` - Code refactoring

### Commit Messages

Use conventional commits:

```
type(scope): description

[optional body]

[optional footer]
```

Examples:
- `feat(api): add product search endpoint`
- `fix(auth): resolve login validation issue`
- `docs(readme): update installation instructions`

## Getting Help

- Check existing issues and discussions
- Join our community chat
- Contact maintainers
- Read the documentation

## Recognition

Contributors will be recognized in:
- CONTRIBUTORS.md file
- Release notes
- Project documentation

Thank you for contributing to COPRRA! 🚀
