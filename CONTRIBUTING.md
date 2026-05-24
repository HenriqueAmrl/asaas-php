# Contributing

Contributions are welcome! Please follow these guidelines.

## Setup

```bash
git clone https://github.com/henriqueamrl/asaas-php
cd asaas-php
composer install
```

## Workflow

1. Fork the repository
2. Create a feature branch: `git checkout -b feat/my-feature`
3. Write tests for your changes
4. Ensure all checks pass:

```bash
composer test       # run tests
composer analyse    # static analysis
composer format     # fix code style
```

5. Open a pull request against `main`

## Standards

- PHP 8.1+
- PSR-12 code style (enforced by Pint)
- PHPStan level 8 (no errors allowed)
- All public methods must have tests
- `declare(strict_types=1)` in every file

## Branches

- `main` — stable, released code
- `feat/*` — new features
- `fix/*` — bug fixes
