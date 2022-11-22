<?php

namespace Drupal\sports_module\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\group\Entity\Group;

/**
 * Provides a block that lists all Groups in a tree format.
 *
 * @Block(
 *   id = "sports_group_tree",
 *   admin_label = @Translation("Sports Groups Tree List"),
 *   category = @Translation("Sports"),
 * )
 */
class SportsGroupTree extends BlockBase {

    /**
     * {@inheritdoc}
     */
    public function build() {

        $ids = \Drupal::entityQuery('group')
            ->execute();

        $group_objects = Group::loadMultiple($ids);

        $groups = [];
        foreach ($group_objects as $g) {
            switch ($g->getGroupType()->id()) {
                case 'sport':
                    $groups[strtolower($g->label())] = [
                        'id' => $g->id(),
                        'leagues' => []
                    ];
                    break;
                case 'league':
                    $sport = strtolower($g->get('field_parent_sport')->referencedEntities()[0]->label());
                    $groups[$sport]['leagues'][$g->label()] = [
                        'id' => $g->id(),
                        'teams' => []
                    ];
                    break;
                case 'team':
                    $league = $g->get('field_parent_league')->referencedEntities()[0];
                    $sport = strtolower($league->get('field_parent_sport')->referencedEntities()[0]->label());
                    $groups[$sport]['leagues'][$league->label()]['teams'][$g->label()] = [
                        'id' => $g->id(),
                    ];
                    break;
            }
        }


        return [
            '#theme' => 'sports_group_tree',
            '#groups' => $groups
        ];
    }


}
