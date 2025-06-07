# Development Workflow

## Branching Strategy

- `main`: Production-ready code
- `develop`: Integration branch for new features
- `feature/*`: Feature branches
- `hotfix/*`: Hotfix branches
- `release/*`: Release preparation branches

## Commit Message Guidelines

Commit messages should follow the conventional commits format:

```
<type>(<scope>): <description>

[optional body]
[optional footer(s)]
```

### Types

- `feat`: A new feature for the user
- `fix`: A bug fix
- `docs`: Documentation only changes
- `style`: Changes that do not affect the meaning of the code
- `refactor`: A code change that neither fixes a bug nor adds a feature
- `perf`: A code change that improves performance
- `test`: Adding missing tests
- `chore`: Changes to the build process or auxiliary tools and libraries

### Example

```
feat(controllers): add new product search functionality

- Implement search endpoint
- Add product filtering
- Add pagination support
```

## Pull Request Process

1. Create a feature branch from `develop`
2. Make your changes
3. Run tests
4. Commit your changes
5. Push to the branch
6. Create a Pull Request
7. Get at least one approval
8. Merge to `develop`
9. Delete the feature branch

## Code Review Guidelines

- Check for code style consistency
- Verify functionality
- Test for security issues
- Check documentation
- Verify test coverage
- Check performance implications

## Testing

1. Run unit tests before committing
2. Write tests for new features
3. Fix failing tests before merging
4. Maintain good test coverage

## Deployment

1. Merge to `develop`
2. Create a release branch
3. Run final tests
4. Merge to `main`
5. Deploy to production

## Security

1. Never commit sensitive data
2. Use environment variables for configuration
3. Follow security best practices
4. Regular security audits
