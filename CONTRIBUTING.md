# Contributing to Gowelle Flutterwave PHP

Thank you for your interest in contributing to Gowelle Flutterwave PHP! This document provides guidelines and instructions for contributing to the project.

## Code of Conduct

By participating in this project, you agree to maintain a respectful and inclusive environment for all contributors.

## Getting Started

1. Fork the repository
2. Clone your fork: `git clone https://github.com/your-username/flutterwave-php.git`
3. Create a new branch: `git checkout -b feature/your-feature-name`
4. Install dependencies: `composer install`

## Development Setup

### Prerequisites

- PHP 8.2 or higher
- Composer
- Laravel 11.x (for testing)

### Running Tests

```bash
# Run all tests
composer test

# Run tests with coverage
composer test-coverage
```

### Code Style

We use Laravel Pint for code formatting. Before committing, ensure your code follows the project's style:

```bash
# Format code
composer format
```

### Static Analysis

We use PHPStan for static analysis. Run it before submitting:

```bash
# Run PHPStan
composer analyse
```

## Making Changes

### Branch Naming

- `feature/` - New features
- `fix/` - Bug fixes
- `docs/` - Documentation updates
- `refactor/` - Code refactoring
- `test/` - Test additions or updates

### Commit Messages

Follow conventional commit format:

```
type(scope): description

[optional body]

[optional footer]
```

Types:

- `feat`: New feature
- `fix`: Bug fix
- `docs`: Documentation changes
- `style`: Code style changes (formatting, etc.)
- `refactor`: Code refactoring
- `test`: Test additions or changes
- `chore`: Maintenance tasks

Examples:

```
feat(payments): add support for recurring payments
fix(webhook): correct signature verification header
docs(readme): update installation instructions
```

### Code Standards

1. **Type Safety**: Use strict types (`declare(strict_types=1);`) and type hints everywhere
2. **PSR-12**: Follow PSR-12 coding standards
3. **Documentation**: Add PHPDoc comments for all public methods and classes
4. **Testing**: Write tests for new features and bug fixes
5. **Error Handling**: Use appropriate exception types from the package

### Testing Requirements

- All new features must include unit tests
- Bug fixes must include regression tests
- Aim for high test coverage (80%+)
- Tests should be clear and well-documented

### Pull Request Process

1. Ensure all tests pass: `composer test`
2. Ensure code style is correct: `composer format`
3. Ensure static analysis passes: `composer analyse`
4. Update documentation if needed
5. Create a pull request with a clear description
6. Reference any related issues

### Pull Request Checklist

- [ ] Tests pass locally
- [ ] Code follows PSR-12 style guide
- [ ] PHPStan analysis passes
- [ ] Documentation updated (if applicable)
- [ ] CHANGELOG.md updated (if applicable)
- [ ] Commit messages follow conventional format

## Project Structure

```
src/
├── Api/              # API endpoint classes
├── Builders/         # Request builders
├── Concerns/         # Reusable traits
├── Console/          # Artisan commands
├── Credentials/      # Credential handling
├── Data/             # Data transfer objects
├── Enums/            # Enumeration classes
├── Events/           # Laravel events
├── Exceptions/       # Custom exceptions
├── Facades/          # Laravel facades
├── Infrastructure/   # Core infrastructure
├── Jobs/             # Queue jobs
├── Listeners/        # Event listeners
├── Models/           # Eloquent models
├── Objects/          # Value objects
├── Services/         # Service classes
└── Support/          # Support classes
```

## Adding New Features

### Adding a New API Endpoint

1. Create API class in `src/Api/`
2. Create service method in appropriate service class
3. Add tests
4. Update documentation

### Adding a New Service

1. Create service class in `src/Services/`
2. Register in `FlutterwaveServiceProvider`
3. Add facade method if needed
4. Write comprehensive tests
5. Update README with usage examples

## Reporting Issues

When reporting issues, please include:

- PHP version
- Laravel version
- Package version
- Steps to reproduce
- Expected behavior
- Actual behavior
- Error messages or logs (if applicable)

## Security Issues

**Do not** open public issues for security vulnerabilities. Instead, please see [SECURITY.md](SECURITY.md) for reporting security issues.

## Questions?

If you have questions, please:

1. Check the [README.md](README.md) for documentation
2. Search existing issues
3. Open a new issue with the `question` label

## License

By contributing, you agree that your contributions will be licensed under the MIT License.
