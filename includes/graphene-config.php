<?php

/**
 * Graphene Theme Configuration System
 *
 * This file provides functions to read/write Graphene theme settings.
 * Settings are stored in a JSON file for simplicity and portability.
 *
 * @package BlackboxEYE
 * @subpackage Graphene
 */

defined('BBX_GRAPHENE_CONFIG') || define('BBX_GRAPHENE_CONFIG', __DIR__ . '/../config/graphene-settings.json');

/**
 * Default Graphene theme settings
 */
function bbx_graphene_defaults(): array
{
  return [
    'theme_mode' => 'standard', // 'standard' or 'strong'
    'colors' => [
      'primary' => '#1a73e8',
      'secondary' => '#113c55',
      'gold' => '#c9a227',
      'accent' => '#e94560',
      'bg_dark' => '#0A1217',
      'bg_light' => '#F5F8FA',
      'text' => '#E0E6EB',
      'text_light' => '#22313A',
    ],
    'effects' => [
      'pulse_intensity' => 'normal', // 'subtle', 'normal', 'intense'
      'glow_enabled' => true,
      'gradient_angle' => 135,
      'animation_speed' => 'normal', // 'slow', 'normal', 'fast'
    ],
    'updated_at' => null,
    'updated_by' => null,
  ];
}

/**
 * Strong mode preset - intensified colors and effects
 */
function bbx_graphene_strong_preset(): array
{
  return [
    'theme_mode' => 'strong',
    'colors' => [
      'primary' => '#2196F3',
      'secondary' => '#0D47A1',
      'gold' => '#9a7b1f',
      'accent' => '#FF1744',
      'bg_dark' => '#050A0D',
      'bg_light' => '#FFFFFF',
      'text' => '#FFFFFF',
      'text_light' => '#1A1A2E',
    ],
    'effects' => [
      'pulse_intensity' => 'intense',
      'glow_enabled' => true,
      'gradient_angle' => 135,
      'animation_speed' => 'fast',
    ],
  ];
}

/**
 * Load Graphene settings from config file
 *
 * @return array Current settings merged with defaults
 */
function bbx_graphene_load_settings(): array
{
  $defaults = bbx_graphene_defaults();

  if (!file_exists(BBX_GRAPHENE_CONFIG)) {
    return $defaults;
  }

  $json = file_get_contents(BBX_GRAPHENE_CONFIG);
  if ($json === false) {
    return $defaults;
  }

  $settings = json_decode($json, true);
  if (!is_array($settings)) {
    return $defaults;
  }

  // Deep merge with defaults
  return array_replace_recursive($defaults, $settings);
}

/**
 * Save Graphene settings to config file
 *
 * @param array $settings Settings to save
 * @param string|null $updated_by Optional user identifier
 * @return bool Success status
 */
function bbx_graphene_save_settings(array $settings, ?string $updated_by = null): bool
{
  // Ensure config directory exists
  $config_dir = dirname(BBX_GRAPHENE_CONFIG);
  if (!is_dir($config_dir)) {
    if (!mkdir($config_dir, 0755, true)) {
      error_log('Graphene Config: Failed to create config directory');
      return false;
    }
  }

  // Merge with defaults to ensure all keys exist
  $defaults = bbx_graphene_defaults();
  $settings = array_replace_recursive($defaults, $settings);

  // Add metadata
  $settings['updated_at'] = date('c');
  $settings['updated_by'] = $updated_by;

  // Save to file
  $json = json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
  $result = file_put_contents(BBX_GRAPHENE_CONFIG, $json, LOCK_EX);

  if ($result === false) {
    error_log('Graphene Config: Failed to save settings');
    return false;
  }

  return true;
}

/**
 * Get current theme mode
 *
 * @return string 'standard' or 'strong'
 */
function bbx_graphene_get_mode(): string
{
  $settings = bbx_graphene_load_settings();
  return $settings['theme_mode'] ?? 'standard';
}

/**
 * Set theme mode and apply preset if switching to strong
 *
 * @param string $mode 'standard' or 'strong'
 * @param string|null $updated_by Optional user identifier
 * @return bool Success status
 */
function bbx_graphene_set_mode(string $mode, ?string $updated_by = null): bool
{
  $settings = bbx_graphene_load_settings();

  if ($mode === 'strong') {
    // Apply strong preset
    $strong = bbx_graphene_strong_preset();
    $settings = array_replace_recursive($settings, $strong);
  } else {
    // Reset to standard defaults
    $defaults = bbx_graphene_defaults();
    $settings['theme_mode'] = 'standard';
    $settings['colors'] = $defaults['colors'];
    $settings['effects'] = $defaults['effects'];
  }

  return bbx_graphene_save_settings($settings, $updated_by);
}

/**
 * Get CSS custom properties for current Graphene settings
 *
 * @return string CSS custom properties block
 */
function bbx_graphene_css_vars(): string
{
  $settings = bbx_graphene_load_settings();
  $colors = $settings['colors'];
  $effects = $settings['effects'];

  // Animation speed mapping
  $speeds = [
    'slow' => '3s',
    'normal' => '2s',
    'fast' => '1s',
  ];
  $anim_speed = $speeds[$effects['animation_speed']] ?? '2s';

  // Pulse intensity mapping
  $pulse_scales = [
    'subtle' => '0.3',
    'normal' => '0.6',
    'intense' => '1',
  ];
  $pulse_scale = $pulse_scales[$effects['pulse_intensity']] ?? '0.6';

  $css = ":root {\n";
  $css .= "  --graphene-primary: {$colors['primary']};\n";
  $css .= "  --graphene-secondary: {$colors['secondary']};\n";
  $css .= "  --graphene-gold: {$colors['gold']};\n";
  $css .= "  --graphene-accent: {$colors['accent']};\n";
  $css .= "  --graphene-bg-dark: {$colors['bg_dark']};\n";
  $css .= "  --graphene-bg-light: {$colors['bg_light']};\n";
  $css .= "  --graphene-text: {$colors['text']};\n";
  $css .= "  --graphene-text-light: {$colors['text_light']};\n";
  $css .= "  --graphene-pulse-speed: {$anim_speed};\n";
  $css .= "  --graphene-pulse-scale: {$pulse_scale};\n";
  $css .= "  --graphene-gradient-angle: {$effects['gradient_angle']}deg;\n";
  $css .= "}\n";

  return $css;
}

/**
 * Get body class for current Graphene mode
 *
 * @return string CSS class name
 */
function bbx_graphene_body_class(): string
{
  $mode = bbx_graphene_get_mode();
  return $mode === 'strong' ? 'graphene-strong' : 'graphene-standard';
}
