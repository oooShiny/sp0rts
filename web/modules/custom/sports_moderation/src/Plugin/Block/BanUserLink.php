<?php

namespace Drupal\sports_moderation\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\group\Entity\Group;
use Drupal\group\Entity\GroupRelationship;

/**
 * Provides a block that displays a link to allow moderators to block users.
 *
 * @Block(
 *   id = "sports_moderation_block_link",
 *   admin_label = @Translation("Sports Moderation Block Link"),
 *   category = @Translation("Sports"),
 *   context_definitions = {
 *     "node" = @ContextDefinition("entity:node", label = @Translation("Node"))
 *   } * )
 */
class BanUserLink extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $current_node = $this->getContextValue('node');
    $user_id = $current_node->getOwnerId();

    $group_contents = GroupRelationship::loadByEntity($current_node);
    if ($group_contents) {
      foreach ($group_contents as $group_content) {
        $group_id = $group_content->getGroup()->id();
        $group_name = Group::load($group_id)->label();
      }
    }
    else {
      $group_id = NULL;
      $group_name = NULL;
    }
    $ban_url = '/ban/' . $user_id . '/group/' . $group_id;
    return [
      '#theme' => 'sports_moderation_block_link',
      '#url' => $ban_url,
      '#title' => $group_name
    ];
  }


}
