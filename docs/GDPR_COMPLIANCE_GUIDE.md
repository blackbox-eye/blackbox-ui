# GDPR Compliance Implementation Guide

## Overview

This guide provides comprehensive implementation steps for achieving GDPR (General Data Protection Regulation) compliance for the ALPHA Interface GUI platform (BLACKBOX.CODES).

---

## Table of Contents

1. [GDPR Requirements Overview](#gdpr-requirements-overview)
2. [Cookie Consent Implementation](#cookie-consent-implementation)
3. [Privacy Policy](#privacy-policy)
4. [Data Retention Policies](#data-retention-policies)
5. [User Rights Implementation](#user-rights-implementation)
6. [Data Processing Records](#data-processing-records)
7. [Security Measures](#security-measures)
8. [Compliance Checklist](#compliance-checklist)

---

## GDPR Requirements Overview

### Key Principles

1. **Lawfulness, Fairness, and Transparency**: Clear communication about data processing
2. **Purpose Limitation**: Data collected only for specified purposes
3. **Data Minimization**: Collect only necessary data
4. **Accuracy**: Keep data up-to-date and correct
5. **Storage Limitation**: Retain data only as long as necessary
6. **Integrity and Confidentiality**: Secure data processing
7. **Accountability**: Demonstrate compliance

### Data We Process

| Data Type | Purpose | Legal Basis | Retention |
|-----------|---------|-------------|-----------|
| Contact Form Submissions | Customer inquiries | Consent | 2 years |
| IP Addresses | Security & fraud prevention | Legitimate interest | 90 days |
| Session Cookies | Authentication | Necessary for service | Session |
| Analytics Cookies | Usage statistics | Consent | 13 months |
| Email Addresses | Communication | Consent | Until withdrawal |
| reCAPTCHA Data | Spam prevention | Legitimate interest | Processed by Google |

---

## Cookie Consent Implementation

### Cookie Banner HTML

Create `/includes/cookie-consent.php`:

```php
<?php
/**
 * GDPR-Compliant Cookie Consent Banner
 * 
 * This component provides a cookie consent interface compliant with GDPR requirements.
 * It allows users to accept/reject non-essential cookies and manages preferences.
 */

// Check if consent has been given
$consentGiven = isset($_COOKIE['bbx_cookie_consent']);
$consentValue = $consentGiven ? $_COOKIE['bbx_cookie_consent'] : null;

if (!$consentGiven):
?>
<div id="cookie-consent-banner" class="fixed bottom-0 left-0 right-0 bg-gray-900 text-white p-4 shadow-lg z-50 border-t-2 border-amber-400" role="dialog" aria-labelledby="cookie-consent-title" aria-describedby="cookie-consent-description">
    <div class="container mx-auto max-w-6xl">
        <div class="flex flex-col md:flex-row items-center justify-between gap-4">
            <div class="flex-1">
                <h3 id="cookie-consent-title" class="text-lg font-bold mb-2">🍪 Vi bruger cookies</h3>
                <p id="cookie-consent-description" class="text-sm text-gray-300">
                    Vi bruger nødvendige cookies for at sikre, at hjemmesiden fungerer korrekt. 
                    Med dit samtykke bruger vi også analytics-cookies til at forbedre din oplevelse. 
                    <a href="/privacy-policy.php" class="text-amber-400 underline hover:text-amber-300">Læs vores privatlivspolitik</a>
                </p>
            </div>
            <div class="flex flex-col sm:flex-row gap-3">
                <button id="cookie-settings-btn" class="px-4 py-2 bg-gray-700 hover:bg-gray-600 rounded transition text-sm font-medium">
                    ⚙️ Indstillinger
                </button>
                <button id="cookie-reject-btn" class="px-4 py-2 bg-gray-700 hover:bg-gray-600 rounded transition text-sm font-medium">
                    ❌ Afvis ikke-nødvendige
                </button>
                <button id="cookie-accept-btn" class="px-6 py-2 bg-amber-400 text-black hover:bg-amber-500 rounded transition text-sm font-bold">
                    ✓ Accepter alle
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Cookie Settings Modal -->
<div id="cookie-settings-modal" class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-[60] hidden" role="dialog" aria-labelledby="cookie-settings-title" aria-modal="true">
    <div class="bg-gray-900 border-2 border-amber-400 rounded-lg p-6 max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-4">
            <h3 id="cookie-settings-title" class="text-2xl font-bold text-white">Cookie Indstillinger</h3>
            <button id="cookie-settings-close" class="text-gray-400 hover:text-white text-2xl font-bold" aria-label="Luk">&times;</button>
        </div>
        
        <div class="space-y-4">
            <!-- Necessary Cookies (Always On) -->
            <div class="border border-gray-700 rounded p-4">
                <div class="flex items-center justify-between mb-2">
                    <h4 class="font-bold text-white">Nødvendige cookies</h4>
                    <span class="text-green-400 text-sm font-medium">Altid aktiv</span>
                </div>
                <p class="text-sm text-gray-400">
                    Disse cookies er nødvendige for at hjemmesiden kan fungere korrekt. 
                    De kan ikke deaktiveres i vores systemer.
                </p>
                <details class="mt-2">
                    <summary class="cursor-pointer text-amber-400 text-sm hover:text-amber-300">Vis detaljer</summary>
                    <ul class="mt-2 text-xs text-gray-400 list-disc list-inside">
                        <li><strong>PHPSESSID</strong>: Session identifikation (udløber ved session afslutning)</li>
                        <li><strong>bbx_cookie_consent</strong>: Gemmer dit cookie samtykke (1 år)</li>
                    </ul>
                </details>
            </div>
            
            <!-- Analytics Cookies -->
            <div class="border border-gray-700 rounded p-4">
                <div class="flex items-center justify-between mb-2">
                    <h4 class="font-bold text-white">Analytics cookies</h4>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" id="analytics-toggle" class="sr-only peer" checked>
                        <div class="w-11 h-6 bg-gray-700 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-amber-400/50 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-amber-400"></div>
                    </label>
                </div>
                <p class="text-sm text-gray-400">
                    Disse cookies hjælper os med at forstå, hvordan besøgende interagerer med hjemmesiden, 
                    så vi kan forbedre din oplevelse.
                </p>
                <details class="mt-2">
                    <summary class="cursor-pointer text-amber-400 text-sm hover:text-amber-300">Vis detaljer</summary>
                    <ul class="mt-2 text-xs text-gray-400 list-disc list-inside">
                        <li><strong>_ga</strong>: Google Analytics identifikation (2 år)</li>
                        <li><strong>_gid</strong>: Google Analytics session (24 timer)</li>
                    </ul>
                </details>
            </div>
            
            <!-- Marketing Cookies (Future) -->
            <div class="border border-gray-700 rounded p-4 opacity-60">
                <div class="flex items-center justify-between mb-2">
                    <h4 class="font-bold text-white">Marketing cookies</h4>
                    <label class="relative inline-flex items-center cursor-not-allowed">
                        <input type="checkbox" id="marketing-toggle" class="sr-only peer" disabled>
                        <div class="w-11 h-6 bg-gray-700 rounded-full cursor-not-allowed"></div>
                    </label>
                </div>
                <p class="text-sm text-gray-400">
                    <em>Ikke i brug endnu.</em> Disse cookies ville blive brugt til at vise relevante annoncer.
                </p>
            </div>
        </div>
        
        <div class="flex justify-end gap-3 mt-6">
            <button id="cookie-save-preferences" class="px-6 py-2 bg-amber-400 text-black hover:bg-amber-500 rounded font-bold transition">
                Gem indstillinger
            </button>
        </div>
    </div>
</div>

<script>
(function() {
    'use strict';
    
    const banner = document.getElementById('cookie-consent-banner');
    const modal = document.getElementById('cookie-settings-modal');
    const acceptBtn = document.getElementById('cookie-accept-btn');
    const rejectBtn = document.getElementById('cookie-reject-btn');
    const settingsBtn = document.getElementById('cookie-settings-btn');
    const closeModalBtn = document.getElementById('cookie-settings-close');
    const savePreferencesBtn = document.getElementById('cookie-save-preferences');
    const analyticsToggle = document.getElementById('analytics-toggle');
    
    // Set cookie with consent
    function setConsent(value) {
        const expiryDate = new Date();
        expiryDate.setFullYear(expiryDate.getFullYear() + 1); // 1 year
        document.cookie = `bbx_cookie_consent=${value}; expires=${expiryDate.toUTCString()}; path=/; SameSite=Strict; Secure`;
        
        // If analytics accepted, load analytics script
        if (value === 'all' || value.includes('analytics')) {
            loadAnalytics();
        }
        
        hideBanner();
    }
    
    function hideBanner() {
        if (banner) banner.style.display = 'none';
        if (modal) modal.classList.add('hidden');
    }
    
    function showModal() {
        if (modal) modal.classList.remove('hidden');
    }
    
    function hideModal() {
        if (modal) modal.classList.add('hidden');
    }
    
    function loadAnalytics() {
        // Placeholder for Google Analytics or other analytics
        console.log('Analytics tracking enabled');
        // Example: Load Google Analytics
        // (function(i,s,o,g,r,a,m){...})(window,document,'script','//www.google-analytics.com/analytics.js','ga');
    }
    
    // Event listeners
    if (acceptBtn) {
        acceptBtn.addEventListener('click', function() {
            setConsent('all');
        });
    }
    
    if (rejectBtn) {
        rejectBtn.addEventListener('click', function() {
            setConsent('necessary');
        });
    }
    
    if (settingsBtn) {
        settingsBtn.addEventListener('click', showModal);
    }
    
    if (closeModalBtn) {
        closeModalBtn.addEventListener('click', hideModal);
    }
    
    if (savePreferencesBtn) {
        savePreferencesBtn.addEventListener('click', function() {
            const analyticsEnabled = analyticsToggle.checked;
            const consent = analyticsEnabled ? 'necessary,analytics' : 'necessary';
            setConsent(consent);
        });
    }
    
    // Close modal on outside click
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                hideModal();
            }
        });
    }
})();
</script>
<?php endif; ?>
```

### Integration

Add to `/includes/site-footer.php` before closing `</body>`:

```php
<?php
// GDPR Cookie Consent Banner
require_once __DIR__ . '/cookie-consent.php';
?>
```

---

## Privacy Policy

### Privacy Policy Page

Create `/privacy-policy.php`:

```php
<?php
require_once __DIR__ . '/includes/i18n.php';
$current_page = 'privacy';
$page_title = 'Privatlivspolitik | BLACKBOX.CODES';
$meta_description = 'Læs om hvordan BLACKBOX.CODES behandler dine personoplysninger i overensstemmelse med GDPR.';
include 'includes/site-header.php';
?>

<main id="main-content" class="pt-24 pb-16">
    <div class="container mx-auto px-4 max-w-4xl">
        <h1 class="text-4xl font-bold mb-8 text-center">Privatlivspolitik</h1>
        
        <div class="prose prose-invert max-w-none space-y-6 text-gray-300">
            <p class="text-sm text-gray-500">
                <strong>Sidst opdateret:</strong> 23. november 2025
            </p>
            
            <section>
                <h2 class="text-2xl font-bold text-white mb-4">1. Introduktion</h2>
                <p>
                    Velkommen til BLACKBOX.CODES (ALPHA Interface GUI). Vi tager dit privatliv alvorligt 
                    og forpligter os til at beskytte dine personoplysninger i overensstemmelse med 
                    EU's Generelle Forordning om Databeskyttelse (GDPR).
                </p>
                <p>
                    Denne privatlivspolitik forklarer, hvilke data vi indsamler, hvorfor vi indsamler dem, 
                    og dine rettigheder som datasubjekt.
                </p>
            </section>
            
            <section>
                <h2 class="text-2xl font-bold text-white mb-4">2. Dataansvarlig</h2>
                <p>
                    <strong>AlphaAcces / BLACKBOX.CODES</strong><br>
                    E-mail: <a href="mailto:ops@blackbox.codes" class="text-amber-400 hover:text-amber-300">ops@blackbox.codes</a><br>
                    Telefon: +45 XX XX XX XX
                </p>
            </section>
            
            <section>
                <h2 class="text-2xl font-bold text-white mb-4">3. Personoplysninger vi indsamler</h2>
                
                <h3 class="text-xl font-bold text-white mb-2 mt-4">3.1 Information du giver os direkte</h3>
                <ul class="list-disc list-inside space-y-2">
                    <li><strong>Kontaktformular:</strong> Navn, e-mailadresse, besked</li>
                    <li><strong>Brugerkonti:</strong> Brugernavn, e-mail, krypteret password (kun for agenter/admins)</li>
                    <li><strong>Supporthenvendelser:</strong> Navn, e-mail, beskrivelse af problem</li>
                </ul>
                
                <h3 class="text-xl font-bold text-white mb-2 mt-4">3.2 Information vi indsamler automatisk</h3>
                <ul class="list-disc list-inside space-y-2">
                    <li><strong>Log data:</strong> IP-adresse, browsertype, besøgstidspunkt, besøgte sider</li>
                    <li><strong>Cookies:</strong> Session-ID, samtykke-præferencer, analytics (kun med dit samtykke)</li>
                    <li><strong>reCAPTCHA:</strong> Interaktionsdata (behandles af Google - se deres <a href="https://policies.google.com/privacy" class="text-amber-400">privatlivspolitik</a>)</li>
                </ul>
            </section>
            
            <section>
                <h2 class="text-2xl font-bold text-white mb-4">4. Hvorfor vi behandler dine data</h2>
                
                <table class="w-full border-collapse border border-gray-700">
                    <thead class="bg-gray-800">
                        <tr>
                            <th class="border border-gray-700 p-3 text-left">Formål</th>
                            <th class="border border-gray-700 p-3 text-left">Retsgrundlag</th>
                            <th class="border border-gray-700 p-3 text-left">Opbevaring</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="border border-gray-700 p-3">Behandle kontaktformular</td>
                            <td class="border border-gray-700 p-3">Samtykke (GDPR Art. 6(1)(a))</td>
                            <td class="border border-gray-700 p-3">2 år</td>
                        </tr>
                        <tr>
                            <td class="border border-gray-700 p-3">Sikkerhed & spam-forebyggelse</td>
                            <td class="border border-gray-700 p-3">Legitim interesse (GDPR Art. 6(1)(f))</td>
                            <td class="border border-gray-700 p-3">90 dage</td>
                        </tr>
                        <tr>
                            <td class="border border-gray-700 p-3">Brugerkonto & autentificering</td>
                            <td class="border border-gray-700 p-3">Kontraktlig forpligtelse (GDPR Art. 6(1)(b))</td>
                            <td class="border border-gray-700 p-3">Indtil konto slettes</td>
                        </tr>
                        <tr>
                            <td class="border border-gray-700 p-3">Analytics (forbedre hjemmesiden)</td>
                            <td class="border border-gray-700 p-3">Samtykke (GDPR Art. 6(1)(a))</td>
                            <td class="border border-gray-700 p-3">13 måneder</td>
                        </tr>
                    </tbody>
                </table>
            </section>
            
            <section>
                <h2 class="text-2xl font-bold text-white mb-4">5. Deling af data med tredjeparter</h2>
                <p>Vi deler kun dine personoplysninger med tredjeparter, når det er nødvendigt:</p>
                <ul class="list-disc list-inside space-y-2">
                    <li><strong>Google reCAPTCHA:</strong> Beskyttelse mod spam og misbrug</li>
                    <li><strong>Hosting-udbyder:</strong> Serverdrift og lagring (databehandleraftale på plads)</li>
                    <li><strong>E-mail-udbyder (Proton Mail):</strong> Afsendelse af e-mails</li>
                    <li><strong>Cloudflare:</strong> CDN og DDoS-beskyttelse</li>
                </ul>
                <p class="mt-2">
                    Vi sælger <strong>aldrig</strong> dine personoplysninger til tredjeparter.
                </p>
            </section>
            
            <section>
                <h2 class="text-2xl font-bold text-white mb-4">6. Dine rettigheder under GDPR</h2>
                <p>Du har følgende rettigheder i forhold til dine personoplysninger:</p>
                <ul class="list-disc list-inside space-y-2">
                    <li><strong>Ret til indsigt (Art. 15):</strong> Få en kopi af de data vi har om dig</li>
                    <li><strong>Ret til berigtigelse (Art. 16):</strong> Korriger ukorrekte eller ufuldstændige data</li>
                    <li><strong>Ret til sletning (Art. 17):</strong> Få dine data slettet ("retten til at blive glemt")</li>
                    <li><strong>Ret til begrænsning (Art. 18):</strong> Begræns behandlingen af dine data</li>
                    <li><strong>Ret til dataportabilitet (Art. 20):</strong> Modtag dine data i struktureret format</li>
                    <li><strong>Ret til indsigelse (Art. 21):</strong> Gør indsigelse mod behandling baseret på legitim interesse</li>
                    <li><strong>Ret til at trække samtykke tilbage (Art. 7):</strong> Tilbagetræk dit samtykke når som helst</li>
                </ul>
                <p class="mt-4">
                    For at udøve dine rettigheder, kontakt os på: 
                    <a href="mailto:ops@blackbox.codes" class="text-amber-400 hover:text-amber-300">ops@blackbox.codes</a>
                </p>
                <p>
                    Vi besvarer alle anmodninger inden for <strong>30 dage</strong> som krævet af GDPR.
                </p>
            </section>
            
            <section>
                <h2 class="text-2xl font-bold text-white mb-4">7. Datasikkerhed</h2>
                <p>Vi implementerer passende tekniske og organisatoriske sikkerhedsforanstaltninger:</p>
                <ul class="list-disc list-inside space-y-2">
                    <li>🔐 TLS/HTTPS-kryptering for alle datatransmissioner</li>
                    <li>🔐 Bcrypt-hashing af passwords (aldrig lagret i klartekst)</li>
                    <li>🔐 Firewall og DDoS-beskyttelse via Cloudflare</li>
                    <li>🔐 Regelmæssige sikkerhedsaudits og penetrationstest</li>
                    <li>🔐 Adgangskontrol og multi-faktor autentificering for admins</li>
                    <li>🔐 Audit logs for alle dataadgang</li>
                </ul>
            </section>
            
            <section>
                <h2 class="text-2xl font-bold text-white mb-4">8. Databrud</h2>
                <p>
                    I tilfælde af et databrud, der sandsynligvis indebærer en risiko for dine rettigheder og friheder, 
                    vil vi underrette dig og datatilsynet inden for <strong>72 timer</strong> som krævet af GDPR Art. 33-34.
                </p>
            </section>
            
            <section>
                <h2 class="text-2xl font-bold text-white mb-4">9. Internationale dataoverførsler</h2>
                <p>
                    Dine data behandles primært inden for EU/EØS. Hvis data overføres til tredjelande, 
                    sikrer vi passende beskyttelse gennem:
                </p>
                <ul class="list-disc list-inside space-y-2">
                    <li>EU's Standardkontraktbestemmelser (SCC)</li>
                    <li>Certificeringer (fx ISO 27001)</li>
                    <li>Privacy Shield efterfølgere</li>
                </ul>
            </section>
            
            <section>
                <h2 class="text-2xl font-bold text-white mb-4">10. Børns privatliv</h2>
                <p>
                    Vores tjeneste er ikke rettet mod børn under 16 år. Vi indsamler ikke bevidst 
                    personoplysninger fra børn. Hvis du er forælder og opdager, at dit barn har givet os 
                    personoplysninger, kontakt os venligst.
                </p>
            </section>
            
            <section>
                <h2 class="text-2xl font-bold text-white mb-4">11. Cookies</h2>
                <p>
                    Vi bruger cookies til at forbedre din oplevelse. Se vores 
                    <a href="#" id="open-cookie-settings-link" class="text-amber-400 hover:text-amber-300">cookie-indstillinger</a> 
                    for at styre dine præferencer.
                </p>
            </section>
            
            <section>
                <h2 class="text-2xl font-bold text-white mb-4">12. Ændringer til denne politik</h2>
                <p>
                    Vi kan opdatere denne privatlivspolitik fra tid til anden. Væsentlige ændringer vil blive 
                    kommunikeret via e-mail eller bannere på hjemmesiden. Tjek "Sidst opdateret" datoen øverst.
                </p>
            </section>
            
            <section>
                <h2 class="text-2xl font-bold text-white mb-4">13. Klage til datatilsynet</h2>
                <p>
                    Hvis du mener, at din behandling af personoplysninger krænker GDPR, har du ret til at indgive 
                    en klage til datatilsynet:
                </p>
                <p class="mt-2">
                    <strong>Datatilsynet (Danmark)</strong><br>
                    Carl Jacobsens Vej 35<br>
                    2500 Valby<br>
                    E-mail: <a href="mailto:dt@datatilsynet.dk" class="text-amber-400">dt@datatilsynet.dk</a><br>
                    Telefon: +45 33 19 32 00
                </p>
            </section>
            
            <section>
                <h2 class="text-2xl font-bold text-white mb-4">14. Kontakt os</h2>
                <p>
                    Hvis du har spørgsmål om denne privatlivspolitik eller vores behandling af dine personoplysninger:
                </p>
                <p class="mt-2">
                    <strong>E-mail:</strong> <a href="mailto:ops@blackbox.codes" class="text-amber-400 hover:text-amber-300">ops@blackbox.codes</a><br>
                    <strong>Telefon:</strong> +45 XX XX XX XX<br>
                    <strong>Adresse:</strong> [Din adresse her]
                </p>
            </section>
        </div>
    </div>
</main>

<script>
// Open cookie settings when link is clicked
document.getElementById('open-cookie-settings-link')?.addEventListener('click', function(e) {
    e.preventDefault();
    document.getElementById('cookie-settings-modal')?.classList.remove('hidden');
});
</script>

<?php include 'includes/site-footer.php'; ?>
```

---

## Data Retention Policies

### Retention Schedule

Create `/docs/DATA_RETENTION_POLICY.md`:

```markdown
# Data Retention Policy

## Overview
This policy defines how long we retain different types of personal data in compliance with GDPR Article 5(1)(e) - storage limitation principle.

## Retention Periods

| Data Category | Retention Period | Justification | Disposal Method |
|---------------|------------------|---------------|-----------------|
| Contact form submissions | 2 years | Business communication | Secure deletion |
| User account data | Until account deleted + 30 days | Service delivery | Anonymization |
| Session logs | 90 days | Security & fraud detection | Automatic deletion |
| IP address logs | 90 days | Security monitoring | Automatic deletion |
| Email communications | 2 years | Legal & business purposes | Secure deletion |
| reCAPTCHA data | Processed externally | Spam prevention | Google's policy |
| Backup data | 90 days (rolling) | Disaster recovery | Encrypted overwrite |
| Audit logs | 5 years | Compliance & legal | Encrypted archive |

## Automated Deletion

Implement cron job for automatic data deletion:

```bash
# /etc/cron.daily/cleanup-old-data.sh
#!/bin/bash
# Delete contact submissions older than 2 years
mysql -u root -p alpha_db -e "DELETE FROM contact_submissions WHERE created_at < DATE_SUB(NOW(), INTERVAL 2 YEAR);"

# Delete old session logs (90 days)
mysql -u root -p alpha_db -e "DELETE FROM session_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL 90 DAY);"

# Delete old IP logs (90 days)
find /var/log/alpha/ -name "*.log" -mtime +90 -delete
```

## User-Requested Deletion

Process within 30 days:
1. Verify identity
2. Delete all associated data
3. Confirm to user via email
4. Log deletion in audit trail
```

---

## User Rights Implementation

### Data Subject Request Handler

Create `/includes/gdpr-requests.php`:

```php
<?php
/**
 * GDPR Data Subject Request Handler
 * 
 * Handles user requests for data access, rectification, and deletion.
 */

class GDPRRequestHandler {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Right to Access (GDPR Art. 15)
     * Export all data for a user
     */
    public function exportUserData($userId) {
        $data = [
            'user_info' => $this->getUserInfo($userId),
            'contact_submissions' => $this->getContactSubmissions($userId),
            'login_history' => $this->getLoginHistory($userId),
            'preferences' => $this->getUserPreferences($userId)
        ];
        
        return json_encode($data, JSON_PRETTY_PRINT);
    }
    
    /**
     * Right to Erasure (GDPR Art. 17)
     * Delete all user data
     */
    public function deleteUserData($userId) {
        $this->db->begin_transaction();
        
        try {
            // Anonymize contact submissions (keep for statistics)
            $stmt = $this->db->prepare("UPDATE contact_submissions SET name = 'Deleted User', email = 'deleted@example.com', message = '[REDACTED]' WHERE user_id = ?");
            $stmt->bind_param('i', $userId);
            $stmt->execute();
            
            // Delete user account
            $stmt = $this->db->prepare("DELETE FROM users WHERE id = ?");
            $stmt->bind_param('i', $userId);
            $stmt->execute();
            
            // Delete sessions
            $stmt = $this->db->prepare("DELETE FROM sessions WHERE user_id = ?");
            $stmt->bind_param('i', $userId);
            $stmt->execute();
            
            // Log deletion
            $this->logGDPRAction('deletion', $userId);
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollback();
            error_log("GDPR deletion failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Right to Rectification (GDPR Art. 16)
     * Update user data
     */
    public function updateUserData($userId, $field, $newValue) {
        $allowedFields = ['name', 'email', 'phone'];
        
        if (!in_array($field, $allowedFields)) {
            return false;
        }
        
        $stmt = $this->db->prepare("UPDATE users SET $field = ? WHERE id = ?");
        $stmt->bind_param('si', $newValue, $userId);
        
        if ($stmt->execute()) {
            $this->logGDPRAction('rectification', $userId, "$field updated");
            return true;
        }
        
        return false;
    }
    
    private function logGDPRAction($action, $userId, $details = '') {
        $stmt = $this->db->prepare("INSERT INTO gdpr_audit_log (action, user_id, details, timestamp) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param('sis', $action, $userId, $details);
        $stmt->execute();
    }
}
```

---

## Compliance Checklist

### Pre-Launch Checklist

- [ ] **Lawful Basis Documented**: All processing has documented legal basis
- [ ] **Cookie Consent Banner**: Implemented and tested
- [ ] **Privacy Policy**: Published and accessible
- [ ] **Data Retention Policy**: Defined and automated
- [ ] **Security Measures**: HTTPS, encryption, access controls in place
- [ ] **Data Processing Agreement**: Signed with all processors (hosting, email, etc.)
- [ ] **Breach Notification Procedure**: Documented and tested
- [ ] **User Rights Mechanism**: Request forms and processes in place
- [ ] **Data Protection Impact Assessment (DPIA)**: Completed if high-risk processing
- [ ] **Staff Training**: Team trained on GDPR compliance
- [ ] **Audit Logging**: Comprehensive logging of data access
- [ ] **Backup and Recovery**: Tested and documented

### Ongoing Compliance

- [ ] **Quarterly Reviews**: Review data processing activities
- [ ] **Annual DPIA Update**: Update risk assessments
- [ ] **Breach Drills**: Test incident response procedures
- [ ] **Policy Updates**: Keep privacy policy current
- [ ] **User Request Log**: Track and respond to DSARs within 30 days
- [ ] **Vendor Audits**: Review processor compliance annually

---

## Contact

For GDPR-related questions or to exercise your rights:

- **Email**: ops@blackbox.codes
- **Data Protection Officer**: [If appointed]
- **Response Time**: Within 30 days (GDPR requirement)

---

**Document Version**: 1.0  
**Last Updated**: 2025-11-23  
**Next Review**: 2026-02-23 (Quarterly)  
**Owner**: ALPHA-CI-Security-Agent
