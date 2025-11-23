# Security Policy

## Supported Versions

We take security seriously and actively maintain the following versions:

| Version | Supported          |
| ------- | ------------------ |
| 1.0.x   | :white_check_mark: |
| < 1.0   | :x:                |

## Reporting a Vulnerability

If you discover a security vulnerability in ALPHA Interface GUI, please follow these steps:

### 1. **Do Not** Open a Public Issue

To protect our users, please **do not** disclose security vulnerabilities publicly until we've had a chance to address them.

### 2. Report Privately

Send your report to: **ops@blackbox.codes**

Include the following information:
- Description of the vulnerability
- Steps to reproduce the issue
- Potential impact
- Any suggested fixes (if applicable)
- Your contact information for follow-up

### 3. Response Timeline

- **Initial Response**: We will acknowledge your report within 48 hours
- **Status Update**: We will provide a status update within 7 days
- **Fix Timeline**: Critical vulnerabilities will be addressed within 30 days

### 4. Disclosure Policy

- We request that you give us reasonable time to fix the vulnerability before public disclosure
- Once a fix is deployed, we will coordinate with you on the disclosure timeline
- We may credit you in our security advisories (unless you prefer to remain anonymous)

## Security Best Practices

When deploying ALPHA Interface GUI:

### Authentication
- Use strong, unique passwords for all accounts
- Implement 2FA/MFA where possible
- Rotate credentials regularly (see README.md for secret rotation guide)

### Database Security
- Use prepared statements (already implemented)
- Restrict database user permissions
- Keep database credentials in environment variables or secrets management

### Web Server
- Use HTTPS/TLS for all connections
- Configure proper security headers
- Keep PHP and web server software up to date
- Disable directory listing

### File Permissions
- Restrict write permissions on configuration files
- Ensure `.env` files are not web-accessible
- Use `.htaccess` or equivalent to protect sensitive directories

### CI/CD Security
- Store all credentials in GitHub Secrets
- Use FTPS/SFTP for deployments (not plain FTP)
- Limit access to deployment secrets
- Enable branch protection rules

## Security Features

ALPHA Interface GUI includes:

- **SQL Injection Protection**: Prepared statements with MySQLi/PDO
- **XSS Protection**: Input sanitization and output encoding
- **CSRF Protection**: Session-based tokens
- **Session Management**: Secure cookies and session handling
- **Role-Based Access Control**: Admin vs. Agent permissions
- **Audit Logging**: Change tracking and access logs
- **Password Hashing**: bcrypt/password_hash() (production mode)

## Automated Security Scanning

We use:
- **CodeQL**: Automated code scanning for PHP and JavaScript vulnerabilities
- **Dependency Scanning**: Regular checks for vulnerable dependencies
- **CI/CD Security Checks**: Pre-deployment security validation

## Third-Party Dependencies

We regularly review and update third-party dependencies. If you discover a vulnerability in a dependency we use, please report it following the process above.

## Security Updates

Security updates are released as soon as possible after a vulnerability is confirmed and fixed. Updates are announced via:
- GitHub Security Advisories
- Release notes
- Email to security@blackbox.codes subscribers

## Questions?

For general security questions or concerns, contact: **ops@blackbox.codes**

For urgent security matters, mark your email subject with **[SECURITY - URGENT]**.

---

*Last updated: 2025-11-23*
