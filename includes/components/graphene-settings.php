<?php

/**
 * Graphene Settings Panel Component
 *
 * Central settings panel for configuring Graphene theme colors and effects.
 * Designed to be included in admin settings pages.
 *
 * @package BlackboxEYE
 * @subpackage Graphene
 */

require_once __DIR__ . '/../graphene-config.php';
require_once __DIR__ . '/../i18n.php';

// Load current settings
$graphene_settings = bbx_graphene_load_settings();
$current_mode = $graphene_settings['theme_mode'];
$colors = $graphene_settings['colors'];
$effects = $graphene_settings['effects'];

// Handle form submission
$save_message = '';
$save_status = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['graphene_save'])) {
  // Verify nonce if available
  $submitted_mode = $_POST['theme_mode'] ?? 'standard';
  $submitted_colors = [
    'primary' => $_POST['color_primary'] ?? $colors['primary'],
    'secondary' => $_POST['color_secondary'] ?? $colors['secondary'],
    'gold' => $_POST['color_gold'] ?? $colors['gold'],
    'accent' => $_POST['color_accent'] ?? $colors['accent'],
    'bg_dark' => $_POST['color_bg_dark'] ?? $colors['bg_dark'],
    'bg_light' => $_POST['color_bg_light'] ?? $colors['bg_light'],
    'text' => $_POST['color_text'] ?? $colors['text'],
    'text_light' => $_POST['color_text_light'] ?? $colors['text_light'],
  ];
  $submitted_effects = [
    'pulse_intensity' => $_POST['pulse_intensity'] ?? $effects['pulse_intensity'],
    'glow_enabled' => isset($_POST['glow_enabled']),
    'gradient_angle' => intval($_POST['gradient_angle'] ?? $effects['gradient_angle']),
    'animation_speed' => $_POST['animation_speed'] ?? $effects['animation_speed'],
  ];

  // If mode changed to preset, apply preset colors
  if ($submitted_mode === 'strong' && $current_mode !== 'strong') {
    $strong_preset = bbx_graphene_strong_preset();
    $submitted_colors = $strong_preset['colors'];
    $submitted_effects = array_merge($submitted_effects, $strong_preset['effects']);
  } elseif ($submitted_mode === 'standard' && $current_mode !== 'standard') {
    $standard_preset = bbx_graphene_defaults();
    $submitted_colors = $standard_preset['colors'];
    $submitted_effects = array_merge($submitted_effects, $standard_preset['effects']);
  }

  $new_settings = [
    'theme_mode' => $submitted_mode,
    'colors' => $submitted_colors,
    'effects' => $submitted_effects,
  ];

  $agent_id = $_SESSION['agent_id'] ?? 'anonymous';
  if (bbx_graphene_save_settings($new_settings, $agent_id)) {
    $save_message = t('settings.graphene.save_success');
    $save_status = 'success';
    // Reload settings after save
    $graphene_settings = bbx_graphene_load_settings();
    $current_mode = $graphene_settings['theme_mode'];
    $colors = $graphene_settings['colors'];
    $effects = $graphene_settings['effects'];
  } else {
    $save_message = t('settings.graphene.save_error');
    $save_status = 'error';
  }
}
?>

