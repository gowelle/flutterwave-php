# Security Policy

## Supported Versions

We actively support the following versions of the package with security updates:

| Version | Supported          |
| ------- | ------------------ |
| 1.x.x   | :white_check_mark: |
| < 1.0   | :x:                |

## Reporting a Vulnerability

We take security vulnerabilities seriously. If you discover a security vulnerability, please follow these steps:

### 1. **Do Not** Open a Public Issue

**Do not** create a public GitHub issue for security vulnerabilities. This could expose the vulnerability to malicious actors before a fix is available.

### 2. Report Privately

Please report security vulnerabilities by emailing:

**Email:** dev@gowelle.codes

**Subject:** `[SECURITY] Flutterwave PHP Package Vulnerability`

### 3. Include Details

In your report, please include:

- A detailed description of the vulnerability
- Steps to reproduce the issue
- Potential impact and severity
- Any suggested fixes or mitigations
- Your contact information (for follow-up questions)

### 4. Response Timeline

- **Initial Response**: Within 48 hours
- **Status Update**: Within 7 days
- **Fix Timeline**: Depends on severity, typically 7-30 days

### 5. Disclosure Policy

- We will acknowledge receipt of your report within 48 hours
- We will keep you informed of the progress toward a fix
- We will notify you when the vulnerability is fixed
- We will credit you in the security advisory (unless you prefer to remain anonymous)

## Security Best Practices

When using this package, please follow these security best practices:

### Credentials

- **Never commit** API credentials to version control
- Use environment variables for all sensitive configuration
- Rotate credentials regularly
- Use different credentials for staging and production

### Webhooks

- Always enable webhook signature verification (`FLUTTERWAVE_WEBHOOK_VERIFY=true`)
- Use HTTPS for webhook endpoints
- Validate webhook payloads before processing
- Implement idempotency checks for webhook handlers

### API Requests

- Use HTTPS for all API communications (enforced by Flutterwave)
- Implement rate limiting to prevent abuse
- Log API interactions for audit purposes
- Monitor for suspicious activity

### Error Handling

- Don't expose sensitive information in error messages
- Log errors securely without exposing credentials
- Use appropriate exception handling

### Dependencies

- Keep dependencies up to date
- Review dependency security advisories regularly
- Use `composer audit` to check for known vulnerabilities

## Known Security Considerations

### Secret Hash

The `FLUTTERWAVE_SECRET_HASH` is used for webhook signature verification. Ensure:

- It's stored securely (environment variables, not in code)
- It matches the secret hash configured in your Flutterwave dashboard
- It's different for staging and production environments

### Access Tokens

Access tokens are automatically cached and refreshed. The package handles token management securely, but ensure:

- Cache storage is secure (use encrypted cache in production)
- Tokens are not logged or exposed in error messages

### Rate Limiting

The package includes rate limiting to prevent API abuse. Configure appropriate limits:

- Set reasonable limits based on your usage
- Monitor rate limit violations
- Implement backoff strategies for rate-limited requests

## Security Updates

Security updates will be:

- Released as patch versions (e.g., 1.0.1, 1.0.2)
- Documented in CHANGELOG.md
- Tagged with security advisories when appropriate

## Thank You

Thank you for helping keep Gowelle Flutterwave PHP and its users safe!
