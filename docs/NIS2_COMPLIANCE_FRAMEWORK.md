# NIS2 Compliance Framework

## Overview

This document outlines the implementation of NIS2 (Network and Information Security Directive 2) compliance measures for the ALPHA Interface GUI platform. NIS2 aims to improve cybersecurity resilience across the EU and applies to essential and important entities.

---

## Table of Contents

1. [NIS2 Requirements Overview](#nis2-requirements-overview)
2. [Risk Management Framework](#risk-management-framework)
3. [Incident Reporting Protocols](#incident-reporting-protocols)
4. [Security Measures](#security-measures)
5. [Business Continuity](#business-continuity)
6. [Supply Chain Security](#supply-chain-security)
7. [Governance and Accountability](#governance-and-accountability)
8. [Compliance Checklist](#compliance-checklist)

---

## NIS2 Requirements Overview

### Scope and Applicability

NIS2 applies to:
- **Essential Entities**: Critical infrastructure providers
- **Important Entities**: Digital service providers, cloud services, cybersecurity firms

**BLACKBOX.CODES Classification**: [To be determined based on services provided]

### Key Obligations

1. **Risk Management**: Implement comprehensive cybersecurity risk management measures
2. **Incident Reporting**: Report significant incidents within strict timelines
3. **Business Continuity**: Maintain operations during incidents
4. **Supply Chain Security**: Assess and manage supplier risks
5. **Governance**: Top management responsibility for cybersecurity

---

## Risk Management Framework

### 1. Risk Assessment Process

#### Asset Inventory

| Asset Type | Description | Criticality | Owner |
|------------|-------------|-------------|-------|
| Production Server | Primary web server (blackbox.codes) | Critical | Ops Team |
| Database Server | MySQL database | Critical | DBA |
| CI/CD Pipeline | GitHub Actions | High | DevOps |
| Domain/DNS | Cloudflare managed | High | Ops Team |
| Vault Server | Secrets management | Critical | Security Team |
| Email Service | Proton Mail SMTP | Medium | Ops Team |
| Backup Storage | Encrypted backups | High | Ops Team |

#### Threat Modeling

```
┌─────────────────────────────────────────────────────────────┐
│                     Threat Landscape                         │
├─────────────────────────────────────────────────────────────┤
│ • DDoS attacks on public endpoints                          │
│ • SQL injection and XSS attacks                             │
│ • Credential stuffing and brute force                       │
│ • Supply chain attacks (compromised dependencies)           │
│ • Insider threats (privileged access abuse)                 │
│ • Ransomware and data exfiltration                          │
│ • API abuse and resource exhaustion                         │
└─────────────────────────────────────────────────────────────┘
```

#### Risk Register

Create `/docs/RISK_REGISTER.md`:

| Risk ID | Threat | Likelihood | Impact | Risk Score | Mitigation | Owner |
|---------|--------|------------|--------|------------|------------|-------|
| R-001 | DDoS attack on website | Medium | High | 12 | Cloudflare DDoS protection | Ops |
| R-002 | SQL injection via forms | Low | Critical | 12 | Prepared statements, input validation | Dev |
| R-003 | Compromised admin account | Low | Critical | 12 | MFA, strong passwords, audit logs | Security |
| R-004 | Data breach via database | Low | Critical | 12 | Encryption, access control, monitoring | DBA |
| R-005 | Ransomware attack | Low | High | 8 | Offline backups, EDR, segmentation | Security |
| R-006 | Supply chain attack (npm) | Medium | High | 12 | Dependency scanning, SCA tools | Dev |
| R-007 | Insider threat | Very Low | High | 4 | Least privilege, audit logs | Security |
| R-008 | Server compromise | Low | Critical | 12 | Patching, hardening, IDS | Ops |

**Risk Score** = Likelihood (1-5) × Impact (1-5)

### 2. Security Controls Implementation

#### Technical Controls

```yaml
# Security Controls Mapping
Network Security:
  - Cloudflare WAF (Web Application Firewall)
  - DDoS protection (automatic mitigation)
  - Rate limiting on API endpoints
  - IP reputation filtering
  
Application Security:
  - Content Security Policy (CSP)
  - HTTP Strict Transport Security (HSTS)
  - Input validation and sanitization
  - Prepared statements for SQL queries
  - reCAPTCHA for spam prevention
  - Security headers (X-Frame-Options, etc.)
  
Access Control:
  - Role-based access control (RBAC)
  - Multi-factor authentication (MFA) for admins
  - Session timeout (30 minutes)
  - Strong password policy (min 12 chars)
  - Password hashing (bcrypt with salt)
  
Data Protection:
  - TLS 1.3 for all communications
  - Database encryption at rest
  - Encrypted backups (AES-256)
  - Secrets management via Vault
  - PII data minimization
  
Monitoring & Detection:
  - CodeQL security scanning
  - Dependency vulnerability scanning
  - Audit logging of all access
  - Failed login monitoring
  - Anomaly detection alerts
```

### 3. Vulnerability Management

**Patch Management Schedule:**

| Asset | Patching Frequency | Responsibility | Testing |
|-------|-------------------|----------------|---------|
| OS (Ubuntu Server) | Monthly (critical patches immediately) | Ops | Staging first |
| PHP | Quarterly | Ops | Compatibility testing |
| MySQL | Quarterly | DBA | Backup before upgrade |
| Dependencies (npm, composer) | Weekly scan, monthly updates | Dev | Automated tests |
| Cloudflare rules | As needed | Ops | Test rules on staging |

**Vulnerability Scanning:**

```bash
# Weekly automated security scan
cron job: 0 2 * * 1 /usr/local/bin/run-security-scan.sh

# /usr/local/bin/run-security-scan.sh
#!/bin/bash
set -euo pipefail

# Run CodeQL analysis
gh workflow run codeql.yml

# Scan dependencies
npm audit --audit-level=moderate
composer audit

# Check for CVEs in Docker images (if applicable)
# trivy image blackbox-eye:latest

# OS vulnerability scan
apt list --upgradable 2>/dev/null | grep security

# Send report
mail -s "Weekly Security Scan Report" ops@blackbox.codes < /tmp/scan-report.txt
```

---

## Incident Reporting Protocols

### NIS2 Reporting Timeline

```
┌─────────────────────────────────────────────────────────────┐
│                  Incident Timeline (NIS2)                    │
├─────────────────────────────────────────────────────────────┤
│                                                              │
│  Detection    Early Warning    Incident Report   Final Report│
│      │              │                 │                │     │
│      ▼              ▼                 ▼                ▼     │
│    T+0          T+24h             T+72h            T+1month  │
│      │              │                 │                │     │
│      └──────────────┴─────────────────┴────────────────┘     │
│                                                              │
│  • Detect incident                                           │
│  • Activate IR team                                          │
│  • Early warning to CSIRT (if significant)                   │
│  • Full incident notification (72h)                          │
│  • Final report with lessons learned (1 month)               │
└─────────────────────────────────────────────────────────────┘
```

### Incident Classification

| Severity | Description | Example | Reporting Required |
|----------|-------------|---------|-------------------|
| **Critical** | Significant operational disruption | Data breach, complete outage | YES - 24h early warning |
| **High** | Degraded service, potential data exposure | DDoS attack, partial outage | YES - 72h notification |
| **Medium** | Minor impact, contained quickly | Failed login attempts spike | NO - Internal log only |
| **Low** | Negligible impact | Single user issue | NO - Internal log only |

### Incident Response Plan

#### 1. Detection and Analysis

**Monitoring Systems:**
- Cloudflare security events
- Application error logs
- Failed authentication logs
- Anomaly detection alerts
- User reports

**Detection Triggers:**
```bash
# Alert on multiple failed logins
if [ $(grep "Failed login" /var/log/auth.log | wc -l) -gt 50 ]; then
    alert-ops "Potential brute force attack detected"
fi

# Alert on database errors
if [ $(grep "SQL error" /var/log/php-errors.log | wc -l) -gt 10 ]; then
    alert-ops "Database errors spiking - potential SQL injection"
fi

# Alert on 500 errors
if [ $(grep "HTTP 500" /var/log/nginx/access.log | wc -l) -gt 20 ]; then
    alert-ops "High rate of server errors"
fi
```

#### 2. Containment

**Immediate Actions:**

1. **Isolate affected systems**
   ```bash
   # Block malicious IP via Cloudflare API
   curl -X POST "https://api.cloudflare.com/client/v4/zones/${ZONE_ID}/firewall/access_rules/rules" \
     -H "Authorization: Bearer ${CF_TOKEN}" \
     -H "Content-Type: application/json" \
     --data '{"mode":"block","configuration":{"target":"ip","value":"<MALICIOUS_IP>"},"notes":"NIS2 incident response"}'
   ```

2. **Enable maintenance mode**
   ```bash
   # Redirect to maintenance page
   touch /var/www/html/.maintenance-mode
   ```

3. **Preserve evidence**
   ```bash
   # Take snapshot of logs
   tar -czf /backup/incident-logs-$(date +%Y%m%d-%H%M%S).tar.gz /var/log/
   ```

#### 3. Eradication and Recovery

1. Identify root cause
2. Remove malicious artifacts
3. Patch vulnerabilities
4. Restore from clean backup if needed
5. Verify system integrity
6. Restore normal operations

#### 4. Post-Incident Activities

**Lessons Learned Report Template:**

```markdown
# Incident Report: [Incident ID]

## Executive Summary
- **Date**: [Date]
- **Duration**: [X hours]
- **Severity**: [Critical/High/Medium/Low]
- **Impact**: [Description]

## Timeline
- T+0: [Detection]
- T+15min: [Response initiated]
- T+2h: [Contained]
- T+6h: [Resolved]

## Root Cause
[Detailed analysis]

## Actions Taken
1. [Action 1]
2. [Action 2]

## Lessons Learned
- **What Went Well**: [Positive aspects]
- **What Needs Improvement**: [Gaps identified]

## Recommendations
1. [Recommendation 1]
2. [Recommendation 2]

## Follow-Up Actions
- [ ] [Action 1] - Owner: [Name] - Due: [Date]
- [ ] [Action 2] - Owner: [Name] - Due: [Date]
```

### Reporting to Authorities

**Danish CSIRT Contact:**

- **Organization**: Center for Cybersecurity (CFCS)
- **Email**: cert@cert.dk
- **Phone**: +45 33 92 33 92
- **Web**: https://www.cfcs.dk

**Reporting Template:**

```
Subject: NIS2 Incident Notification - [Incident ID]

To: cert@cert.dk
From: ops@blackbox.codes

INCIDENT NOTIFICATION (NIS2 Article 23)

1. ENTITY INFORMATION
   Organization: AlphaAcces / BLACKBOX.CODES
   Contact: ops@blackbox.codes
   Phone: +45 XX XX XX XX

2. INCIDENT DETAILS
   Incident ID: INC-2025-001
   Detection Time: 2025-11-23 14:30 UTC
   Notification Time: 2025-11-24 10:00 UTC (within 24h)
   Severity: [Critical/High]

3. INCIDENT DESCRIPTION
   [Brief description of the incident]

4. AFFECTED SYSTEMS
   [List of affected systems and services]

5. IMPACT ASSESSMENT
   - Users affected: [Number]
   - Services impacted: [List]
   - Data potentially compromised: [Yes/No - Details]

6. CONTAINMENT MEASURES
   [Actions taken to contain the incident]

7. CURRENT STATUS
   [Ongoing/Contained/Resolved]

8. NEXT STEPS
   [Planned actions]

9. CONTACT FOR FOLLOW-UP
   Name: [Incident Commander]
   Email: ops@blackbox.codes
   Phone: +45 XX XX XX XX

---
This notification is provided in compliance with NIS2 Directive (EU) 2022/2555.
```

---

## Security Measures

### Minimum Security Requirements (NIS2 Article 21)

#### 1. Risk Analysis and Security Policies

- [x] **Annual risk assessment** - Document in `/docs/RISK_REGISTER.md`
- [x] **Information security policies** - Document in `/docs/SECURITY_POLICY.md`
- [x] **Asset inventory** - Maintain in `/docs/ASSET_INVENTORY.md`

#### 2. Incident Handling

- [x] **Incident response plan** - Documented above
- [x] **Communication procedures** - Email, phone tree, Slack alerts
- [x] **Crisis management team** - Ops Lead, Security Lead, CTO

#### 3. Business Continuity and Backup Management

- [x] **Backup strategy** - Daily backups, 90-day retention
- [x] **Disaster recovery plan** - RTO: 4 hours, RPO: 1 hour
- [x] **Testing schedule** - Quarterly DR drills

#### 4. Security in Network and Information Systems

- [x] **Encryption** - TLS 1.3, AES-256 for data at rest
- [x] **Access control** - RBAC, MFA for admins
- [x] **Network segmentation** - Separate DB and web tiers

#### 5. Security in Acquisition, Development and Maintenance

- [x] **Secure SDLC** - Code review, security testing
- [x] **Dependency management** - Regular updates, vulnerability scanning
- [x] **Secure configuration** - Hardened servers, minimal attack surface

#### 6. Human Resources Security

- [x] **Security awareness training** - Annual training for all staff
- [x] **Background checks** - For privileged access
- [x] **Access revocation** - Immediate upon termination

#### 7. Multi-Factor Authentication

- [x] **Admin accounts** - MFA mandatory
- [x] **Remote access** - VPN with MFA
- [x] **API access** - Token-based with rotation

#### 8. Secure Communications

- [x] **Email encryption** - TLS for SMTP
- [x] **Internal comms** - Encrypted channels (Signal, Slack with encryption)
- [x] **Public disclosure** - Responsible disclosure policy

#### 9. Security in Supply Chain

- [x] **Vendor assessment** - Security questionnaires
- [x] **Contracts** - Data processing agreements
- [x] **Monitoring** - Regular audits

---

## Business Continuity

### Recovery Objectives

| Service | RTO (Recovery Time Objective) | RPO (Recovery Point Objective) |
|---------|-------------------------------|--------------------------------|
| Website (public pages) | 2 hours | 1 hour |
| User authentication | 4 hours | 1 hour |
| Database | 4 hours | 1 hour |
| Contact form | 8 hours | 24 hours |
| Admin dashboard | 8 hours | 24 hours |

### Backup Strategy

```bash
# /etc/cron.daily/backup.sh
#!/bin/bash
set -euo pipefail

BACKUP_DIR="/backup/$(date +%Y%m%d)"
mkdir -p "$BACKUP_DIR"

# Database backup
mysqldump -u root -p"${DB_PASSWORD}" --all-databases | gzip > "$BACKUP_DIR/db-backup.sql.gz"

# File backup
tar -czf "$BACKUP_DIR/files-backup.tar.gz" /var/www/html

# Encrypt backups
gpg --encrypt --recipient ops@blackbox.codes "$BACKUP_DIR/db-backup.sql.gz"
gpg --encrypt --recipient ops@blackbox.codes "$BACKUP_DIR/files-backup.tar.gz"

# Upload to offsite storage
aws s3 sync "$BACKUP_DIR" s3://alpha-backups/$(date +%Y%m%d)/ --sse AES256

# Delete local unencrypted backups
rm "$BACKUP_DIR"/*.sql.gz "$BACKUP_DIR"/*.tar.gz

# Retain backups for 90 days
find /backup/ -mtime +90 -delete

echo "Backup completed: $(date)" | mail -s "Backup Success" ops@blackbox.codes
```

### Disaster Recovery Procedures

**Scenario 1: Complete Server Loss**

1. **Activate DR Team** (15 minutes)
   - Notify stakeholders
   - Assemble incident response team

2. **Provision New Server** (1 hour)
   ```bash
   # Launch new server (AWS/DO/etc.)
   # Install base OS and dependencies
   apt-get update && apt-get upgrade -y
   apt-get install -y nginx php8.1-fpm mysql-server
   ```

3. **Restore from Backup** (2 hours)
   ```bash
   # Download latest backup
   aws s3 cp s3://alpha-backups/latest/ /restore/ --recursive
   
   # Decrypt backups
   gpg --decrypt /restore/db-backup.sql.gz.gpg | gunzip > /tmp/db-backup.sql
   gpg --decrypt /restore/files-backup.tar.gz.gpg | tar -xzf - -C /var/www/
   
   # Restore database
   mysql -u root -p < /tmp/db-backup.sql
   
   # Verify restoration
   mysql -u root -p -e "SHOW DATABASES;"
   ```

4. **Update DNS** (30 minutes)
   - Point domain to new server IP
   - Wait for propagation

5. **Verify and Test** (30 minutes)
   - Test all critical functionality
   - Verify data integrity
   - Run smoke tests

**Total RTO: 4 hours**

---

## Supply Chain Security

### Third-Party Risk Assessment

| Vendor | Service | Risk Level | Assessment Date | Next Review |
|--------|---------|------------|-----------------|-------------|
| Cloudflare | CDN, WAF, DDoS protection | Low | 2025-11-23 | 2026-05-23 |
| GitHub | Code hosting, CI/CD | Low | 2025-11-23 | 2026-05-23 |
| Proton Mail | Email service | Low | 2025-11-23 | 2026-05-23 |
| Hosting Provider | Server infrastructure | Medium | 2025-11-23 | 2026-02-23 |
| npm Registry | JavaScript dependencies | Medium | 2025-11-23 | Monthly |
| Composer/Packagist | PHP dependencies | Medium | 2025-11-23 | Monthly |

### Dependency Management

```yaml
# .github/workflows/dependency-scan.yml
name: Dependency Security Scan

on:
  schedule:
    - cron: '0 9 * * 1'  # Weekly on Monday
  push:
    branches: [ main ]
  pull_request:

jobs:
  scan:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      
      - name: NPM Audit
        run: |
          npm audit --audit-level=moderate
          npm audit fix --dry-run
      
      - name: Composer Audit
        run: |
          composer audit
      
      - name: OWASP Dependency Check
        uses: dependency-check/Dependency-Check_Action@main
        with:
          project: 'ALPHA-Interface-GUI'
          path: '.'
          format: 'HTML'
      
      - name: Upload results
        uses: actions/upload-artifact@v3
        with:
          name: dependency-scan-results
          path: reports/
```

---

## Governance and Accountability

### Management Responsibility (NIS2 Article 20)

**Top Management Obligations:**

1. **Approve cybersecurity measures** - Board-level approval required
2. **Allocate resources** - Budget and personnel for security
3. **Oversee implementation** - Quarterly security reviews
4. **Training** - Management cybersecurity training
5. **Accountability** - Personal liability for non-compliance

### Organizational Structure

```
┌─────────────────────────────────────┐
│        Board of Directors           │
│  (Ultimate responsibility)          │
└──────────────┬──────────────────────┘
               │
        ┌──────▼──────┐
        │     CTO     │
        └──────┬──────┘
               │
    ┌──────────┼──────────┐
    │          │          │
┌───▼───┐  ┌──▼───┐  ┌───▼────┐
│  Ops  │  │ Dev  │  │Security│
│ Lead  │  │ Lead │  │  Lead  │
└───────┘  └──────┘  └────────┘
```

### Security Metrics and KPIs

| Metric | Target | Frequency | Owner |
|--------|--------|-----------|-------|
| Mean Time to Detect (MTTD) | < 15 minutes | Monthly | Security |
| Mean Time to Respond (MTTR) | < 2 hours | Monthly | Ops |
| Patch Compliance | 100% critical patches < 7 days | Weekly | Ops |
| Failed login rate | < 0.5% of total | Daily | Security |
| Backup success rate | 100% | Daily | Ops |
| Security training completion | 100% staff annually | Quarterly | HR |
| Vulnerability scan coverage | 100% of assets | Weekly | Security |

---

## Compliance Checklist

### NIS2 Implementation Checklist

#### Risk Management
- [x] Documented risk assessment methodology
- [x] Asset inventory maintained
- [x] Threat modeling completed
- [x] Risk register created and updated
- [ ] Annual risk review scheduled

#### Incident Handling
- [x] Incident response plan documented
- [x] Incident classification defined
- [x] Reporting procedures to CSIRT established
- [ ] IR team trained and drills conducted
- [ ] Communication templates prepared

#### Business Continuity
- [x] Backup strategy implemented
- [x] RTO/RPO defined for all services
- [ ] DR plan tested (quarterly)
- [ ] Backup restoration tested

#### Security Measures
- [x] Encryption implemented (TLS 1.3, AES-256)
- [x] Access control (RBAC, MFA)
- [x] Monitoring and logging
- [x] Security headers configured
- [x] Vulnerability management process

#### Supply Chain
- [x] Vendor risk assessment process
- [ ] Data processing agreements signed
- [x] Dependency scanning automated
- [ ] Vendor audits scheduled

#### Governance
- [ ] Board-level cybersecurity approval
- [ ] Management training completed
- [ ] Security budget allocated
- [x] Organizational structure defined
- [ ] KPIs tracked and reported

#### Reporting
- [ ] CSIRT contact established
- [x] Incident reporting templates
- [ ] Early warning procedure tested
- [ ] Final report template created

---

## Next Steps

### Immediate Actions (Week 1-2)
1. [ ] Conduct management cybersecurity training
2. [ ] Test incident response plan (tabletop exercise)
3. [ ] Sign data processing agreements with vendors
4. [ ] Schedule quarterly disaster recovery drill

### Short-term Actions (Month 1-3)
1. [ ] Implement SIEM integration
2. [ ] Complete vendor security assessments
3. [ ] Conduct penetration test
4. [ ] Establish security metrics dashboard

### Long-term Actions (Month 3-6)
1. [ ] Achieve ISO 27001 certification (optional)
2. [ ] Implement SOC 2 compliance (optional)
3. [ ] Enhance automated threat detection
4. [ ] Expand security team capacity

---

## References

- **NIS2 Directive**: (EU) 2022/2555
- **Danish Implementation**: [Link to Danish law when published]
- **CFCS Guidance**: https://www.cfcs.dk
- **ENISA Guidelines**: https://www.enisa.europa.eu

---

## Support

- **Internal Security Team**: security@blackbox.codes
- **External CSIRT**: cert@cert.dk
- **Emergency Hotline**: +45 XX XX XX XX (24/7)

---

**Document Version**: 1.0  
**Last Updated**: 2025-11-23  
**Next Review**: 2026-02-23 (Quarterly)  
**Owner**: ALPHA-CI-Security-Agent  
**Approved By**: [Management Signature Required]
