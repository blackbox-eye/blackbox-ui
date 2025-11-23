# SIEM Integration Guide - Blackbox EYE

## Overview

This guide provides comprehensive documentation for integrating Blackbox EYE with Security Information and Event Management (SIEM) systems. It covers log collection, secure transmission, correlation rules, and operational procedures.

---

## Table of Contents

1. [Architecture Overview](#architecture-overview)
2. [Supported SIEM Platforms](#supported-siem-platforms)
3. [Log Sources and Events](#log-sources-and-events)
4. [Integration Methods](#integration-methods)
5. [Secure Log Transmission](#secure-log-transmission)
6. [Operational Bridge Setup](#operational-bridge-setup)
7. [Correlation Rules](#correlation-rules)
8. [Alerting and Response](#alerting-and-response)
9. [Troubleshooting](#troubleshooting)

---

## Architecture Overview

```
┌─────────────────────────────────────────────────────────────┐
│                   Blackbox EYE Platform                      │
├─────────────────────────────────────────────────────────────┤
│                                                              │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐  ┌──────────┐   │
│  │   Web    │  │   API    │  │   Auth   │  │  Admin   │   │
│  │  Server  │  │  Server  │  │  Module  │  │  Panel   │   │
│  └─────┬────┘  └─────┬────┘  └─────┬────┘  └─────┬────┘   │
│        │             │             │             │         │
│        └─────────────┴─────────────┴─────────────┘         │
│                          │                                  │
│                    ┌─────▼─────┐                           │
│                    │  Log      │                           │
│                    │Aggregator │                           │
│                    └─────┬─────┘                           │
│                          │                                  │
└──────────────────────────┼──────────────────────────────────┘
                           │
                           │ Encrypted Transmission
                           │ (TLS 1.3, mTLS)
                           │
            ┌──────────────┼──────────────┐
            │              │              │
       ┌────▼────┐   ┌─────▼─────┐  ┌────▼────┐
       │ Splunk  │   │  ELK/     │  │ Azure   │
       │Enterprise│   │Elastic   │  │Sentinel │
       └─────────┘   │  Stack    │  └─────────┘
                     └───────────┘
                           │
                     ┌─────▼─────┐
                     │   SOC     │
                     │  Analysts │
                     └───────────┘
```

---

## Supported SIEM Platforms

### 1. Splunk Enterprise / Splunk Cloud

**Advantages:**
- Industry-leading analytics and visualization
- Extensive app ecosystem
- Powerful SPL (Search Processing Language)
- Strong correlation capabilities

**Integration Method:** HTTP Event Collector (HEC)

### 2. Elastic Stack (ELK)

**Advantages:**
- Open source and cost-effective
- Highly scalable
- Real-time search and analytics
- Kibana for visualization

**Integration Method:** Filebeat → Logstash → Elasticsearch

### 3. Azure Sentinel

**Advantages:**
- Cloud-native SIEM
- AI-powered threat detection
- Integration with Microsoft ecosystem
- Automated response (SOAR capabilities)

**Integration Method:** Azure Monitor Agent / Log Analytics API

### 4. QRadar

**Advantages:**
- Strong compliance features
- Behavioral analytics
- Network flow analysis

**Integration Method:** Syslog / LEEF format

### 5. ArcSight

**Advantages:**
- Enterprise-scale performance
- Advanced correlation
- Compliance reporting

**Integration Method:** SmartConnector / CEF format

---

## Log Sources and Events

### Event Categories

#### 1. Authentication Events

```json
{
  "timestamp": "2025-11-23T18:45:23.123Z",
  "event_type": "authentication",
  "action": "login_attempt",
  "result": "success",
  "user": {
    "username": "agent_001",
    "role": "agent",
    "id": "12345"
  },
  "source": {
    "ip": "203.0.113.42",
    "user_agent": "Mozilla/5.0 ...",
    "country": "DK",
    "city": "Copenhagen"
  },
  "session": {
    "id": "sess_abc123",
    "duration": 0,
    "mfa_used": true
  }
}
```

**Event Types:**
- `login_attempt` (success/failure)
- `logout`
- `password_change`
- `mfa_challenge`
- `session_timeout`
- `account_locked`

#### 2. Security Events

```json
{
  "timestamp": "2025-11-23T18:45:30.456Z",
  "event_type": "security",
  "severity": "high",
  "category": "suspicious_activity",
  "description": "Multiple failed login attempts",
  "details": {
    "failed_attempts": 15,
    "timeframe_seconds": 60,
    "source_ip": "198.51.100.23",
    "target_user": "admin",
    "blocked": true
  },
  "mitigation": "IP temporarily blocked for 30 minutes"
}
```

**Event Types:**
- `brute_force_detected`
- `sql_injection_attempt`
- `xss_attempt`
- `csrf_token_mismatch`
- `rate_limit_exceeded`
- `suspicious_activity`
- `privilege_escalation_attempt`

#### 3. Access Events

```json
{
  "timestamp": "2025-11-23T18:45:45.789Z",
  "event_type": "access",
  "resource": "/admin/users",
  "action": "view",
  "user": {
    "username": "admin_john",
    "role": "administrator",
    "id": "67890"
  },
  "result": "authorized",
  "source_ip": "192.0.2.15"
}
```

**Event Types:**
- `page_access`
- `api_call`
- `file_download`
- `data_export`
- `settings_change`
- `user_management`

#### 4. Data Events

```json
{
  "timestamp": "2025-11-23T18:46:00.012Z",
  "event_type": "data",
  "action": "contact_form_submission",
  "data_classification": "pii",
  "details": {
    "form_id": "contact_main",
    "fields_submitted": ["name", "email", "message"],
    "recaptcha_score": 0.9,
    "stored_location": "logs/contact-submissions.log",
    "retention_days": 730
  },
  "source_ip": "203.0.113.99"
}
```

**Event Types:**
- `contact_form_submission`
- `user_data_created`
- `user_data_modified`
- `user_data_deleted`
- `data_export_requested`
- `gdpr_request`

#### 5. System Events

```json
{
  "timestamp": "2025-11-23T18:46:15.345Z",
  "event_type": "system",
  "severity": "warning",
  "component": "database",
  "message": "High query latency detected",
  "metrics": {
    "avg_query_time_ms": 2500,
    "slow_queries_count": 42,
    "connections_active": 85
  }
}
```

**Event Types:**
- `application_error`
- `database_error`
- `service_start`
- `service_stop`
- `backup_completed`
- `backup_failed`
- `disk_space_warning`
- `high_cpu_usage`

#### 6. Audit Events

```json
{
  "timestamp": "2025-11-23T18:46:30.678Z",
  "event_type": "audit",
  "action": "user_role_changed",
  "actor": {
    "username": "admin_jane",
    "role": "administrator",
    "id": "11111"
  },
  "target": {
    "username": "agent_005",
    "old_role": "agent",
    "new_role": "senior_agent",
    "id": "22222"
  },
  "reason": "Promotion after 6 months performance review"
}
```

**Event Types:**
- `user_created`
- `user_modified`
- `user_deleted`
- `role_changed`
- `permission_granted`
- `permission_revoked`
- `configuration_changed`

---

## Integration Methods

### Method 1: Splunk HTTP Event Collector (HEC)

#### Configuration

**1. Create HEC Token in Splunk:**

```bash
# In Splunk Web UI:
# Settings > Data Inputs > HTTP Event Collector > Add New
# Copy the token: XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX
```

**2. Configure Blackbox EYE:**

Create `/includes/siem-logger.php`:

```php
<?php
/**
 * SIEM Integration - Splunk HEC
 */

class SIEMLogger {
    private $hecUrl;
    private $hecToken;
    private $sourcetype;
    
    public function __construct() {
        $this->hecUrl = getenv('SPLUNK_HEC_URL') ?: 'https://splunk.example.com:8088/services/collector';
        $this->hecToken = getenv('SPLUNK_HEC_TOKEN');
        $this->sourcetype = 'blackbox_eye:json';
    }
    
    public function logEvent($eventType, $data) {
        if (!$this->hecToken) {
            error_log("SIEM: HEC token not configured");
            return false;
        }
        
        $event = [
            'time' => time(),
            'host' => gethostname(),
            'source' => 'blackbox-eye',
            'sourcetype' => $this->sourcetype,
            'event' => array_merge([
                'timestamp' => date('c'),
                'event_type' => $eventType,
                'environment' => getenv('ENV') ?: 'production',
                'application' => 'blackbox-eye'
            ], $data)
        ];
        
        return $this->sendToSplunk($event);
    }
    
    private function sendToSplunk($event) {
        $ch = curl_init($this->hecUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($event));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Splunk ' . $this->hecToken,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            error_log("SIEM: Failed to send event to Splunk (HTTP $httpCode)");
            return false;
        }
        
        return true;
    }
}

// Global logger instance
$siemLogger = new SIEMLogger();

// Helper function
function log_to_siem($eventType, $data) {
    global $siemLogger;
    return $siemLogger->logEvent($eventType, $data);
}
```

**3. Usage Example:**

```php
<?php
require_once __DIR__ . '/includes/siem-logger.php';

// Log authentication event
log_to_siem('authentication', [
    'action' => 'login_attempt',
    'result' => 'success',
    'user' => ['username' => $username, 'role' => $role],
    'source' => ['ip' => $_SERVER['REMOTE_ADDR']]
]);

// Log security event
log_to_siem('security', [
    'severity' => 'high',
    'category' => 'brute_force_detected',
    'details' => ['failed_attempts' => $failedCount, 'source_ip' => $ip]
]);
```

---

### Method 2: Elastic Stack (Filebeat)

#### Configuration

**1. Install Filebeat:**

```bash
curl -L -O https://artifacts.elastic.co/downloads/beats/filebeat/filebeat-8.11.0-amd64.deb
sudo dpkg -i filebeat-8.11.0-amd64.deb
```

**2. Configure Filebeat (`/etc/filebeat/filebeat.yml`):**

```yaml
filebeat.inputs:
  - type: log
    enabled: true
    paths:
      - /var/www/html/logs/siem/*.json
    json.keys_under_root: true
    json.add_error_key: true
    fields:
      application: blackbox-eye
      environment: production
    fields_under_root: true

output.elasticsearch:
  hosts: ["https://elasticsearch.example.com:9200"]
  username: "elastic"
  password: "${ELASTICSEARCH_PASSWORD}"
  ssl.certificate_authorities: ["/etc/filebeat/ca.crt"]

# Alternative: Output to Logstash
# output.logstash:
#   hosts: ["logstash.example.com:5044"]
#   ssl.certificate_authorities: ["/etc/filebeat/ca.crt"]

processors:
  - add_host_metadata:
      when.not.contains.tags: forwarded
  - add_cloud_metadata: ~
  - add_docker_metadata: ~
  - add_kubernetes_metadata: ~

logging.level: info
logging.to_files: true
logging.files:
  path: /var/log/filebeat
  name: filebeat
  keepfiles: 7
```

**3. Create Log Directory:**

```bash
mkdir -p /var/www/html/logs/siem
chmod 755 /var/www/html/logs/siem
```

**4. Update PHP Logging:**

```php
<?php
// Log to SIEM directory (JSON format for Filebeat)
function log_siem_event($eventType, $data) {
    $logDir = __DIR__ . '/../logs/siem';
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    $event = array_merge([
        '@timestamp' => date('c'),
        'event_type' => $eventType,
        'application' => 'blackbox-eye',
        'host' => gethostname()
    ], $data);
    
    $logFile = $logDir . '/events-' . date('Y-m-d') . '.json';
    file_put_contents($logFile, json_encode($event) . PHP_EOL, FILE_APPEND | LOCK_EX);
}
```

**5. Start Filebeat:**

```bash
sudo systemctl enable filebeat
sudo systemctl start filebeat
```

---

### Method 3: Azure Sentinel

#### Configuration

**1. Create Log Analytics Workspace:**

```bash
# Azure CLI
az monitor log-analytics workspace create \
  --resource-group blackbox-rg \
  --workspace-name blackbox-eye-logs \
  --location westeurope
```

**2. Get Workspace ID and Key:**

```bash
WORKSPACE_ID=$(az monitor log-analytics workspace show \
  --resource-group blackbox-rg \
  --workspace-name blackbox-eye-logs \
  --query customerId -o tsv)

WORKSPACE_KEY=$(az monitor log-analytics workspace get-shared-keys \
  --resource-group blackbox-rg \
  --workspace-name blackbox-eye-logs \
  --query primarySharedKey -o tsv)
```

**3. Send Logs via HTTP Data Collector API:**

```php
<?php
class AzureSentinelLogger {
    private $workspaceId;
    private $sharedKey;
    private $logType = 'BlackboxEye';
    
    public function __construct() {
        $this->workspaceId = getenv('AZURE_WORKSPACE_ID');
        $this->sharedKey = getenv('AZURE_WORKSPACE_KEY');
    }
    
    public function logEvent($data) {
        $url = "https://{$this->workspaceId}.ods.opinsights.azure.com/api/logs?api-version=2016-04-01";
        
        $rfc1123date = gmdate('D, d M Y H:i:s T');
        $jsonData = json_encode($data);
        $contentLength = strlen($jsonData);
        
        $signature = $this->buildSignature($rfc1123date, $contentLength);
        
        $headers = [
            "Authorization: SharedKey {$this->workspaceId}:$signature",
            "Log-Type: {$this->logType}",
            "x-ms-date: $rfc1123date",
            "Content-Type: application/json",
            "time-generated-field: timestamp"
        ];
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return $httpCode === 200;
    }
    
    private function buildSignature($date, $contentLength) {
        $stringToSign = "POST\n{$contentLength}\napplication/json\nx-ms-date:{$date}\n/api/logs";
        $decodedKey = base64_decode($this->sharedKey);
        $hash = hash_hmac('sha256', $stringToSign, $decodedKey, true);
        return base64_encode($hash);
    }
}
```

---

## Secure Log Transmission

### Encryption Requirements

All log transmissions MUST use:
- **TLS 1.3** or TLS 1.2 minimum
- **Mutual TLS (mTLS)** for high-security environments
- **Certificate pinning** recommended

### Network Security

```nginx
# Nginx configuration for log forwarding proxy
upstream siem_backend {
    server siem.example.com:6514;
    keepalive 32;
}

server {
    listen 443 ssl http2;
    server_name logs.blackbox.codes;
    
    # TLS Configuration
    ssl_certificate /etc/nginx/ssl/logs.blackbox.codes.crt;
    ssl_certificate_key /etc/nginx/ssl/logs.blackbox.codes.key;
    ssl_protocols TLSv1.3;
    ssl_ciphers 'TLS_AES_256_GCM_SHA384:TLS_CHACHA20_POLY1305_SHA256';
    ssl_prefer_server_ciphers on;
    
    # Client certificate authentication (mTLS)
    ssl_client_certificate /etc/nginx/ssl/ca.crt;
    ssl_verify_client on;
    
    location /logs {
        proxy_pass https://siem_backend;
        proxy_ssl_verify on;
        proxy_ssl_trusted_certificate /etc/nginx/ssl/siem-ca.crt;
    }
}
```

### Data Protection

**Sensitive Data Masking:**

```php
<?php
function mask_sensitive_data($data) {
    $maskFields = ['password', 'credit_card', 'ssn', 'api_key'];
    
    array_walk_recursive($data, function(&$value, $key) use ($maskFields) {
        if (in_array(strtolower($key), $maskFields)) {
            $value = '[REDACTED]';
        }
    });
    
    return $data;
}

// Before logging
$sanitizedData = mask_sensitive_data($rawData);
log_to_siem('data_access', $sanitizedData);
```

---

## Operational Bridge Setup

### Real-Time Log Streaming

**WebSocket Bridge for Real-Time Monitoring:**

```javascript
// /assets/js/operational-bridge.js
class OperationalBridge {
    constructor(wsUrl, apiToken) {
        this.wsUrl = wsUrl;
        this.apiToken = apiToken;
        this.ws = null;
        this.reconnectInterval = 5000;
    }
    
    connect() {
        this.ws = new WebSocket(this.wsUrl);
        
        this.ws.onopen = () => {
            console.log('Operational Bridge connected');
            this.authenticate();
        };
        
        this.ws.onmessage = (event) => {
            const data = JSON.parse(event.data);
            this.handleEvent(data);
        };
        
        this.ws.onerror = (error) => {
            console.error('WebSocket error:', error);
        };
        
        this.ws.onclose = () => {
            console.log('Connection closed. Reconnecting...');
            setTimeout(() => this.connect(), this.reconnectInterval);
        };
    }
    
    authenticate() {
        this.ws.send(JSON.stringify({
            type: 'auth',
            token: this.apiToken
        }));
    }
    
    handleEvent(event) {
        // Forward to SIEM
        this.forwardToSIEM(event);
        
        // Update dashboard
        this.updateDashboard(event);
        
        // Trigger alerts if needed
        if (event.severity === 'critical' || event.severity === 'high') {
            this.triggerAlert(event);
        }
    }
    
    forwardToSIEM(event) {
        fetch('/api/siem/forward', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${this.apiToken}`
            },
            body: JSON.stringify(event)
        });
    }
    
    updateDashboard(event) {
        const dashboard = document.getElementById('security-dashboard');
        if (dashboard) {
            const eventElement = this.createEventElement(event);
            dashboard.prepend(eventElement);
        }
    }
    
    triggerAlert(event) {
        // Desktop notification
        if (Notification.permission === 'granted') {
            new Notification('Security Alert', {
                body: event.description,
                icon: '/assets/img/alert-icon.png',
                badge: '/assets/img/badge.png'
            });
        }
        
        // Visual alert
        this.showAlertBanner(event);
    }
    
    createEventElement(event) {
        const div = document.createElement('div');
        div.className = `event-item severity-${event.severity}`;
        div.innerHTML = `
            <span class="timestamp">${event.timestamp}</span>
            <span class="event-type">${event.event_type}</span>
            <span class="description">${event.description}</span>
        `;
        return div;
    }
    
    showAlertBanner(event) {
        const banner = document.createElement('div');
        banner.className = 'alert-banner critical';
        banner.textContent = `🚨 ${event.description}`;
        document.body.prepend(banner);
        
        setTimeout(() => banner.remove(), 10000);
    }
}

// Initialize
const bridge = new OperationalBridge('wss://logs.blackbox.codes/stream', 'YOUR_API_TOKEN');
bridge.connect();
```

---

## Correlation Rules

### Splunk Correlation Searches

**1. Brute Force Detection:**

```spl
index=blackbox_eye event_type=authentication action=login_attempt result=failure
| stats count by source.ip
| where count > 10
| eval severity="high"
| eval description="Potential brute force attack detected from " . source.ip
```

**2. Privilege Escalation:**

```spl
index=blackbox_eye event_type=audit action=role_changed
| where old_role!="administrator" AND new_role="administrator"
| eval severity="critical"
| eval description="User " . target.username . " elevated to administrator by " . actor.username
```

**3. Data Exfiltration:**

```spl
index=blackbox_eye event_type=data action=data_export_requested
| stats sum(data_size_mb) as total_mb by user.username
| where total_mb > 1000
| eval severity="high"
| eval description="Potential data exfiltration: " . user.username . " exported " . total_mb . " MB"
```

### ELK Watcher Rules

```json
{
  "trigger": {
    "schedule": {
      "interval": "5m"
    }
  },
  "input": {
    "search": {
      "request": {
        "indices": ["blackbox-eye-*"],
        "body": {
          "query": {
            "bool": {
              "must": [
                {"match": {"event_type": "authentication"}},
                {"match": {"result": "failure"}}
              ],
              "filter": {
                "range": {
                  "@timestamp": {
                    "gte": "now-5m"
                  }
                }
              }
            }
          },
          "aggs": {
            "failed_by_ip": {
              "terms": {
                "field": "source.ip",
                "min_doc_count": 10
              }
            }
          }
        }
      }
    }
  },
  "condition": {
    "compare": {
      "ctx.payload.aggregations.failed_by_ip.buckets.0.doc_count": {
        "gte": 10
      }
    }
  },
  "actions": {
    "send_email": {
      "email": {
        "to": "security@blackbox.codes",
        "subject": "Brute Force Alert - Blackbox EYE",
        "body": "Multiple failed login attempts detected from {{ctx.payload.aggregations.failed_by_ip.buckets.0.key}}"
      }
    }
  }
}
```

---

## Alerting and Response

### Alert Severity Matrix

| Severity | Response Time | Escalation | Examples |
|----------|--------------|------------|----------|
| **Critical** | Immediate (< 5 min) | SOC Manager + On-call | Data breach, system compromise |
| **High** | < 30 min | SOC Analyst | Brute force, privilege escalation |
| **Medium** | < 2 hours | SOC Analyst | Rate limit exceeded, suspicious activity |
| **Low** | < 24 hours | Daily review | Failed login, minor errors |

### Automated Response Actions

```yaml
# Example: Auto-block IP after failed logins
# Triggered by SIEM correlation rule

action: block_ip
trigger: failed_logins_threshold_exceeded
parameters:
  source_ip: "{{ event.source.ip }}"
  duration: 1800  # 30 minutes
  method: cloudflare_firewall
  
execution:
  - call_api:
      url: "https://api.cloudflare.com/client/v4/zones/{{ zone_id }}/firewall/access_rules/rules"
      method: POST
      headers:
        Authorization: "Bearer {{ cf_token }}"
      body:
        mode: "block"
        configuration:
          target: "ip"
          value: "{{ source_ip }}"
        notes: "Auto-blocked by SIEM - Brute force attempt"
  
  - send_notification:
      to: "security@blackbox.codes"
      subject: "Auto-blocked IP: {{ source_ip }}"
      body: "IP {{ source_ip }} blocked for {{ duration }} seconds due to {{ failed_count }} failed login attempts."
```

---

## Troubleshooting

### Common Issues

**1. Logs Not Appearing in SIEM**

```bash
# Check if logs are being generated
tail -f /var/www/html/logs/siem/events-$(date +%Y-%m-%d).json

# Check Filebeat status
sudo systemctl status filebeat
sudo journalctl -u filebeat -f

# Test connectivity to Elasticsearch
curl -X GET "https://elasticsearch.example.com:9200/_cat/health?v"
```

**2. High Log Volume**

```php
<?php
// Implement log sampling
function should_log_event($eventType, $samplingRate = 0.1) {
    // Always log critical events
    $alwaysLog = ['security', 'audit', 'authentication'];
    if (in_array($eventType, $alwaysLog)) {
        return true;
    }
    
    // Sample other events
    return (mt_rand() / mt_getrandmax()) < $samplingRate;
}

if (should_log_event($eventType, 0.1)) {
    log_to_siem($eventType, $data);
}
```

**3. Certificate Errors**

```bash
# Verify certificate chain
openssl s_client -connect elasticsearch.example.com:9200 -showcerts

# Update CA certificates
sudo update-ca-certificates

# Test with curl
curl -v --cacert /etc/ssl/certs/ca-certificates.crt https://elasticsearch.example.com:9200
```

---

## Performance Considerations

### Log Buffering

```php
<?php
class BufferedSIEMLogger {
    private $buffer = [];
    private $bufferSize = 100;
    private $flushInterval = 60; // seconds
    private $lastFlush = 0;
    
    public function log($eventType, $data) {
        $this->buffer[] = ['type' => $eventType, 'data' => $data];
        
        if (count($this->buffer) >= $this->bufferSize || 
            (time() - $this->lastFlush) > $this->flushInterval) {
            $this->flush();
        }
    }
    
    private function flush() {
        if (empty($this->buffer)) return;
        
        // Batch send to SIEM
        $this->sendBatch($this->buffer);
        
        $this->buffer = [];
        $this->lastFlush = time();
    }
    
    private function sendBatch($events) {
        // Implementation depends on SIEM platform
        // Splunk HEC supports batch events
    }
    
    public function __destruct() {
        $this->flush();
    }
}
```

---

## Compliance and Audit

### Audit Trail

All SIEM integrations MUST maintain:
- **Configuration changes**: Who changed what and when
- **Access logs**: Who accessed SIEM data
- **Export logs**: Data exported from SIEM
- **Retention policies**: How long logs are kept

### GDPR Compliance

- Personal data in logs MUST be minimized
- Users can request deletion of their logs (Right to Erasure)
- Logs containing PII encrypted at rest
- Access to logs restricted to authorized personnel

---

## Support and Maintenance

### Monitoring Dashboard

Access the SIEM health dashboard:
- **URL**: https://blackbox.codes/admin/siem-health
- **Metrics**: Log volume, latency, error rate
- **Alerts**: Integration failures, certificate expiration

### Contact

- **SIEM Integration Support**: siem-support@blackbox.codes
- **Emergency Hotline**: +45 XX XX XX XX (24/7)
- **Documentation**: https://docs.blackbox.codes/siem

---

**Document Version**: 1.0  
**Last Updated**: 2025-11-23  
**Next Review**: 2026-02-23  
**Owner**: ALPHA-CI-Security-Agent
