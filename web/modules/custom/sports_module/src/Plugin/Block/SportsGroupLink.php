<?php

namespace Drupal\sports_module\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\group\Entity\Group;
use Drupal\group\Entity\GroupRelationship;

/**
 * Provides a block that displays a link on content to the associated Group.
 *
 * @Block(
 *   id = "sports_group_link",
 *   admin_label = @Translation("Sports Group Link"),
 *   category = @Translation("Sports"),
 *   context_definitions = {
 *     "node" = @ContextDefinition("entity:node", label = @Translation("Node"))
 *   } * )
 */
class SportsGroupLink extends BlockBase {

    /**
     * {@inheritdoc}
     */
    public function build() {
        $current_node = $this->getContextValue('node');

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

        return [
            '#theme' => 'sports_group_link',
            '#url' => '/group/'.$group_id,
            '#title' => $group_name
        ];
    }


}
