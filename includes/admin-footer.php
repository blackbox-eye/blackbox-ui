<?php

/**
 * Admin Footer Template
 *
 * Lukker admin-layoutet og inkluderer JavaScript
 */
?>
</main>

<footer class="admin-footer-meta" aria-label="Admin status footer">
  <span class="admin-footer-meta__label">Blackbox EYE Control Panel</span>
  <?php if (defined('BBX_QA_MODE') && BBX_QA_MODE): ?>
    <span class="qa-version-chip">ALPHA-GUI v1.0.0-QA</span>
  <?php endif; ?>
</footer>

<?php if (defined('BBX_QA_MODE') && BBX_QA_MODE) {
  include __DIR__ . '/components/qa-debug-panel.php';
} ?>

<!-- Admin Scripts -->
<script src="/assets/js/router-guard.js" defer></script>
<script src="/assets/js/qa-mode.js" defer></script>
<script src="/assets/js/interface-menu.js"></script>
<script src="/assets/js/password-toggle.js"></script>
</body>

</html>
