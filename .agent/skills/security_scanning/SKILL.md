---
name: Security Scanning
description: Scan the codebase for security vulnerabilities in PHP and Node.js dependencies.
---

# Security Scanning

This skill helps you identify known security vulnerabilities in your project's dependencies.

## Instructions

1.  **Check PHP Dependencies**:
    Run the following command to check for vulnerabilities in PHP packages:
    ```bash
    composer audit
    ```

2.  **Check Node.js Dependencies**:
    Run the following command to check for vulnerabilities in Node.js packages:
    ```bash
    npm audit
    ```

3.  **Review Security Policy**:
    Refer to [SECURITY.md](file:///c:/Users/John%20Gowelle/Apps/flutterwave-php/SECURITY.md) for the project's security policy, reporting guidelines, and best practices.

## Best Practices

- Run these scans regularly (e.g., before every release).
- Address high and critical severity vulnerabilities immediately.
- Update dependencies to their patched versions as recommended by the audit tools.
