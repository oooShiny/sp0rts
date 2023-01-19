<?php

namespace Drupal\sports_moderation\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\group\Entity\Group;
use Drupal\user\Entity\User;

/**
 * A controller that performs a ban on a user.
 */
class BanController extends ControllerBase {

  function ban_user(AccountInterface $user, Group $group) {
    $moderator = User::load(\Drupal::currentUser()->id());
    $text = 'You don\'t have permissions to ban the user ' . $user->getDisplayName() . ' from the ' . $group->label() . ' group.';
    foreach ($group->getMember($moderator)->getRoles() as $group_role) {
      if ($group_role->label() == 'Moderator' && $group_role->status() === TRUE) {
        $user->field_group_post_ban[] = ['target_id' => $group->id()];
        $user->field_group_comment_ban[] = ['target_id' => $group->id()];
        $user->save();
        $text = 'User ' . $user->getDisplayName() . ' has been banned from the ' . $group->label() . ' group.';
      }
    }

    return [
      '#markup' => $this->t($text),
    ];
  }
}
