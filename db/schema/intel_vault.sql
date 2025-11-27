-- Intel Vault Database Schema
-- Secure encrypted document storage
-- Part of ALPHA Interface GUI

CREATE TABLE IF NOT EXISTS intel_vault_documents (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    uuid            CHAR(36) NOT NULL UNIQUE COMMENT 'Public document identifier',

    -- Document metadata
    original_name   VARCHAR(255) NOT NULL COMMENT 'Original filename',
    stored_name     VARCHAR(255) NOT NULL COMMENT 'Encrypted filename on disk',
    mime_type       VARCHAR(100) NOT NULL,
    file_size       BIGINT UNSIGNED NOT NULL COMMENT 'Original file size in bytes',
    file_hash       CHAR(64) NOT NULL COMMENT 'SHA-256 hash of original content',

    -- Classification
    classification  ENUM('unclassified', 'internal', 'confidential', 'secret', 'top_secret')
                    NOT NULL DEFAULT 'internal' COMMENT 'Security classification level',

    -- Encryption
    encryption_key_id VARCHAR(64) NOT NULL COMMENT 'Reference to encryption key',
    encryption_iv     CHAR(32) NOT NULL COMMENT 'Initialization vector (hex)',

    -- Organization
    folder_id       INT DEFAULT NULL COMMENT 'Parent folder, NULL for root',
    tags            JSON DEFAULT NULL COMMENT 'Array of tags for categorization',
    description     TEXT DEFAULT NULL COMMENT 'Document description/notes',

    -- Ownership and access
    uploaded_by     INT NOT NULL COMMENT 'Agent ID who uploaded',
    access_level    ENUM('owner', 'team', 'department', 'all') NOT NULL DEFAULT 'owner',

    -- Timestamps
    created_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    accessed_at     TIMESTAMP NULL DEFAULT NULL COMMENT 'Last access timestamp',
    expires_at      TIMESTAMP NULL DEFAULT NULL COMMENT 'Auto-delete after this date',

    -- Soft delete
    deleted_at      TIMESTAMP NULL DEFAULT NULL,
    deleted_by      INT DEFAULT NULL,

    -- Indexes
    INDEX idx_uuid (uuid),
    INDEX idx_uploaded_by (uploaded_by),
    INDEX idx_classification (classification),
    INDEX idx_folder (folder_id),
    INDEX idx_created (created_at),
    INDEX idx_deleted (deleted_at),

    -- Foreign keys (assuming agents table exists)
    CONSTRAINT fk_vault_uploaded_by
        FOREIGN KEY (uploaded_by) REFERENCES agents(id) ON DELETE RESTRICT,
    CONSTRAINT fk_vault_deleted_by
        FOREIGN KEY (deleted_by) REFERENCES agents(id) ON DELETE SET NULL,
    CONSTRAINT fk_vault_folder
        FOREIGN KEY (folder_id) REFERENCES intel_vault_folders(id) ON DELETE SET NULL

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Encrypted document storage for Intel Vault';


-- Folders for organizing documents
CREATE TABLE IF NOT EXISTS intel_vault_folders (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    uuid            CHAR(36) NOT NULL UNIQUE,
    name            VARCHAR(100) NOT NULL,
    parent_id       INT DEFAULT NULL,
    created_by      INT NOT NULL,
    created_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_parent (parent_id),
    INDEX idx_created_by (created_by),

    CONSTRAINT fk_folder_parent
        FOREIGN KEY (parent_id) REFERENCES intel_vault_folders(id) ON DELETE CASCADE,
    CONSTRAINT fk_folder_created_by
        FOREIGN KEY (created_by) REFERENCES agents(id) ON DELETE RESTRICT

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Folder structure for Intel Vault documents';


-- Audit log for document access
CREATE TABLE IF NOT EXISTS intel_vault_audit_log (
    id              BIGINT AUTO_INCREMENT PRIMARY KEY,
    document_id     INT NOT NULL,
    agent_id        INT NOT NULL,
    action          ENUM('view', 'download', 'update', 'share', 'delete', 'restore', 'classify') NOT NULL,
    ip_address      VARCHAR(45) DEFAULT NULL,
    user_agent      VARCHAR(500) DEFAULT NULL,
    details         JSON DEFAULT NULL COMMENT 'Additional action details',
    created_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_document (document_id),
    INDEX idx_agent (agent_id),
    INDEX idx_action (action),
    INDEX idx_created (created_at),

    CONSTRAINT fk_audit_document
        FOREIGN KEY (document_id) REFERENCES intel_vault_documents(id) ON DELETE CASCADE,
    CONSTRAINT fk_audit_agent
        FOREIGN KEY (agent_id) REFERENCES agents(id) ON DELETE CASCADE

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Audit trail for all Intel Vault document actions';


-- Shared access links
CREATE TABLE IF NOT EXISTS intel_vault_shares (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    document_id     INT NOT NULL,
    share_token     CHAR(64) NOT NULL UNIQUE COMMENT 'Secure random token',
    created_by      INT NOT NULL,

    -- Restrictions
    expires_at      TIMESTAMP NULL DEFAULT NULL,
    max_downloads   INT UNSIGNED DEFAULT NULL COMMENT 'Max download count, NULL for unlimited',
    download_count  INT UNSIGNED NOT NULL DEFAULT 0,
    password_hash   VARCHAR(255) DEFAULT NULL COMMENT 'Optional password protection',
    allowed_ips     JSON DEFAULT NULL COMMENT 'IP whitelist',

    -- Tracking
    created_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    last_accessed   TIMESTAMP NULL DEFAULT NULL,
    revoked_at      TIMESTAMP NULL DEFAULT NULL,

    INDEX idx_token (share_token),
    INDEX idx_document (document_id),
    INDEX idx_expires (expires_at),

    CONSTRAINT fk_share_document
        FOREIGN KEY (document_id) REFERENCES intel_vault_documents(id) ON DELETE CASCADE,
    CONSTRAINT fk_share_created_by
        FOREIGN KEY (created_by) REFERENCES agents(id) ON DELETE CASCADE

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Time-limited sharing links for Intel Vault documents';
