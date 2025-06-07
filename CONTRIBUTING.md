# Contributing to IDallItalia

Thank you for considering contributing to IDallItalia! Here are some guidelines to help you get started:

## Getting Started

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## Development Setup

1. Clone the repository
2. Install dependencies:
   ```bash
   composer install
   npm install
   ```
3. Configure your environment:
   - Copy `config/parameters.php.example` to `config/parameters.php`
   - Update database credentials
   - Configure other environment variables as needed

## Code Style

- Follow PSR-2 coding standards
- Use PHPStan for static analysis
- Write tests for new features
- Keep PHPDoc blocks up to date
- Use meaningful variable and function names
- Keep lines under 80 characters
- Use early returns for better readability
- Avoid global variables
- Use constants for magic numbers
- Follow DRY (Don't Repeat Yourself) principle
- Use proper error handling
- Document complex logic

## Testing

1. Run tests using:
   ```bash
   ./bin/phpunit
   ```
2. Test coverage requirements:
   - New features must include tests
   - Bug fixes must include tests
   - Maintain minimum 80% coverage
   - Write both unit and integration tests

## Security

1. Never commit sensitive data:
   - Database credentials
   - API keys
   - Private keys
   - Passwords
2. Use environment variables for configuration
3. Follow security best practices:
   - Input validation
   - SQL injection prevention
   - XSS protection
   - CSRF protection
   - Secure file uploads
   - Proper error handling
4. Regular security audits
5. Keep dependencies up to date

## Commit Messages

Follow these guidelines for commit messages:

1. Use the following types:
   - `feat`: new feature for the user
   - `fix`: bug fix
   - `docs`: documentation only changes
   - `style`: changes that do not affect the meaning of the code
   - `refactor`: a code change that neither fixes a bug nor adds a feature
   - `perf`: a code change that improves performance
   - `test`: adding missing tests
   - `chore`: changes to the build process or auxiliary tools and libraries

2. Format:
   ```
   <type>(<scope>): <description>
   
   [optional body]
   [optional footer(s)]
   ```

3. Examples:
   ```
   feat(controllers): add new product search functionality
   
   - Implement search endpoint
   - Add product filtering
   - Add pagination support
   ```

## Pull Requests

1. PR Requirements:
   - Clear title and description
   - Reference related issues
   - Include screenshots if applicable
   - Include test coverage
   - Follow coding standards
   - No merge conflicts

2. Review Process:
   - At least one approval required
   - Code review checklist
   - Security review
   - Performance review
   - Documentation review

3. Merge Strategy:
   - Use squash merge for feature branches
   - Use rebase for hotfixes
   - Maintain clean history

## Branching Strategy

1. Main Branches:
   - `main`: Production-ready code
   - `develop`: Integration branch

2. Feature Branches:
   - Format: `feature/<name>`
   - Keep focused and small
   - Regularly merge from develop

3. Hotfix Branches:
   - Format: `hotfix/<name>`
   - Direct to main
   - High priority

## Code Review

1. Review Checklist:
   - Code style
   - Functionality
   - Security
   - Performance
   - Documentation
   - Test coverage
   - Error handling

2. Review Process:
   - At least one approval
   - Address all comments
   - Fix merge conflicts
   - Update documentation

## Documentation

1. Required Documentation:
   - API documentation
   - Setup instructions
   - Configuration guide
   - Security guidelines
   - Error reference

2. Documentation Standards:
   - Clear and concise
   - Keep updated
   - Include examples
   - Use consistent format
   - Include version information

## Security

If you discover a security vulnerability, please send an email to security@idallitalia.com instead of opening an issue.

## License

This project is licensed under the MIT License - see the LICENSE file for details.
