-- Blog Posts Table for Blackbox EYE CMS
-- Sprint 4: Blog CMS System
-- Created: November 23, 2025

CREATE TABLE IF NOT EXISTS blog_posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(255) UNIQUE NOT NULL,
    
    -- Multi-language content
    title_da VARCHAR(255) NOT NULL,
    title_en VARCHAR(255) NOT NULL,
    content_da TEXT NOT NULL,
    content_en TEXT NOT NULL,
    excerpt_da TEXT,
    excerpt_en TEXT,
    
    -- Media
    featured_image VARCHAR(255),
    
    -- Organization
    category VARCHAR(100),
    tags JSON,
    author VARCHAR(100) DEFAULT 'Blackbox EYE',
    
    -- Publishing
    status ENUM('draft', 'published', 'archived') DEFAULT 'draft',
    publish_date DATETIME,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- SEO
    meta_description_da TEXT,
    meta_description_en TEXT,
    
    -- Analytics
    views INT DEFAULT 0,
    
    -- Indexes for performance
    INDEX idx_status_publish (status, publish_date DESC),
    INDEX idx_category (category),
    INDEX idx_slug (slug),
    FULLTEXT idx_search (title_da, title_en, content_da, content_en)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sample blog post (Danish cybersecurity topic)
INSERT INTO blog_posts (
    slug, 
    title_da, 
    title_en, 
    content_da, 
    content_en, 
    excerpt_da, 
    excerpt_en,
    category, 
    tags, 
    status, 
    publish_date,
    meta_description_da,
    meta_description_en
) VALUES (
    'ai-drevet-trusselsdetektion-fremtiden',
    'AI-drevet Trusselsdetektion: Fremtiden for Cybersikkerhed',
    'AI-Powered Threat Detection: The Future of Cybersecurity',
    '<p>Kunstig intelligens revolutionerer cybersikkerhed ved at identificere trusler hurtigere og mere præcist end traditionelle metoder. Blackbox EYE anvender avancerede AI-modeller til proaktiv trusselsdetektion.</p>

<h2>Hvordan AI Forbedrer Sikkerhed</h2>
<p>Traditionelle sikkerhedssystemer reagerer på kendte angrebsmønstre, men AI kan opdage anomalier og nye trusler i realtid. Vores platform analyserer millioner af datapunkter for at identificere mistænkelig aktivitet.</p>

<h3>Machine Learning i Praksis</h3>
<ul>
<li><strong>Behavioral Analysis:</strong> AI lærer normale brugermønstre og detecterer afvigelser</li>
<li><strong>Predictive Modeling:</strong> Forudsiger potentielle angreb baseret på historiske data</li>
<li><strong>Automated Response:</strong> Reagerer øjeblikkeligt på trusler uden menneskelig intervention</li>
</ul>

<p>Med Blackbox EYE får din virksomhed adgang til enterprise-grade AI-sikkerhed, der beskytte mod nuværende og fremtidige trusler.</p>',
    
    '<p>Artificial intelligence is revolutionizing cybersecurity by identifying threats faster and more accurately than traditional methods. Blackbox EYE utilizes advanced AI models for proactive threat detection.</p>

<h2>How AI Improves Security</h2>
<p>Traditional security systems react to known attack patterns, but AI can detect anomalies and new threats in real-time. Our platform analyzes millions of data points to identify suspicious activity.</p>

<h3>Machine Learning in Practice</h3>
<ul>
<li><strong>Behavioral Analysis:</strong> AI learns normal user patterns and detects deviations</li>
<li><strong>Predictive Modeling:</strong> Predicts potential attacks based on historical data</li>
<li><strong>Automated Response:</strong> Responds instantly to threats without human intervention</li>
</ul>

<p>With Blackbox EYE, your business gains access to enterprise-grade AI security that protects against current and future threats.</p>',
    
    'Kunstig intelligens revolutionerer cybersikkerhed ved at identificere trusler hurtigere og mere præcist end traditionelle metoder.',
    'Artificial intelligence is revolutionizing cybersecurity by identifying threats faster and more accurately than traditional methods.',
    'Cybersecurity',
    '["AI", "Machine Learning", "Threat Detection", "Automation"]',
    'published',
    NOW(),
    'Lær hvordan AI-drevet trusselsdetektion transformerer cybersikkerhed med Blackbox EYE. Avanceret machine learning og automatiseret respons.',
    'Learn how AI-powered threat detection transforms cybersecurity with Blackbox EYE. Advanced machine learning and automated response.'
);
