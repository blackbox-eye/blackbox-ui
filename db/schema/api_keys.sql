-- API Keys Database Schema
-- Secure API key management for Blackbox UI
-- Part of Blackbox UI

CREATE TABLE IF NOT EXISTS api_keys (
    id              INT AUTO_INCREMENT PRIMARY KEY,

    -- Key identification
    key_id          CHAR(16) NOT NULL UNIQUE COMMENT 'Public key identifier (prefix)',
    key_hash        CHAR(64) NOT NULL COMMENT 'SHA-256 hash of the full API key',
    key_hint        CHAR(8) NOT NULL COMMENT 'Last 8 chars for identification',

    -- Metadata
    name            VARCHAR(100) NOT NULL COMMENT 'User-friendly name for the key',
    description     TEXT DEFAULT NULL COMMENT 'Optional description of key purpose',

    -- Ownership
    agent_id        INT NOT NULL COMMENT 'Owner of the API key',

    -- Permissions & Scopes
    scopes          JSON DEFAULT NULL COMMENT 'Array of allowed scopes/permissions',

    -- Rate limiting
    rate_limit      INT UNSIGNED DEFAULT 1000 COMMENT 'Requests per hour, NULL for unlimited',

    -- Restrictions
    allowed_ips     JSON DEFAULT NULL COMMENT 'IP whitelist, NULL for any',
    allowed_origins JSON DEFAULT NULL COMMENT 'CORS origins whitelist',

    -- Usage tracking
    last_used_at    TIMESTAMP NULL DEFAULT NULL,
    last_used_ip    VARCHAR(45) DEFAULT NULL,
    request_count   BIGINT UNSIGNED NOT NULL DEFAULT 0,

    -- Status
    is_active       BOOLEAN NOT NULL DEFAULT TRUE,
    expires_at      TIMESTAMP NULL DEFAULT NULL COMMENT 'NULL for no expiry',

    -- Timestamps
    created_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    revoked_at      TIMESTAMP NULL DEFAULT NULL,
    revoked_by      INT DEFAULT NULL,

    -- Indexes
    INDEX idx_key_id (key_id),
    INDEX idx_key_hash (key_hash),
    INDEX idx_agent (agent_id),
    INDEX idx_active (is_active),
    INDEX idx_expires (expires_at),

    -- Foreign keys
    CONSTRAINT fk_apikey_agent
        FOREIGN KEY (agent_id) REFERENCES agents(id) ON DELETE CASCADE,
    CONSTRAINT fk_apikey_revoked_by
        FOREIGN KEY (revoked_by) REFERENCES agents(id) ON DELETE SET NULL

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='API keys for external integrations';


-- API Key usage log for auditing
CREATE TABLE IF NOT EXISTS api_key_usage_log (
    id              BIGINT AUTO_INCREMENT PRIMARY KEY,
    key_id          INT NOT NULL,

    -- Request details
    endpoint        VARCHAR(255) NOT NULL,
    method          VARCHAR(10) NOT NULL,
    status_code     SMALLINT UNSIGNED NOT NULL,
    response_time   INT UNSIGNED COMMENT 'Response time in milliseconds',

    -- Client info
    ip_address      VARCHAR(45) NOT NULL,
    user_agent      VARCHAR(500) DEFAULT NULL,

    -- Timestamp
    created_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    -- Indexes
    INDEX idx_key (key_id),
    INDEX idx_created (created_at),
    INDEX idx_endpoint (endpoint),

    -- Foreign key
    CONSTRAINT fk_usage_key
        FOREIGN KEY (key_id) REFERENCES api_keys(id) ON DELETE CASCADE

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='API key usage audit log';


-- Available scopes/permissions
CREATE TABLE IF NOT EXISTS api_scopes (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    scope           VARCHAR(50) NOT NULL UNIQUE,
    name            VARCHAR(100) NOT NULL,
    description     TEXT DEFAULT NULL,
    is_admin_only   BOOLEAN NOT NULL DEFAULT FALSE,
    created_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_scope (scope)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Available API permission scopes';


-- Insert default scopes
INSERT INTO api_scopes (scope, name, description, is_admin_only) VALUES
('read:vault', 'Læs Vault', 'Læs dokumenter fra Intel Vault', FALSE),
('write:vault', 'Skriv Vault', 'Upload dokumenter til Intel Vault', FALSE),
('delete:vault', 'Slet Vault', 'Slet dokumenter fra Intel Vault', FALSE),
('read:users', 'Læs Brugere', 'Læs brugeroplysninger', TRUE),
('write:users', 'Administrer Brugere', 'Opret og rediger brugere', TRUE),
('read:logs', 'Læs Logs', 'Læs systemlogs', TRUE),
('read:analytics', 'Læs Analytics', 'Læs analytiske data', FALSE),
('webhook:receive', 'Modtag Webhooks', 'Modtag webhook-notifikationer', FALSE)
ON DUPLICATE KEY UPDATE name = VALUES(name);
