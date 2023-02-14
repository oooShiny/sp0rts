<?php

namespace Drupal\sports_moderation\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\group\Entity\Group;
use Drupal\user\Entity\User;

/**
 * Display a dashboard to moderators.
 */
class ModDashboardController extends ControllerBase {

  function build() {
    $text = 'Welcome to your Moderator Dashboard.';
    return [
      '#markup' => $this->t($text),
    ];
  }
}
