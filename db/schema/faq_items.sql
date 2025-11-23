-- FAQ Items Table for Blackbox EYE
-- Sprint 4: FAQ Section + AI Search
-- Created: November 23, 2025

CREATE TABLE IF NOT EXISTS faq_items (
    id INT AUTO_INCREMENT PRIMARY KEY,

    -- Category (Security, Pricing, Technical, Integration, Support, etc.)
    category VARCHAR(100) NOT NULL,

    -- Multi-language questions and answers
    question_da TEXT NOT NULL,
    question_en TEXT NOT NULL,
    answer_da TEXT NOT NULL,
    answer_en TEXT NOT NULL,

    -- Keywords for AI search matching
    keywords JSON,

    -- Display order within category
    order_index INT DEFAULT 0,

    -- Helpfulness tracking
    helpful_count INT DEFAULT 0,
    not_helpful_count INT DEFAULT 0,

    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    -- Indexes for performance
    INDEX idx_category (category),
    INDEX idx_order (category, order_index),
    FULLTEXT idx_search (question_da, question_en, answer_da, answer_en)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sample FAQ items
INSERT INTO faq_items (category, question_da, question_en, answer_da, answer_en, keywords, order_index) VALUES

-- Security Category
('Security',
 'Hvordan beskytter Blackbox EYE min virksomhed mod cybertrusler?',
 'How does Blackbox EYE protect my business from cyber threats?',
 'Blackbox EYE anvender avanceret AI-drevet trusselsdetektion kombineret med kontinuerlig overvågning og proaktiv sikkerhedsanalyse. Vores platform identificerer anomalier i realtid og reagerer automatisk på potentielle trusler før de bliver til faktiske angreb.',
 'Blackbox EYE uses advanced AI-powered threat detection combined with continuous monitoring and proactive security analysis. Our platform identifies anomalies in real-time and automatically responds to potential threats before they become actual attacks.',
 '["cyber threats", "AI detection", "real-time monitoring", "automated response"]',
 1),

('Security',
 'Hvad er forskellen mellem penetration testing og kontinuerlig overvågning?',
 'What is the difference between penetration testing and continuous monitoring?',
 'Penetration testing er periodiske sikkerhedstests hvor etiske hackere forsøger at finde sårbarheder i dine systemer. Kontinuerlig overvågning derimod kører 24/7 og detekterer mistænkelig aktivitet i realtid. Blackbox EYE kombinerer begge for maksimal sikkerhed.',
 'Penetration testing is periodic security testing where ethical hackers attempt to find vulnerabilities in your systems. Continuous monitoring, on the other hand, runs 24/7 and detects suspicious activity in real-time. Blackbox EYE combines both for maximum security.',
 '["penetration testing", "pen test", "continuous monitoring", "24/7", "vulnerability scanning"]',
 2),

('Security',
 'Kan Blackbox EYE integreres med vores eksisterende sikkerhedsinfrastruktur?',
 'Can Blackbox EYE integrate with our existing security infrastructure?',
 'Ja, Blackbox EYE er designet til at integrere med de fleste sikkerhedsværktøjer inklusive SIEM-systemer, firewalls, IDS/IPS, og endpoint-beskyttelse. Vi understøtter standard API\'er og kan tilpasse integration til jeres specifikke behov.',
 'Yes, Blackbox EYE is designed to integrate with most security tools including SIEM systems, firewalls, IDS/IPS, and endpoint protection. We support standard APIs and can customize integration to your specific needs.',
 '["integration", "API", "SIEM", "firewall", "existing infrastructure"]',
 3),

-- Pricing Category
('Pricing',
 'Hvad koster Blackbox EYE MVP-pakken?',
 'How much does the Blackbox EYE MVP package cost?',
 'Vores MVP-pakke starter fra 2.999 DKK/måned og inkluderer kernefunktioner som penetration testing, trusselsdetektion og basissupport. For enterprise-løsninger med fuld funktionalitet og dedikeret support kontakt os for et skræddersyet tilbud.',
 'Our MVP package starts from DKK 2,999/month and includes core features like penetration testing, threat detection, and basic support. For enterprise solutions with full functionality and dedicated support, contact us for a customized quote.',
 '["pricing", "cost", "MVP", "subscription", "monthly fee"]',
 1),

('Pricing',
 'Er der nogen skjulte omkostninger?',
 'Are there any hidden costs?',
 'Nej, vi har fuld transparens i vores prissætning. Alle features i din valgte pakke er inkluderet uden ekstraomkostninger. Eventuelle tilkøb som dedikeret penetration testing eller custom development prissættes separat og kommunikeres tydeligt på forhånd.',
 'No, we have full transparency in our pricing. All features in your chosen package are included without additional costs. Any add-ons like dedicated penetration testing or custom development are priced separately and communicated clearly in advance.',
 '["hidden costs", "pricing transparency", "additional fees", "extras"]',
 2),

-- Technical Category
('Technical',
 'Hvilke programmeringssprog og frameworks understøtter I?',
 'What programming languages and frameworks do you support?',
 'Blackbox EYE understøtter sikkerhedsanalyse af applikationer bygget i Python, JavaScript/Node.js, PHP, Java, C#/.NET, Ruby, og Go. Vi kan også analysere moderne frameworks som React, Vue, Angular, Django, og Laravel.',
 'Blackbox EYE supports security analysis of applications built in Python, JavaScript/Node.js, PHP, Java, C#/.NET, Ruby, and Go. We can also analyze modern frameworks like React, Vue, Angular, Django, and Laravel.',
 '["programming languages", "frameworks", "Python", "JavaScript", "PHP", "technical stack"]',
 1),

('Technical',
 'Hvordan håndterer I data-sikkerhed og GDPR?',
 'How do you handle data security and GDPR?',
 'Vi er fuldt GDPR-compliant og lagrer alle data krypteret i EU-baserede datacentre. Alle medarbejdere er underlagt strenge fortrolighedsaftaler, og vi gennemfører regelmæssige sikkerhedsaudits. Dine data deles aldrig med tredjeparter.',
 'We are fully GDPR-compliant and store all data encrypted in EU-based data centers. All employees are subject to strict confidentiality agreements, and we conduct regular security audits. Your data is never shared with third parties.',
 '["data security", "GDPR", "privacy", "encryption", "compliance", "data protection"]',
 2),

-- Integration Category
('Integration',
 'Hvor lang tid tager det at implementere Blackbox EYE?',
 'How long does it take to implement Blackbox EYE?',
 'Standard implementation tager typisk 2-4 uger afhængig af kompleksiteten af jeres infrastruktur. Vi tilbyder dedikeret onboarding support og kan accelerere processen ved behov. MVP-pakken kan være operationel indenfor få dage.',
 'Standard implementation typically takes 2-4 weeks depending on the complexity of your infrastructure. We offer dedicated onboarding support and can accelerate the process if needed. The MVP package can be operational within a few days.',
 '["implementation", "onboarding", "setup time", "deployment", "integration timeline"]',
 1),

('Integration',
 'Kræver Blackbox EYE installation af software på vores servere?',
 'Does Blackbox EYE require software installation on our servers?',
 'Det afhænger af din valgte løsning. Cloud-baseret overvågning kan køre uden lokal installation. For on-premise deployment installerer vi lette agenter der køre med minimal ressourceforbrug. Vi tilbyder også hybrid-løsninger.',
 'It depends on your chosen solution. Cloud-based monitoring can run without local installation. For on-premise deployment, we install lightweight agents that run with minimal resource consumption. We also offer hybrid solutions.',
 '["installation", "on-premise", "cloud", "agents", "deployment options"]',
 2),

-- Support Category
('Support',
 'Hvilken support får jeg med min pakke?',
 'What support do I get with my package?',
 'MVP-pakken inkluderer email support med respons indenfor 24 timer. Premium og Enterprise-pakker har prioriteret support med 4-timers responstid, telefon support, og dedikeret account manager. Alle pakker har adgang til vores videnbase.',
 'The MVP package includes email support with response within 24 hours. Premium and Enterprise packages have prioritized support with 4-hour response time, phone support, and a dedicated account manager. All packages have access to our knowledge base.',
 '["support", "customer service", "response time", "help desk", "account manager"]',
 1),

('Support',
 'Tilbyder I træning til vores team?',
 'Do you offer training for our team?',
 'Ja, vi tilbyder både online og on-site træning i brug af platformen. Enterprise-pakker inkluderer skræddersyet træning, workshops, og løbende uddannelse. Vi har også omfattende dokumentation og video-tutorials tilgængelige.',
 'Yes, we offer both online and on-site training in using the platform. Enterprise packages include customized training, workshops, and ongoing education. We also have comprehensive documentation and video tutorials available.',
 '["training", "education", "workshops", "onboarding", "tutorials", "learning"]',
 2);
