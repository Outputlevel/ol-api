# OL-API Testing Guide

This document outlines how to run tests, coverage reports, and quality checks for the OL-API plugin.

## Prerequisites

- PHP 8.0 or higher
- Composer installed
- WordPress installation (for integration tests)

## Installing Dependencies

```bash
composer install
```

## Running Tests

### All Tests
```bash
composer test
```

### Specific Test Suite
```bash
# Core tests
vendor/bin/phpunit --testsuite=Core

# Infrastructure tests
vendor/bin/phpunit --testsuite=Infrastructure

# Repository tests
vendor/bin/phpunit --testsuite=Repositories

# Integration tests
vendor/bin/phpunit --testsuite=Integration
```

### Single Test File
```bash
vendor/bin/phpunit tests/Unit/Core/LoaderTest.php
```

### Verbose Output
```bash
vendor/bin/phpunit --verbose
```

## Code Coverage

### Generate Coverage Report
```bash
composer test:coverage
```

This generates:
- `coverage/html/` - HTML coverage report
- `coverage/clover.xml` - Clover XML format
- `coverage/coverage.txt` - Text coverage summary

### View HTML Coverage
Open `coverage/html/index.html` in your browser.

## Quality Assurance

### PHPStan (Static Analysis)
```bash
composer phpstan
```

Analyzes code for type errors and bugs (Level 7 - strictest)

### PHPCS (WordPress Coding Standards)
```bash
# Check code
composer phpcs

# Auto-fix code
composer phpcs:fix
```

### All Quality Checks
```bash
composer quality
```

Runs: PHPStan, PHPCS, and Psalm

## Continuous Integration

For CI/CD pipelines:

```bash
composer dev
```

This runs all tests and quality checks.

## Debugging Tests

### Run Single Test with Debug Output
```bash
vendor/bin/phpunit tests/Unit/Core/LoaderTest.php::LoaderTest::test_register --verbose
```

### Using xDebug
Configure your IDE with xDebug and run:
```bash
vendor/bin/phpunit --configuration=phpunit.xml.dist
```

## Test Structure

```
tests/
├── bootstrap.php           # Test initialization
├── Unit/
│   ├── Core/             # Core component tests
│   ├── Infrastructure/   # Infrastructure tests
│   └── Repositories/     # Repository tests
└── Integration/          # Integration tests
```

## Code Coverage Requirements

- **Minimum coverage:** 80% of code
- **Core components:** 90% coverage target
- **Infrastructure:** 85% coverage target

Current coverage can be viewed in `coverage/html/index.html`

## Known Limitations

- Database tests require a WordPress installation
- Mock objects are used for tests that don't require WordPress functions
- Integration tests run slower than unit tests

## Troubleshooting

### Tests Won't Run
1. Ensure `tests/bootstrap.php` exists
2. Check that `includes/Loader.php` is accessible
3. Verify PHP version is 8.0+

### Coverage Reports Empty
1. Ensure `coverage/` directory is writable
2. Run: `php -m | grep xdebug` to verify xDebug is installed
3. For better coverage, install: `composer require --dev phpunit/phpcov`

### PHPCS Errors
Run auto-fix: `composer phpcs:fix`

## References

- [PHPUnit Documentation](https://phpunit.de/)
- [PHPStan Documentation](https://phpstan.org/)
- [WPCS Configuration](https://github.com/WordPress/WordPress-Coding-Standards)