<div class="graphene-settings-panel graphene-section" id="graphene-settings">
  <?php if ($save_message): ?>
    <div class="graphene-alert graphene-alert--<?= htmlspecialchars($save_status) ?>" role="alert">
      <svg class="graphene-alert__icon" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
        <?php if ($save_status === 'success'): ?>
          <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z" />
        <?php else: ?>
          <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z" />
        <?php endif; ?>
      </svg>
      <span><?= htmlspecialchars($save_message) ?></span>
    </div>
  <?php endif; ?>

  <form method="POST" class="graphene-settings-form" id="graphene-settings-form">
    <!-- Theme Mode Selection -->
    <fieldset class="graphene-fieldset">
      <legend class="graphene-legend">
        <svg class="graphene-legend__icon" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
          <path d="M12 3c-4.97 0-9 4.03-9 9s4.03 9 9 9c.83 0 1.5-.67 1.5-1.5 0-.39-.15-.74-.39-1.01-.23-.26-.38-.61-.38-.99 0-.83.67-1.5 1.5-1.5H16c2.76 0 5-2.24 5-5 0-4.42-4.03-8-9-8zm-5.5 9c-.83 0-1.5-.67-1.5-1.5S5.67 9 6.5 9 8 9.67 8 10.5 7.33 12 6.5 12zm3-4C8.67 8 8 7.33 8 6.5S8.67 5 9.5 5s1.5.67 1.5 1.5S10.33 8 9.5 8zm5 0c-.83 0-1.5-.67-1.5-1.5S13.67 5 14.5 5s1.5.67 1.5 1.5S15.33 8 14.5 8zm3 4c-.83 0-1.5-.67-1.5-1.5S16.67 9 17.5 9s1.5.67 1.5 1.5-.67 1.5-1.5 1.5z" />
        </svg>
        <?= t('settings.graphene.theme_mode') ?>
      </legend>

      <div class="graphene-mode-selector">
        <label class="graphene-mode-option <?= $current_mode === 'standard' ? 'is-selected' : '' ?>">
          <input type="radio" name="theme_mode" value="standard" <?= $current_mode === 'standard' ? 'checked' : '' ?>>
          <div class="graphene-mode-card">
            <div class="graphene-mode-preview graphene-mode-preview--standard">
              <div class="preview-hex"></div>
              <div class="preview-hex"></div>
              <div class="preview-hex"></div>
            </div>
            <div class="graphene-mode-info">
              <span class="graphene-mode-title"><?= t('settings.graphene.mode_standard') ?></span>
              <span class="graphene-mode-desc"><?= t('settings.graphene.mode_standard_desc') ?></span>
            </div>
          </div>
        </label>

        <label class="graphene-mode-option <?= $current_mode === 'strong' ? 'is-selected' : '' ?>">
          <input type="radio" name="theme_mode" value="strong" <?= $current_mode === 'strong' ? 'checked' : '' ?>>
          <div class="graphene-mode-card">
            <div class="graphene-mode-preview graphene-mode-preview--strong">
              <div class="preview-hex"></div>
              <div class="preview-hex"></div>
              <div class="preview-hex"></div>
            </div>
            <div class="graphene-mode-info">
              <span class="graphene-mode-title"><?= t('settings.graphene.mode_strong') ?></span>
              <span class="graphene-mode-desc"><?= t('settings.graphene.mode_strong_desc') ?></span>
            </div>
          </div>
        </label>
      </div>
    </fieldset>

    <!-- Color Settings -->
    <fieldset class="graphene-fieldset">
      <legend class="graphene-legend">
        <svg class="graphene-legend__icon" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
          <path d="M12 22C6.49 22 2 17.51 2 12S6.49 2 12 2s10 4.04 10 9c0 3.31-2.69 6-6 6h-1.77c-.28 0-.5.22-.5.5 0 .12.05.23.13.33.41.47.64 1.06.64 1.67A2.5 2.5 0 0 1 12 22zm0-18c-4.41 0-8 3.59-8 8s3.59 8 8 8c.28 0 .5-.22.5-.5a.54.54 0 0 0-.14-.35c-.41-.46-.63-1.05-.63-1.65a2.5 2.5 0 0 1 2.5-2.5H16c2.21 0 4-1.79 4-4 0-3.86-3.59-7-8-7z" />
        </svg>
        <?= t('settings.graphene.colors') ?>
      </legend>

      <div class="graphene-color-grid">
        <div class="graphene-color-field">
          <label for="color_primary"><?= t('settings.graphene.color_primary') ?></label>
          <div class="graphene-color-input">
            <input type="color" id="color_primary" name="color_primary" value="<?= htmlspecialchars($colors['primary']) ?>">
            <input type="text" value="<?= htmlspecialchars($colors['primary']) ?>" data-color-text="color_primary" readonly>
          </div>
        </div>

        <div class="graphene-color-field">
          <label for="color_secondary"><?= t('settings.graphene.color_secondary') ?></label>
          <div class="graphene-color-input">
            <input type="color" id="color_secondary" name="color_secondary" value="<?= htmlspecialchars($colors['secondary']) ?>">
            <input type="text" value="<?= htmlspecialchars($colors['secondary']) ?>" data-color-text="color_secondary" readonly>
          </div>
        </div>

        <div class="graphene-color-field">
          <label for="color_gold"><?= t('settings.graphene.color_gold') ?></label>
          <div class="graphene-color-input">
            <input type="color" id="color_gold" name="color_gold" value="<?= htmlspecialchars($colors['gold']) ?>">
            <input type="text" value="<?= htmlspecialchars($colors['gold']) ?>" data-color-text="color_gold" readonly>
          </div>
        </div>

        <div class="graphene-color-field">
          <label for="color_accent"><?= t('settings.graphene.color_accent') ?></label>
          <div class="graphene-color-input">
            <input type="color" id="color_accent" name="color_accent" value="<?= htmlspecialchars($colors['accent']) ?>">
            <input type="text" value="<?= htmlspecialchars($colors['accent']) ?>" data-color-text="color_accent" readonly>
          </div>
        </div>

        <div class="graphene-color-field">
          <label for="color_bg_dark"><?= t('settings.graphene.color_bg_dark') ?></label>
          <div class="graphene-color-input">
            <input type="color" id="color_bg_dark" name="color_bg_dark" value="<?= htmlspecialchars($colors['bg_dark']) ?>">
            <input type="text" value="<?= htmlspecialchars($colors['bg_dark']) ?>" data-color-text="color_bg_dark" readonly>
          </div>
        </div>

        <div class="graphene-color-field">
          <label for="color_bg_light"><?= t('settings.graphene.color_bg_light') ?></label>
          <div class="graphene-color-input">
            <input type="color" id="color_bg_light" name="color_bg_light" value="<?= htmlspecialchars($colors['bg_light']) ?>">
            <input type="text" value="<?= htmlspecialchars($colors['bg_light']) ?>" data-color-text="color_bg_light" readonly>
          </div>
        </div>
      </div>
    </fieldset>

    <!-- Effect Settings -->
    <fieldset class="graphene-fieldset">
      <legend class="graphene-legend">
        <svg class="graphene-legend__icon" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
          <path d="M7 2v11h3v9l7-12h-4l4-8z" />
        </svg>
        <?= t('settings.graphene.effects') ?>
      </legend>

      <div class="graphene-effects-grid">
        <div class="graphene-select-field">
          <label for="pulse_intensity"><?= t('settings.graphene.pulse_intensity') ?></label>
          <select id="pulse_intensity" name="pulse_intensity">
            <option value="subtle" <?= $effects['pulse_intensity'] === 'subtle' ? 'selected' : '' ?>><?= t('settings.graphene.intensity_subtle') ?></option>
            <option value="normal" <?= $effects['pulse_intensity'] === 'normal' ? 'selected' : '' ?>><?= t('settings.graphene.intensity_normal') ?></option>
            <option value="intense" <?= $effects['pulse_intensity'] === 'intense' ? 'selected' : '' ?>><?= t('settings.graphene.intensity_intense') ?></option>
          </select>
        </div>

        <div class="graphene-select-field">
          <label for="animation_speed"><?= t('settings.graphene.animation_speed') ?></label>
          <select id="animation_speed" name="animation_speed">
            <option value="slow" <?= $effects['animation_speed'] === 'slow' ? 'selected' : '' ?>><?= t('settings.graphene.speed_slow') ?></option>
            <option value="normal" <?= $effects['animation_speed'] === 'normal' ? 'selected' : '' ?>><?= t('settings.graphene.speed_normal') ?></option>
            <option value="fast" <?= $effects['animation_speed'] === 'fast' ? 'selected' : '' ?>><?= t('settings.graphene.speed_fast') ?></option>
          </select>
        </div>

        <div class="graphene-range-field">
          <label for="gradient_angle"><?= t('settings.graphene.gradient_angle') ?>: <span id="gradient_angle_value"><?= $effects['gradient_angle'] ?>°</span></label>
          <input type="range" id="gradient_angle" name="gradient_angle" min="0" max="360" value="<?= $effects['gradient_angle'] ?>">
        </div>

        <div class="graphene-toggle-field">
          <label class="graphene-toggle">
            <input type="checkbox" name="glow_enabled" <?= $effects['glow_enabled'] ? 'checked' : '' ?>>
            <span class="graphene-toggle__slider"></span>
            <span class="graphene-toggle__label"><?= t('settings.graphene.glow_enabled') ?></span>
          </label>
        </div>
      </div>
    </fieldset>

    <!-- Live Preview -->
    <fieldset class="graphene-fieldset">
      <legend class="graphene-legend">
        <svg class="graphene-legend__icon" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
          <path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z" />
        </svg>
        <?= t('settings.graphene.preview') ?>
      </legend>

      <div class="graphene-preview-container" id="graphene-preview">
        <div class="graphene-preview-hero">
          <div class="graphene-preview-badge">
            <span class="graphene-preview-pulse"></span>
            <span>Blackbox EYE™</span>
          </div>
          <div class="graphene-preview-title"><?= t('settings.graphene.preview_title') ?></div>
          <div class="graphene-preview-subtitle"><?= t('settings.graphene.preview_subtitle') ?></div>
          <div class="graphene-preview-buttons">
            <button type="button" class="graphene-preview-btn graphene-preview-btn--primary"><?= t('settings.graphene.preview_cta') ?></button>
            <button type="button" class="graphene-preview-btn graphene-preview-btn--secondary"><?= t('settings.graphene.preview_secondary') ?></button>
          </div>
        </div>
      </div>
    </fieldset>

    <!-- Submit Button -->
    <div class="graphene-form-actions">
      <button type="submit" name="graphene_save" class="btn-graphene-primary btn-graphene-lg">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
        </svg>
        <?= t('settings.graphene.save_button') ?>
      </button>
      <button type="button" class="btn-graphene-secondary" onclick="location.reload()">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
        </svg>
        <?= t('settings.graphene.reset_button') ?>
      </button>
    </div>
  </form>
