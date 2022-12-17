<?php

namespace Drupal\sports_module\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a block that displays a button to apply for being a moderator.
 *
 * @Block(
 *   id = "sports_group_mod_list_apply",
 *   admin_label = @Translation("Sports Group Mod List and Apply Button"),
 *   category = @Translation("Sports"),
 *   context_definitions = {
 *     "group" = @ContextDefinition("entity:group")
 *   } * )
 */
class SportsGroupModListApply extends BlockBase {

    /**
     * {@inheritdoc}
     */
    public function build() {
      $group = $this->getContextValue('group');
      $type = $group->getGroupType()->id();
      $mod_role = $type . '-moderator';
      $mods = [];
      foreach ($group->getMembers($mod_role) as $mod) {
        $user = $mod->getUser();
        $mods[$user->id()] = $user->label();
      }

      return [
          '#theme' => 'sports_group_mod_apply',
          '#mods' => $mods,
          '#url' => '/form/moderator-application?requested_group='.$this->getContextValue('group')->id(),
      ];
    }


}
