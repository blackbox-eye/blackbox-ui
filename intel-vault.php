<?php

/**
 * Intel Vault Page
 *
 * Secure document storage and intelligence repository
 * Features AES-256-GCM encryption for all uploads.
 */

session_start();

// Check authentication
if (!isset($_SESSION['agent_id'])) {
  header('Location: agent-login.php');
  exit;
}

// Load dependencies
require_once __DIR__ . '/includes/env.php';
require_once __DIR__ . '/includes/vault-encryption.php';
require_once __DIR__ . '/db.php';

// Page configuration
$page_title = 'Intel Vault';
$current_admin_page = 'intel-vault';

$agentId = (int) $_SESSION['agent_id'];
$isAdmin = !empty($_SESSION['is_admin']);

// Fetch documents from database
$documents = [];
$totalSize = 0;
$dbError = null;

if (defined('BBX_DB_CONNECTED') && BBX_DB_CONNECTED && isset($pdo)) {
  try {
    // Fetch documents the user has access to
    if ($isAdmin) {
      // Admins see all documents
      $stmt = $pdo->prepare("
        SELECT
          d.*,
          a.name AS uploader_name
        FROM intel_vault_documents d
        LEFT JOIN agents a ON d.uploaded_by = a.id
        WHERE d.deleted_at IS NULL
        ORDER BY d.created_at DESC
        LIMIT 100
      ");
      $stmt->execute();
    } else {
      // Regular users see their own and shared documents
      $stmt = $pdo->prepare("
        SELECT
          d.*,
          a.name AS uploader_name
        FROM intel_vault_documents d
        LEFT JOIN agents a ON d.uploaded_by = a.id
        WHERE d.deleted_at IS NULL
        AND (d.uploaded_by = :agent_id OR d.access_level = 'all')
        ORDER BY d.created_at DESC
        LIMIT 100
      ");
      $stmt->execute([':agent_id' => $agentId]);
    }

    $documents = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calculate total size
    foreach ($documents as $doc) {
      $totalSize += (int) $doc['file_size'];
    }
  } catch (PDOException $e) {
    error_log('Intel Vault fetch error: ' . $e->getMessage());
    $dbError = 'Kunne ikke hente dokumenter fra databasen';
  }
}

// Get classification levels for display
$classifications = vault_classification_levels();

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
        Sikker dokumenthåndtering med AES-256-GCM kryptering
      </p>
    </div>
    <div class="admin-page__header-actions">
      <button type="button" class="admin-btn admin-btn--primary" id="uploadTrigger">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" width="16" height="16">
          <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
          <polyline points="17 8 12 3 7 8" />
          <line x1="12" y1="3" x2="12" y2="15" />
        </svg>
        Upload dokument
      </button>
    </div>
  </header>

  <!-- Stats Overview -->
  <section class="intel-vault__stats">
    <div class="intel-vault__stat">
      <span class="intel-vault__stat-value"><?php echo count($documents); ?></span>
      <span class="intel-vault__stat-label">Dokumenter</span>
    </div>
    <div class="intel-vault__stat">
      <span class="intel-vault__stat-value"><?php echo vault_format_size($totalSize); ?></span>
      <span class="intel-vault__stat-label">Total størrelse</span>
    </div>
    <div class="intel-vault__stat">
      <span class="intel-vault__stat-value"><?php echo vault_format_size(vault_max_upload_size()); ?></span>
      <span class="intel-vault__stat-label">Max upload</span>
    </div>
  </section>

  <?php if ($dbError): ?>
    <div class="intel-vault__error" role="alert">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
        <circle cx="12" cy="12" r="10" />
        <line x1="12" y1="8" x2="12" y2="12" />
        <line x1="12" y1="16" x2="12.01" y2="16" />
      </svg>
      <?php echo htmlspecialchars($dbError); ?>
    </div>
  <?php endif; ?>

  <!-- Upload Modal -->
  <div class="intel-vault__modal" id="uploadModal" aria-hidden="true" role="dialog" aria-labelledby="uploadModalTitle">
    <div class="intel-vault__modal-backdrop" data-close-modal></div>
    <div class="intel-vault__modal-content">
      <button type="button" class="intel-vault__modal-close" data-close-modal aria-label="Luk">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
          <line x1="18" y1="6" x2="6" y2="18" />
          <line x1="6" y1="6" x2="18" y2="18" />
        </svg>
      </button>

      <h2 id="uploadModalTitle" class="intel-vault__modal-title">Upload dokument</h2>
      <p class="intel-vault__modal-description">
        Filer krypteres med AES-256-GCM før de gemmes. Kun autoriserede brugere kan dekryptere.
      </p>

      <form id="uploadForm" enctype="multipart/form-data">
        <!-- Drop Zone -->
        <div class="intel-vault__dropzone" id="dropzone">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="48" height="48">
            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
            <polyline points="17 8 12 3 7 8" />
            <line x1="12" y1="3" x2="12" y2="15" />
          </svg>
          <p class="intel-vault__dropzone-text">
            Træk filer hertil eller <button type="button" class="intel-vault__dropzone-btn">vælg filer</button>
          </p>
          <p class="intel-vault__dropzone-hint">
            Max <?php echo vault_format_size(vault_max_upload_size()); ?> • PDF, DOCX, XLSX, billeder, arkiver
          </p>
          <input type="file" id="fileInput" name="file" class="intel-vault__file-input" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.csv,.rtf,.jpg,.jpeg,.png,.gif,.webp,.svg,.zip,.rar,.7z,.json,.xml">
        </div>

        <!-- Selected File Info -->
        <div class="intel-vault__selected-file" id="selectedFile" hidden>
          <div class="intel-vault__selected-file-info">
            <span class="intel-vault__selected-file-name" id="selectedFileName"></span>
            <span class="intel-vault__selected-file-size" id="selectedFileSize"></span>
          </div>
          <button type="button" class="intel-vault__selected-file-remove" id="removeFile" aria-label="Fjern fil">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
              <line x1="18" y1="6" x2="6" y2="18" />
              <line x1="6" y1="6" x2="18" y2="18" />
            </svg>
          </button>
        </div>

        <!-- Classification -->
        <div class="intel-vault__form-group">
          <label for="classification" class="intel-vault__label">Klassifikation</label>
          <select id="classification" name="classification" class="intel-vault__select">
            <?php foreach ($classifications as $key => $level): ?>
              <option value="<?php echo $key; ?>" <?php echo $key === 'internal' ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($level['label']); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Description -->
        <div class="intel-vault__form-group">
          <label for="description" class="intel-vault__label">Beskrivelse <span class="optional">(valgfri)</span></label>
          <textarea id="description" name="description" class="intel-vault__textarea" rows="3" placeholder="Kort beskrivelse af dokumentets indhold..."></textarea>
        </div>

        <!-- Upload Progress -->
        <div class="intel-vault__progress" id="uploadProgress" hidden>
          <div class="intel-vault__progress-bar" id="progressBar"></div>
          <span class="intel-vault__progress-text" id="progressText">Krypterer og uploader...</span>
        </div>

        <!-- Actions -->
        <div class="intel-vault__modal-actions">
          <button type="button" class="admin-btn admin-btn--secondary" data-close-modal>Annuller</button>
          <button type="submit" class="admin-btn admin-btn--primary" id="uploadBtn" disabled>
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
              <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
            </svg>
            Kryptér og upload
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- Document List -->
  <section class="intel-vault__documents" aria-labelledby="vault-documents-heading">
    <header class="intel-vault__documents-header">
      <h2 id="vault-documents-heading" class="intel-vault__section-title">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" width="20" height="20">
          <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
          <polyline points="14 2 14 8 20 8" />
        </svg>
        Dokumenter
      </h2>
      <div class="intel-vault__search">
        <input type="search"
          placeholder="Søg i vault..."
          class="intel-vault__search-input"
          id="searchInput"
          aria-label="Søg i dokumenter">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="intel-vault__search-icon">
          <circle cx="11" cy="11" r="8" />
          <line x1="21" y1="21" x2="16.65" y2="16.65" />
        </svg>
      </div>
    </header>

    <?php if (empty($documents)): ?>
      <div class="intel-vault__empty-state">
        <div class="intel-vault__empty-icon" aria-hidden="true">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2">
            <rect x="3" y="11" width="18" height="11" rx="2" ry="2" />
            <path d="M7 11V7a5 5 0 0 1 10 0v4" />
          </svg>
        </div>
        <p class="intel-vault__empty-text">
          Intel Vault er tom. Upload dit første dokument for at komme i gang.
        </p>
      </div>
    <?php else: ?>
      <div class="intel-vault__list" id="documentList">
        <?php foreach ($documents as $doc): ?>
          <?php
          $classLevel = $classifications[$doc['classification']] ?? $classifications['internal'];
          $extension = strtolower(pathinfo($doc['original_name'], PATHINFO_EXTENSION));
          $iconClass = 'file';
          if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'])) {
            $iconClass = 'image';
          } elseif ($extension === 'pdf') {
            $iconClass = 'pdf';
          } elseif (in_array($extension, ['doc', 'docx'])) {
            $iconClass = 'word';
          } elseif (in_array($extension, ['xls', 'xlsx', 'csv'])) {
            $iconClass = 'excel';
          } elseif (in_array($extension, ['zip', 'rar', '7z'])) {
            $iconClass = 'archive';
          }
          ?>
          <article class="intel-vault__item" data-uuid="<?php echo htmlspecialchars($doc['uuid']); ?>" data-name="<?php echo htmlspecialchars(strtolower($doc['original_name'])); ?>">
            <div class="intel-vault__item-icon intel-vault__item-icon--<?php echo $iconClass; ?>">
              <?php if ($iconClass === 'pdf'): ?>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                  <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                  <polyline points="14 2 14 8 20 8" />
                  <path d="M10 12h4" />
                  <path d="M10 16h4" />
                </svg>
              <?php elseif ($iconClass === 'image'): ?>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                  <rect x="3" y="3" width="18" height="18" rx="2" ry="2" />
                  <circle cx="8.5" cy="8.5" r="1.5" />
                  <polyline points="21 15 16 10 5 21" />
                </svg>
              <?php else: ?>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                  <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                  <polyline points="14 2 14 8 20 8" />
                </svg>
              <?php endif; ?>
            </div>

            <div class="intel-vault__item-info">
              <h3 class="intel-vault__item-name"><?php echo htmlspecialchars($doc['original_name']); ?></h3>
              <div class="intel-vault__item-meta">
                <span class="intel-vault__item-size"><?php echo vault_format_size((int) $doc['file_size']); ?></span>
                <span class="intel-vault__item-date"><?php echo date('d. M Y H:i', strtotime($doc['created_at'])); ?></span>
                <?php if (!empty($doc['uploader_name'])): ?>
                  <span class="intel-vault__item-uploader"><?php echo htmlspecialchars($doc['uploader_name']); ?></span>
                <?php endif; ?>
              </div>
            </div>

            <span class="intel-vault__item-badge" style="--badge-color: <?php echo $classLevel['color']; ?>">
              <?php echo htmlspecialchars($classLevel['label']); ?>
            </span>

            <div class="intel-vault__item-actions">
              <a href="api/vault-download.php?uuid=<?php echo urlencode($doc['uuid']); ?>"
                class="intel-vault__item-btn"
                title="Download"
                download>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                  <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                  <polyline points="7 10 12 15 17 10" />
                  <line x1="12" y1="15" x2="12" y2="3" />
                </svg>
              </a>
              <?php if ($isAdmin || (int) $doc['uploaded_by'] === $agentId): ?>
                <button type="button"
                  class="intel-vault__item-btn intel-vault__item-btn--danger"
                  title="Slet"
                  data-delete="<?php echo htmlspecialchars($doc['uuid']); ?>">
                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                    <polyline points="3 6 5 6 21 6" />
                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                  </svg>
                </button>
              <?php endif; ?>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </section>
</div>

<script>
  (function() {
    'use strict';

    // DOM elements
    const uploadTrigger = document.getElementById('uploadTrigger');
    const uploadModal = document.getElementById('uploadModal');
    const uploadForm = document.getElementById('uploadForm');
    const dropzone = document.getElementById('dropzone');
    const fileInput = document.getElementById('fileInput');
    const selectedFile = document.getElementById('selectedFile');
    const selectedFileName = document.getElementById('selectedFileName');
    const selectedFileSize = document.getElementById('selectedFileSize');
    const removeFileBtn = document.getElementById('removeFile');
    const uploadBtn = document.getElementById('uploadBtn');
    const uploadProgress = document.getElementById('uploadProgress');
    const progressBar = document.getElementById('progressBar');
    const progressText = document.getElementById('progressText');
    const searchInput = document.getElementById('searchInput');
    const documentList = document.getElementById('documentList');

    let currentFile = null;

    // Format file size
    function formatSize(bytes) {
      const units = ['B', 'KB', 'MB', 'GB'];
      let i = 0;
      while (bytes >= 1024 && i < units.length - 1) {
        bytes /= 1024;
        i++;
      }
      return bytes.toFixed(1) + ' ' + units[i];
    }

    // Open modal
    function openModal() {
      uploadModal.setAttribute('aria-hidden', 'false');
      uploadModal.classList.add('is-open');
      document.body.style.overflow = 'hidden';
      uploadModal.querySelector('[data-close-modal]').focus();
    }

    // Close modal
    function closeModal() {
      uploadModal.setAttribute('aria-hidden', 'true');
      uploadModal.classList.remove('is-open');
      document.body.style.overflow = '';
      resetForm();
    }

    // Reset form
    function resetForm() {
      uploadForm.reset();
      currentFile = null;
      fileInput.value = '';
      selectedFile.hidden = true;
      uploadBtn.disabled = true;
      uploadProgress.hidden = true;
      progressBar.style.width = '0%';
    }

    // Handle file selection
    function handleFile(file) {
      if (!file) return;

      const maxSize = <?php echo vault_max_upload_size(); ?>;
      if (file.size > maxSize) {
        alert('Filen er for stor. Max størrelse: ' + formatSize(maxSize));
        return;
      }

      currentFile = file;
      selectedFileName.textContent = file.name;
      selectedFileSize.textContent = formatSize(file.size);
      selectedFile.hidden = false;
      uploadBtn.disabled = false;
      dropzone.classList.add('has-file');
    }

    // Upload trigger
    uploadTrigger.addEventListener('click', openModal);

    // Close modal events
    document.querySelectorAll('[data-close-modal]').forEach(btn => {
      btn.addEventListener('click', closeModal);
    });

    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && uploadModal.classList.contains('is-open')) {
        closeModal();
      }
    });

    // File input change
    fileInput.addEventListener('change', (e) => {
      handleFile(e.target.files[0]);
    });

    // Dropzone click
    dropzone.addEventListener('click', () => {
      fileInput.click();
    });

    // Drag and drop
    dropzone.addEventListener('dragover', (e) => {
      e.preventDefault();
      dropzone.classList.add('dragover');
    });

    dropzone.addEventListener('dragleave', () => {
      dropzone.classList.remove('dragover');
    });

    dropzone.addEventListener('drop', (e) => {
      e.preventDefault();
      dropzone.classList.remove('dragover');
      handleFile(e.dataTransfer.files[0]);
    });

    // Remove file
    removeFileBtn.addEventListener('click', (e) => {
      e.stopPropagation();
      currentFile = null;
      fileInput.value = '';
      selectedFile.hidden = true;
      uploadBtn.disabled = true;
      dropzone.classList.remove('has-file');
    });

    // Form submit
    uploadForm.addEventListener('submit', async (e) => {
      e.preventDefault();

      if (!currentFile) return;

      uploadBtn.disabled = true;
      uploadProgress.hidden = false;
      progressText.textContent = 'Krypterer og uploader...';

      const formData = new FormData();
      formData.append('file', currentFile);
      formData.append('classification', document.getElementById('classification').value);
      formData.append('description', document.getElementById('description').value);

      try {
        const xhr = new XMLHttpRequest();

        xhr.upload.addEventListener('progress', (e) => {
          if (e.lengthComputable) {
            const percent = (e.loaded / e.total) * 100;
            progressBar.style.width = percent + '%';

            if (percent === 100) {
              progressText.textContent = 'Krypterer på serveren...';
            }
          }
        });

        xhr.onload = function() {
          if (xhr.status === 200) {
            const response = JSON.parse(xhr.responseText);
            if (response.success) {
              progressText.textContent = 'Upload færdig!';
              progressBar.style.width = '100%';

              setTimeout(() => {
                closeModal();
                window.location.reload();
              }, 500);
            } else {
              throw new Error(response.error || 'Upload fejlede');
            }
          } else {
            const response = JSON.parse(xhr.responseText);
            throw new Error(response.error || 'Server fejl');
          }
        };

        xhr.onerror = function() {
          throw new Error('Netværksfejl');
        };

        xhr.open('POST', 'api/vault-upload.php');
        xhr.send(formData);

      } catch (error) {
        alert('Fejl: ' + error.message);
        uploadBtn.disabled = false;
        uploadProgress.hidden = true;
      }
    });

    // Search functionality
    if (searchInput && documentList) {
      searchInput.addEventListener('input', (e) => {
        const query = e.target.value.toLowerCase();
        const items = documentList.querySelectorAll('.intel-vault__item');

        items.forEach(item => {
          const name = item.dataset.name || '';
          item.style.display = name.includes(query) ? '' : 'none';
        });
      });
    }

    // Delete functionality
    document.querySelectorAll('[data-delete]').forEach(btn => {
      btn.addEventListener('click', async () => {
        const uuid = btn.dataset.delete;

        if (!confirm('Er du sikker på, at du vil slette dette dokument? Denne handling kan ikke fortrydes.')) {
          return;
        }

        try {
          const response = await fetch('api/vault-delete.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json'
            },
            body: JSON.stringify({
              uuid
            })
          });

          const data = await response.json();

          if (data.success) {
            btn.closest('.intel-vault__item').remove();
          } else {
            alert('Fejl: ' + (data.error || 'Kunne ikke slette dokumentet'));
          }
        } catch (error) {
          alert('Fejl: ' + error.message);
        }
      });
    });
  })();
</script>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