</div>

<script>
  (function() {
    // Color input sync
    document.querySelectorAll('input[type="color"]').forEach(function(colorInput) {
      colorInput.addEventListener('input', function() {
        var textInput = document.querySelector('[data-color-text="' + this.id + '"]');
        if (textInput) {
          textInput.value = this.value;
        }
        updatePreview();
      });
    });

    // Gradient angle display
    var gradientSlider = document.getElementById('gradient_angle');
    var gradientValue = document.getElementById('gradient_angle_value');
    if (gradientSlider && gradientValue) {
      gradientSlider.addEventListener('input', function() {
        gradientValue.textContent = this.value + '°';
        updatePreview();
      });
    }

    // Mode selection styling
    document.querySelectorAll('input[name="theme_mode"]').forEach(function(radio) {
      radio.addEventListener('change', function() {
        document.querySelectorAll('.graphene-mode-option').forEach(function(opt) {
          opt.classList.remove('is-selected');
        });
        this.closest('.graphene-mode-option').classList.add('is-selected');

        // Apply preset colors when mode changes
        if (this.value === 'strong') {
          applyStrongPreset();
        } else {
          applyStandardPreset();
        }
      });
    });

    function applyStrongPreset() {
      var presets = {
        color_primary: '#2196F3',
        color_secondary: '#0D47A1',
        color_gold: '#FFD700',
        color_accent: '#FF1744',
        color_bg_dark: '#050A0D',
        color_bg_light: '#FFFFFF'
      };
      applyPreset(presets);
      document.getElementById('pulse_intensity').value = 'intense';
      document.getElementById('animation_speed').value = 'fast';
      updatePreview();
    }

    function applyStandardPreset() {
      var presets = {
        color_primary: '#1a73e8',
        color_secondary: '#113c55',
        color_gold: '#d4af37',
        color_accent: '#e94560',
        color_bg_dark: '#0A1217',
        color_bg_light: '#F5F8FA'
      };
      applyPreset(presets);
      document.getElementById('pulse_intensity').value = 'normal';
      document.getElementById('animation_speed').value = 'normal';
      updatePreview();
    }

    function applyPreset(presets) {
      Object.keys(presets).forEach(function(key) {
        var colorInput = document.getElementById(key);
        var textInput = document.querySelector('[data-color-text="' + key + '"]');
        if (colorInput) {
          colorInput.value = presets[key];
        }
        if (textInput) {
          textInput.value = presets[key];
        }
      });
    }

    function updatePreview() {
      var preview = document.getElementById('graphene-preview');
      if (!preview) return;

      var primary = document.getElementById('color_primary').value;
      var gold = document.getElementById('color_gold').value;
      var bgDark = document.getElementById('color_bg_dark').value;
      var gradientAngle = document.getElementById('gradient_angle').value;
      var pulseIntensity = document.getElementById('pulse_intensity').value;
      var animSpeed = document.getElementById('animation_speed').value;

      preview.style.setProperty('--preview-primary', primary);
      preview.style.setProperty('--preview-gold', gold);
      preview.style.setProperty('--preview-bg', bgDark);
      preview.style.setProperty('--preview-gradient-angle', gradientAngle + 'deg');

      var speedMap = {
        slow: '3s',
        normal: '2s',
        fast: '1s'
      };
      var scaleMap = {
        subtle: '0.3',
        normal: '0.6',
        intense: '1'
      };
      preview.style.setProperty('--preview-anim-speed', speedMap[animSpeed] || '2s');
      preview.style.setProperty('--preview-pulse-scale', scaleMap[pulseIntensity] || '0.6');
    }

    // Initial preview update
    updatePreview();
  })();
</script>
