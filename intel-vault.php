<?php

/**
 * Intel Vault Page
 *
 * Secure document storage and intelligence repository
 * Placeholder page for future implementation
 */

session_start();

// Check authentication
if (!isset($_SESSION['agent_id'])) {
  header('Location: agent-login.php');
  exit;
}

// Page configuration
$page_title = 'Intel Vault';
$current_admin_page = 'intel-vault';

// Include admin layout
require_once __DIR__ . '/includes/admin-layout.php';
?>

<!-- Intel Vault Content -->
<div class="admin-page intel-vault">
  <!-- Page Header -->
  <header class="admin-page__header">
    <div class="admin-page__header-content">
      <h1 class="admin-page__title">
        <span class="admin-page__title-icon" aria-hidden="true">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
            <rect x="3" y="11" width="18" height="11" rx="2" ry="2" />
            <path d="M7 11V7a5 5 0 0 1 10 0v4" />
            <circle cx="12" cy="16" r="1" />
          </svg>
        </span>
        Intel Vault
      </h1>
      <p class="admin-page__subtitle">
        Sikker dokumenthåndtering og efterretningslager
      </p>
    </div>
    <div class="admin-page__header-actions">
      <button type="button" class="admin-btn admin-btn--secondary" disabled title="Kommer snart">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" width="16" height="16">
          <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
          <polyline points="17 8 12 3 7 8" />
          <line x1="12" y1="3" x2="12" y2="15" />
        </svg>
        Upload dokument
      </button>
    </div>
  </header>

  <!-- Coming Soon Notice -->
  <section class="intel-vault__notice" aria-labelledby="vault-notice-heading">
    <div class="intel-vault__notice-icon" aria-hidden="true">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
        <circle cx="12" cy="12" r="10" />
        <path d="M12 6v6l4 2" />
      </svg>
    </div>
    <h2 id="vault-notice-heading" class="intel-vault__notice-title">Under udvikling</h2>
    <p class="intel-vault__notice-description">
      Intel Vault er under aktiv udvikling og vil snart være tilgængelig.
      Her vil du kunne gemme, organisere og søge i klassificerede dokumenter og efterretningsdata.
    </p>
  </section>

  <!-- Feature Preview Cards -->
  <section class="intel-vault__features" aria-labelledby="vault-features-heading">
    <h2 id="vault-features-heading" class="sr-only">Kommende funktioner</h2>

    <div class="intel-vault__feature-grid">
      <!-- Encrypted Storage -->
      <article class="intel-vault__feature">
        <div class="intel-vault__feature-icon" aria-hidden="true">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
          </svg>
        </div>
        <h3 class="intel-vault__feature-title">Krypteret lager</h3>
        <p class="intel-vault__feature-description">
          End-to-end kryptering med AES-256 og nøglehåndtering via hardware security modules (HSM).
        </p>
        <span class="intel-vault__feature-status">Planlagt</span>
      </article>

      <!-- Document Classification -->
      <article class="intel-vault__feature">
        <div class="intel-vault__feature-icon" aria-hidden="true">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
            <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z" />
            <line x1="12" y1="11" x2="12" y2="17" />
            <line x1="9" y1="14" x2="15" y2="14" />
          </svg>
        </div>
        <h3 class="intel-vault__feature-title">Klassifikationssystem</h3>
        <p class="intel-vault__feature-description">
          Multi-niveau klassifikation fra UKLASSIFICERET til STRENGT FORTROLIGT med automatisk markering.
        </p>
        <span class="intel-vault__feature-status">Planlagt</span>
      </article>

      <!-- Full-text Search -->
      <article class="intel-vault__feature">
        <div class="intel-vault__feature-icon" aria-hidden="true">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
            <circle cx="11" cy="11" r="8" />
            <line x1="21" y1="21" x2="16.65" y2="16.65" />
          </svg>
        </div>
        <h3 class="intel-vault__feature-title">Fuldtekstsøgning</h3>
        <p class="intel-vault__feature-description">
          Avanceret søgning med OCR-understøttelse, filtrering og semantisk matching via AI.
        </p>
        <span class="intel-vault__feature-status">Planlagt</span>
      </article>

      <!-- Audit Trail -->
      <article class="intel-vault__feature">
        <div class="intel-vault__feature-icon" aria-hidden="true">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
            <polyline points="14 2 14 8 20 8" />
            <line x1="16" y1="13" x2="8" y2="13" />
            <line x1="16" y1="17" x2="8" y2="17" />
            <polyline points="10 9 9 9 8 9" />
          </svg>
        </div>
        <h3 class="intel-vault__feature-title">Fuld sporbarhed</h3>
        <p class="intel-vault__feature-description">
          Komplet audit trail for alle handlinger – hvem åbnede, redigerede eller delte dokumenter.
        </p>
        <span class="intel-vault__feature-status">Planlagt</span>
      </article>

      <!-- Access Control -->
      <article class="intel-vault__feature">
        <div class="intel-vault__feature-icon" aria-hidden="true">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
            <circle cx="9" cy="7" r="4" />
            <path d="M23 21v-2a4 4 0 0 0-3-3.87" />
            <path d="M16 3.13a4 4 0 0 1 0 7.75" />
          </svg>
        </div>
        <h3 class="intel-vault__feature-title">Adgangskontrol</h3>
        <p class="intel-vault__feature-description">
          Finkornet adgangsstyring baseret på roller, clearance-niveau og need-to-know principper.
        </p>
        <span class="intel-vault__feature-status">Planlagt</span>
      </article>

      <!-- Secure Sharing -->
      <article class="intel-vault__feature">
        <div class="intel-vault__feature-icon" aria-hidden="true">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
            <circle cx="18" cy="5" r="3" />
            <circle cx="6" cy="12" r="3" />
            <circle cx="18" cy="19" r="3" />
            <line x1="8.59" y1="13.51" x2="15.42" y2="17.49" />
            <line x1="15.41" y1="6.51" x2="8.59" y2="10.49" />
          </svg>
        </div>
        <h3 class="intel-vault__feature-title">Sikker deling</h3>
        <p class="intel-vault__feature-description">
          Tidsbegrænset deling med vandmærkning, download-begrænsninger og automatisk udløb.
        </p>
        <span class="intel-vault__feature-status">Planlagt</span>
      </article>
    </div>
  </section>

  <!-- Placeholder Document List -->
  <section class="intel-vault__documents" aria-labelledby="vault-documents-heading">
    <header class="intel-vault__documents-header">
      <h2 id="vault-documents-heading" class="intel-vault__section-title">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" width="20" height="20">
          <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
          <polyline points="14 2 14 8 20 8" />
        </svg>
        Seneste dokumenter
      </h2>
      <div class="intel-vault__search">
        <input type="search"
          placeholder="Søg i vault..."
          class="intel-vault__search-input"
          disabled
          aria-label="Søg i dokumenter">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="intel-vault__search-icon">
          <circle cx="11" cy="11" r="8" />
          <line x1="21" y1="21" x2="16.65" y2="16.65" />
        </svg>
      </div>
    </header>

    <div class="intel-vault__empty-state">
      <div class="intel-vault__empty-icon" aria-hidden="true">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2">
          <rect x="3" y="11" width="18" height="11" rx="2" ry="2" />
          <path d="M7 11V7a5 5 0 0 1 10 0v4" />
        </svg>
      </div>
      <p class="intel-vault__empty-text">
        Intel Vault er tom. Når funktionen lanceres, vil dine dokumenter vises her.
      </p>
    </div>
  </section>
</div>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
