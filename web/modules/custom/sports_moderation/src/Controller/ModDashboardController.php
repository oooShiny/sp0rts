<?php

namespace Drupal\sports_moderation\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\group\Entity\Group;

/**
 * Display a dashboard to moderators.
 */
class ModDashboardController extends ControllerBase {

  function build() {
    // Check if current user is group moderator.
    $groups = \Drupal::service('group.membership_loader')->loadByUser();
    $mod_groups = [];
    if ($groups) {
      // Check all user groups to see if they're a mod of any of them.
      foreach ($groups as $membership) {
        $gid = $membership->getGroup()->id();
        $group = Group::load($gid);
        $mod_roles = [
          $group->bundle() . '-moderator',
          $group->bundle() . '-admin'
        ];
        // Get this group's moderators/admins.
        $mods = $group->getMembers($mod_roles);
        foreach ($mods as $mod) {
          $id = $mod->getUser()->id();
          // If the mod ID matches the current user, add this group to the list.
          if ($id == $this->currentUser()->id()) {
            $mod_groups[$group->id()] = $group->label();
          }
        }
      }
    }
    // Send all group IDs that the user moderates to build views in twig.
    return [
      '#theme' => 'sports_moderation_dashboard',
      '#groups' => $mod_groups
    ];
  }
}
