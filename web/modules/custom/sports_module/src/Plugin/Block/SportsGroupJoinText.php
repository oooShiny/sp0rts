<?php

namespace Drupal\sports_module\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\group\Entity\Group;

/**
 * Provides a block that displays a button to apply for being a moderator.
 *
 * @Block(
 *   id = "sports_group_mod_apply",
 *   admin_label = @Translation("Sports Group Mod Apply Button"),
 *   category = @Translation("Sports"),
 *   context_definitions = {
 *     "group" = @ContextDefinition("entity:group")
 *   } * )
 */
class SportsGroupModApply extends BlockBase {

    /**
     * {@inheritdoc}
     */
    public function build() {

        return [
            '#theme' => 'sports_group_mod_apply',
            '#url' => '/form/moderator-application?requested_group='.$this->getContextValue('group')->id(),
        ];
    }


}
