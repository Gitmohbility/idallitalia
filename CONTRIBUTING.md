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

## Testing

Run tests using:
```bash
./bin/phpunit
```

## Security

If you discover a security vulnerability, please send an email to security@idallitalia.com instead of opening an issue.

## License

This project is licensed under the MIT License - see the LICENSE file for details.
