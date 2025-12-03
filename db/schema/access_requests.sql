-- Access Requests Table for Blackbox EYE Portal
-- Sprint 7: Request Access Workflow with Database Storage
-- Created: November 27, 2025

CREATE TABLE IF NOT EXISTS access_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,

    -- Applicant information
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    organization VARCHAR(255) NOT NULL,
    role ENUM('observer', 'operator', 'analyst', 'admin', 'unspecified') DEFAULT 'unspecified',
    reason TEXT NOT NULL,

    -- Request metadata
    ip_address VARCHAR(45),
    user_agent VARCHAR(500),
    recaptcha_score DECIMAL(3,2),

    -- Status tracking
    status ENUM('pending', 'approved', 'denied', 'expired') DEFAULT 'pending',
    reviewed_by INT NULL,
    reviewed_at TIMESTAMP NULL,
    review_notes TEXT NULL,

    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    -- Indexes for performance
    INDEX idx_email (email),
    INDEX idx_status (status),
    INDEX idx_created (created_at),
    INDEX idx_status_created (status, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Optional: Add foreign key if agents table exists
-- ALTER TABLE access_requests
--     ADD CONSTRAINT fk_reviewed_by
--     FOREIGN KEY (reviewed_by) REFERENCES agents(id)
--     ON DELETE SET NULL;
