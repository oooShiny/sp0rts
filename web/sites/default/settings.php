<?php

$conf['nodejs_service_key'] = 'heyHowAreYa';

// Pusher API settings.
$settings['pusher_api']['api_settings']['app_id'] = "1527192";
$settings['pusher_api']['api_settings']['key'] = "c428b53f1cbc41e05ffe";
$settings['pusher_api']['api_settings']['secret'] = "e75abd4a46bb4b293244";
$settings['pusher_api']['api_settings']['cluster'] = "us2";

$schemes = [
  'bunnycdn' => [
    'driver' => 'bunnycdn',
    'config' => [
      'token' => '#my-dropbox-token#',
      'client_id' => '#my-dropbox-email-id#',
    ],
  ],
];
$settings['flysystem'] = $schemes;

/**
 * Load services definition file.
 */
$settings['container_yamls'][] = __DIR__ . '/services.yml';

/**
 * Include the Pantheon-specific settings file.
 *
 * n.b. The settings.pantheon.php file makes some changes
 *      that affect all environments that this site
 *      exists in.  Always include this file, even in
 *      a local development environment, to ensure that
 *      the site settings remain consistent.
 */
include __DIR__ . "/settings.pantheon.php";

/**
 * Skipping permissions hardening will make scaffolding
 * work better, but will also raise a warning when you
 * install Drupal.
 *
 * https://www.drupal.org/project/drupal/issues/3091285
 */
// $settings['skip_permissions_hardening'] = TRUE;

/**
 * If there is a local settings file, then include it
 */
$local_settings = __DIR__ . "/settings.local.php";
if (file_exists($local_settings)) {
  include $local_settings;
}

// Automatically generated include for settings managed by ddev.
$ddev_settings = dirname(__FILE__) . '/settings.ddev.php';
if (getenv('IS_DDEV_PROJECT') == 'true' && is_readable($ddev_settings)) {
  require $ddev_settings;
}
