# PHPMailer Library

**Version**: 6.9.1  
**License**: LGPL 2.1  
**Source**: https://github.com/PHPMailer/PHPMailer

## Purpose

This is the PHPMailer library used for SMTP email authentication when configured. It provides a robust, secure way to send emails via SMTP servers with authentication.

## Files Included

- `PHPMailer.php` - Main email class
- `SMTP.php` - SMTP protocol implementation
- `Exception.php` - Error handling
- `OAuth.php` - OAuth authentication support
- `OAuthTokenProvider.php` - OAuth token interface
- `POP3.php` - POP3 protocol support
- `DSNConfigurator.php` - Delivery Status Notification

## Usage in This Project

PHPMailer is loaded automatically by `includes/mail-helper.php` when SMTP credentials are configured via environment variables:

```php
SMTP_HOST=smtp.protonmail.ch
SMTP_PORT=587
SMTP_USERNAME=ops@blackbox.codes
SMTP_PASSWORD=your-app-password
SMTP_SECURE=tls
```

If no SMTP credentials are configured, the system uses PHP's native `mail()` function instead.

## Why PHPMailer?

1. **Industry Standard** - Used by millions of websites
2. **Actively Maintained** - Regular security updates
3. **Well Tested** - Battle-tested in production
4. **Standalone** - No composer or dependencies needed
5. **Secure** - Security audited and maintained

## Security

- PHPMailer is maintained by a security-conscious team
- Regular security audits and updates
- LGPL 2.1 license (commercial-friendly)
- No known vulnerabilities in version 6.9.1

## Documentation

Full documentation: https://github.com/PHPMailer/PHPMailer/wiki

## License

PHPMailer is distributed under the LGPL 2.1 license. See: https://github.com/PHPMailer/PHPMailer/blob/master/LICENSE

## Version History

- v6.9.1 (2023-11-25): Current version in use
- Regular security and feature updates
- Maintained by: Marcus Bointon and contributors

## Do Not Modify

These files are third-party library code and should not be modified. If updates are needed:

1. Download latest stable release from GitHub
2. Replace files in this directory
3. Test thoroughly before deploying

---

**Added**: 2025-11-20  
**Purpose**: SMTP support for contact form  
**Used by**: includes/mail-helper.php
