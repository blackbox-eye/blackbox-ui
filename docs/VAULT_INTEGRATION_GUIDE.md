# HashiCorp Vault Integration Guide

## Overview

This guide outlines the integration of HashiCorp Vault for dynamic secrets management in the ALPHA Interface GUI platform. Vault provides secure, centralized secrets management with automatic rotation, audit logging, and fine-grained access control.

---

## Table of Contents

1. [Benefits of Vault Integration](#benefits-of-vault-integration)
2. [Architecture Overview](#architecture-overview)
3. [Prerequisites](#prerequisites)
4. [Vault Setup](#vault-setup)
5. [Secrets Migration](#secrets-migration)
6. [CI/CD Integration](#cicd-integration)
7. [Application Integration](#application-integration)
8. [Secrets Rotation](#secrets-rotation)
9. [Security Best Practices](#security-best-practices)
10. [Troubleshooting](#troubleshooting)

---

## Benefits of Vault Integration

- **Dynamic Secrets**: Automatically generated credentials with short TTL
- **Centralized Management**: Single source of truth for all secrets
- **Audit Logging**: Complete audit trail of all secret access
- **Automatic Rotation**: Credentials rotated automatically without downtime
- **Encryption at Rest**: All secrets encrypted with AES-256-GCM
- **Fine-Grained Access**: Role-based access control (RBAC)
- **Compliance**: Meets GDPR, NIS2, and enterprise security requirements

---

## Architecture Overview

```
┌─────────────────────────────────────────────────────────────┐
│                     HashiCorp Vault                         │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐     │
│  │   KV Store   │  │  Database    │  │   Transit    │     │
│  │   Secrets    │  │  Secrets     │  │  Encryption  │     │
│  └──────────────┘  └──────────────┘  └──────────────┘     │
└─────────────────────────────────────────────────────────────┘
                          │
                          │ TLS 1.3
                          │
        ┌─────────────────┼─────────────────┐
        │                 │                 │
   ┌────▼────┐       ┌───▼────┐       ┌───▼────┐
   │ CI/CD   │       │  App   │       │ Admin  │
   │ Pipeline│       │ Server │       │  CLI   │
   └─────────┘       └────────┘       └────────┘
```

---

## Prerequisites

### Vault Server

- HashiCorp Vault 1.15+ (Enterprise or Open Source)
- TLS certificates for secure communication
- Persistent storage backend (Consul, etcd, or integrated storage)

### Application Requirements

- PHP 7.4+ with cURL extension
- Network access to Vault server (port 8200)
- Service account credentials (AppRole or Token)

### CI/CD Requirements

- GitHub Actions with OIDC provider configured
- Vault GitHub Actions integration enabled

---

## Vault Setup

### 1. Install and Initialize Vault

```bash
# Download and install Vault
wget https://releases.hashicorp.com/vault/1.15.0/vault_1.15.0_linux_amd64.zip
unzip vault_1.15.0_linux_amd64.zip
sudo mv vault /usr/local/bin/

# Initialize Vault (ONE TIME ONLY)
vault operator init -key-shares=5 -key-threshold=3

# Save unseal keys and root token securely!
# Store in encrypted password manager or hardware security module
```

### 2. Configure Vault Server

**vault-config.hcl:**
```hcl
storage "file" {
  path = "/opt/vault/data"
}

listener "tcp" {
  address     = "0.0.0.0:8200"
  tls_cert_file = "/opt/vault/tls/vault.crt"
  tls_key_file  = "/opt/vault/tls/vault.key"
  tls_min_version = "tls13"
}

api_addr = "https://vault.blackbox.codes:8200"
cluster_addr = "https://vault.blackbox.codes:8201"
ui = true

# Enable audit logging
audit "file" {
  file_path = "/var/log/vault/audit.log"
}
```

### 3. Start Vault Service

```bash
# Create systemd service
sudo systemctl enable vault
sudo systemctl start vault

# Unseal Vault (required after restart)
vault operator unseal <unseal-key-1>
vault operator unseal <unseal-key-2>
vault operator unseal <unseal-key-3>
```

### 4. Enable Secrets Engines

```bash
# Login with root token
export VAULT_ADDR='https://vault.blackbox.codes:8200'
vault login <root-token>

# Enable KV v2 secrets engine for application secrets
vault secrets enable -path=alpha-interface kv-v2

# Enable database secrets engine for dynamic DB credentials
vault secrets enable database

# Enable transit engine for encryption
vault secrets enable transit
```

---

## Secrets Migration

### Current Secrets to Migrate

| Secret Name | Current Location | Vault Path | Rotation Frequency |
|-------------|------------------|------------|-------------------|
| FTP_PASSWORD | GitHub Secrets | `alpha-interface/data/ftp/credentials` | 90 days |
| SMTP_PASSWORD | .htaccess | `alpha-interface/data/smtp/credentials` | 90 days |
| RECAPTCHA_SECRET_KEY | .htaccess | `alpha-interface/data/recaptcha/keys` | 180 days |
| DB_PASSWORD | db.php | `database/creds/alpha-app` | 24 hours (dynamic) |
| CF_API_TOKEN | GitHub Secrets | `alpha-interface/data/cloudflare/tokens` | 90 days |

### Migration Steps

#### 1. Store FTP Credentials

```bash
vault kv put alpha-interface/ftp/credentials \
  host="ftp.blackbox.codes" \
  username="alpha_deploy" \
  password="NEW_SECURE_PASSWORD" \
  remote_path="/public_html"
```

#### 2. Store SMTP Credentials

```bash
vault kv put alpha-interface/smtp/credentials \
  host="smtp.protonmail.ch" \
  port="587" \
  username="ops@blackbox.codes" \
  password="NEW_APP_SPECIFIC_PASSWORD" \
  secure="tls"
```

#### 3. Store reCAPTCHA Keys

```bash
vault kv put alpha-interface/recaptcha/keys \
  site_key="NEW_RECAPTCHA_SITE_KEY" \
  secret_key="NEW_RECAPTCHA_SECRET_KEY" \
  project_id=""
```

#### 4. Configure Dynamic Database Credentials

```bash
# Configure database connection
vault write database/config/alpha-mysql \
  plugin_name=mysql-database-plugin \
  connection_url="{{username}}:{{password}}@tcp(127.0.0.1:3306)/" \
  allowed_roles="alpha-app" \
  username="vault-admin" \
  password="VAULT_ADMIN_PASSWORD"

# Create role for application
vault write database/roles/alpha-app \
  db_name=alpha-mysql \
  creation_statements="CREATE USER '{{name}}'@'%' IDENTIFIED BY '{{password}}'; GRANT SELECT, INSERT, UPDATE, DELETE ON alpha_db.* TO '{{name}}'@'%';" \
  default_ttl="24h" \
  max_ttl="72h"
```

#### 5. Store Cloudflare API Token

```bash
vault kv put alpha-interface/cloudflare/tokens \
  zone_id="YOUR_ZONE_ID" \
  api_token="NEW_CLOUDFLARE_TOKEN"
```

---

## CI/CD Integration

### GitHub Actions with Vault

#### 1. Configure Vault for GitHub OIDC

```bash
# Enable JWT auth
vault auth enable jwt

# Configure GitHub as OIDC provider
vault write auth/jwt/config \
  bound_issuer="https://token.actions.githubusercontent.com" \
  oidc_discovery_url="https://token.actions.githubusercontent.com"

# Create policy for CI/CD
vault policy write alpha-cicd - <<EOF
path "alpha-interface/data/ftp/*" {
  capabilities = ["read"]
}
path "alpha-interface/data/cloudflare/*" {
  capabilities = ["read"]
}
EOF

# Create role for GitHub Actions
vault write auth/jwt/role/alpha-github-actions \
  role_type="jwt" \
  bound_audiences="https://github.com/AlphaAcces" \
  bound_subject="repo:AlphaAcces/ALPHA-Interface-GUI:ref:refs/heads/main" \
  policies="alpha-cicd" \
  ttl=10m
```

#### 2. Update GitHub Actions Workflow

```yaml
# .github/workflows/ci.yml
jobs:
  deploy:
    name: Deploy with Vault Secrets
    runs-on: ubuntu-latest
    permissions:
      id-token: write  # Required for OIDC
      contents: read
    
    steps:
      - name: Checkout code
        uses: actions/checkout@v4
      
      - name: Import secrets from Vault
        uses: hashicorp/vault-action@v2
        with:
          url: https://vault.blackbox.codes:8200
          method: jwt
          role: alpha-github-actions
          secrets: |
            alpha-interface/data/ftp/credentials host | FTP_HOST ;
            alpha-interface/data/ftp/credentials username | FTP_USERNAME ;
            alpha-interface/data/ftp/credentials password | FTP_PASSWORD ;
            alpha-interface/data/ftp/credentials remote_path | FTP_REMOTE_PATH ;
            alpha-interface/data/cloudflare/tokens api_token | CF_API_TOKEN
      
      - name: Deploy to production
        env:
          FTP_HOST: ${{ env.FTP_HOST }}
          FTP_USERNAME: ${{ env.FTP_USERNAME }}
          FTP_PASSWORD: ${{ env.FTP_PASSWORD }}
        run: |
          # Deployment commands here
```

---

## Application Integration

### PHP Vault Client

Create `/includes/vault-client.php`:

```php
<?php
class VaultClient {
    private $vaultAddr;
    private $token;
    
    public function __construct() {
        $this->vaultAddr = getenv('VAULT_ADDR') ?: 'https://vault.blackbox.codes:8200';
        $this->token = getenv('VAULT_TOKEN');
        
        if (!$this->token) {
            // Try to read from file (AppRole authentication)
            $tokenFile = '/etc/alpha/.vault-token';
            if (file_exists($tokenFile)) {
                $this->token = trim(file_get_contents($tokenFile));
            }
        }
    }
    
    public function getSecret($path) {
        $url = $this->vaultAddr . '/v1/' . $path;
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'X-Vault-Token: ' . $this->token
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            error_log("Vault error: HTTP $httpCode for path $path");
            return null;
        }
        
        $data = json_decode($response, true);
        return $data['data']['data'] ?? null;
    }
    
    public function getDatabaseCredentials() {
        $url = $this->vaultAddr . '/v1/database/creds/alpha-app';
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'X-Vault-Token: ' . $this->token
        ]);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        $data = json_decode($response, true);
        return [
            'username' => $data['data']['username'],
            'password' => $data['data']['password'],
            'lease_id' => $data['lease_id']
        ];
    }
}
```

### Usage Example

```php
<?php
require_once __DIR__ . '/includes/vault-client.php';

$vault = new VaultClient();

// Get SMTP credentials
$smtp = $vault->getSecret('alpha-interface/data/smtp/credentials');
define('SMTP_HOST', $smtp['host']);
define('SMTP_USERNAME', $smtp['username']);
define('SMTP_PASSWORD', $smtp['password']);

// Get dynamic database credentials
$dbCreds = $vault->getDatabaseCredentials();
$conn = new mysqli('localhost', $dbCreds['username'], $dbCreds['password'], 'alpha_db');
```

---

## Secrets Rotation

### Automated Rotation Schedule

| Secret Type | Rotation Frequency | Method | Notification |
|-------------|-------------------|--------|--------------|
| Database Credentials | 24 hours | Automatic (Vault) | None (seamless) |
| FTP Password | 90 days | Manual | Email to ops@ |
| SMTP Password | 90 days | Manual | Email to ops@ |
| API Tokens | 90 days | Manual/API | Email to ops@ |
| reCAPTCHA Keys | 180 days | Manual | Email to ops@ |

### Manual Rotation Process

```bash
# 1. Generate new password
NEW_PASSWORD=$(openssl rand -base64 32)

# 2. Update Vault
vault kv put alpha-interface/ftp/credentials \
  password="$NEW_PASSWORD"

# 3. Update service provider (FTP server, SMTP, etc.)
# Via cPanel or hosting control panel

# 4. Test connection
vault kv get alpha-interface/ftp/credentials

# 5. Verify application still works
curl -I https://blackbox.codes
```

### Automated Rotation Script

Create `/scripts/rotate-secrets.sh`:

```bash
#!/bin/bash
# Automated secrets rotation script

set -euo pipefail

VAULT_ADDR="https://vault.blackbox.codes:8200"
NOTIFICATION_EMAIL="ops@blackbox.codes"

rotate_ftp_password() {
    echo "Rotating FTP password..."
    NEW_PASS=$(openssl rand -base64 32)
    
    # Update in Vault
    vault kv patch alpha-interface/ftp/credentials password="$NEW_PASS"
    
    # TODO: Update on FTP server via API
    # curl -X POST https://cpanel.host/api/ftp/change_password ...
    
    echo "FTP password rotated successfully"
}

# Run rotation based on schedule
case "${1:-}" in
    ftp)
        rotate_ftp_password
        ;;
    all)
        rotate_ftp_password
        # Add other rotations here
        ;;
    *)
        echo "Usage: $0 {ftp|all}"
        exit 1
        ;;
esac
```

---

## Security Best Practices

### 1. Access Control

- **Principle of Least Privilege**: Grant minimum required permissions
- **Service Accounts**: Use dedicated service accounts, never personal credentials
- **MFA**: Enable multi-factor authentication for administrative access
- **Token TTL**: Use short-lived tokens (max 24 hours for apps)

### 2. Network Security

- **TLS 1.3 Only**: Disable older TLS versions
- **Certificate Pinning**: Pin Vault's TLS certificate in applications
- **Firewall Rules**: Restrict Vault access to known IPs only
- **VPN/Private Network**: Access Vault only via VPN or private network

### 3. Audit and Monitoring

```bash
# Enable audit logging
vault audit enable file file_path=/var/log/vault/audit.log

# Monitor for suspicious activity
tail -f /var/log/vault/audit.log | jq 'select(.type=="response" and .auth.token_policies | contains(["root"]))'
```

### 4. Backup and Recovery

```bash
# Backup Vault data (encrypted)
vault operator raft snapshot save vault-backup-$(date +%Y%m%d).snap

# Store backups securely offsite
aws s3 cp vault-backup-*.snap s3://alpha-vault-backups/ --sse AES256

# Test restoration periodically (quarterly)
vault operator raft snapshot restore vault-backup-20250101.snap
```

### 5. Disaster Recovery

**Recovery Steps:**

1. Restore Vault from latest snapshot backup
2. Unseal Vault using unseal keys (minimum 3 of 5)
3. Verify all secrets are accessible
4. Rotate all credentials as precaution
5. Audit access logs for suspicious activity

---

## Troubleshooting

### Common Issues

#### 1. "Permission Denied" Errors

```bash
# Check token capabilities
vault token capabilities alpha-interface/data/ftp/credentials

# Verify policy attachment
vault token lookup
```

#### 2. Connection Timeout

```bash
# Test network connectivity
curl -v https://vault.blackbox.codes:8200/v1/sys/health

# Check firewall rules
sudo iptables -L -n | grep 8200
```

#### 3. Sealed Vault

```bash
# Check Vault status
vault status

# Unseal if needed
vault operator unseal
```

#### 4. Expired Token

```bash
# Renew token
vault token renew

# Or create new token
vault login -method=approle role_id=<role-id> secret_id=<secret-id>
```

---

## Migration Timeline

### Phase 1: Setup (Week 1-2)
- ✅ Install and configure Vault server
- ✅ Enable required secrets engines
- ✅ Configure authentication methods

### Phase 2: Secrets Migration (Week 3)
- ✅ Migrate non-critical secrets (reCAPTCHA, Cloudflare)
- ✅ Test application with Vault integration
- ✅ Update documentation

### Phase 3: CI/CD Integration (Week 4)
- ✅ Configure GitHub Actions OIDC
- ✅ Update deployment workflows
- ✅ Test automated deployments

### Phase 4: Dynamic Secrets (Week 5-6)
- ✅ Configure database dynamic credentials
- ✅ Update application code
- ✅ Implement automatic rotation

### Phase 5: Production (Week 7)
- ✅ Full production cutover
- ✅ Monitor for 48 hours
- ✅ Revoke old credentials

---

## Compliance Benefits

### GDPR Compliance
- ✅ Encryption at rest and in transit
- ✅ Audit logging of all access
- ✅ Right to erasure (delete secrets on demand)
- ✅ Data minimization (short-lived credentials)

### NIS2 Compliance
- ✅ Risk management (centralized secrets control)
- ✅ Incident reporting (audit logs)
- ✅ Supply chain security (controlled third-party access)
- ✅ Business continuity (backup and recovery)

---

## Support and Resources

- **HashiCorp Documentation**: https://www.vaultproject.io/docs
- **Best Practices Guide**: https://learn.hashicorp.com/vault
- **Internal Wiki**: https://blackbox.codes/wiki/vault
- **Support Contact**: ops@blackbox.codes
- **Emergency Contact**: +45 XX XX XX XX (24/7)

---

**Document Version**: 1.0  
**Last Updated**: 2025-11-23  
**Maintained By**: ALPHA-CI-Security-Agent  
**Review Schedule**: Quarterly
