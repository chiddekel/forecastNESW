# AGENTS.md

## Commands
- **Build**: `composer install` then `bin/console cache:clear`
- **Test**: `./bin/phpunit` (run all tests)
- **Single test**: `./bin/phpunit tests/YourTestFile.php`
- **Lint**: No dedicated lint command - rely on PHP strict typing

## Code Style Guidelines

### PHP
- Use `declare(strict_types=1);` at top of all PHP files
- Follow PSR-4 autoloading: `App\` namespace maps to `src/`
- Use Symfony 6.4+ conventions and attributes for routes
- Controllers extend `AbstractController` and return `Response` objects
- Use dependency injection via constructor
- Import classes at top, group imports: Symfony, then third-party, then App

### Naming
- Classes: PascalCase (e.g., `WeatherController`)
- Methods: camelCase (e.g., `highlanderSays`)
- Variables: camelCase, descriptive names
- Routes: kebab-case in URL, snake_case for route names

### Error Handling
- Use Symfony's HTTP exceptions (`NotFoundHttpException`, `BadRequestHttpException`)
- Validate input parameters with route requirements
- Return appropriate HTTP status codes

### Twig
- Extend `base.html.twig`
- Use blocks: `title`, `stylesheets`, `javascripts`, `body`
- Pass data from controllers as associative arrays