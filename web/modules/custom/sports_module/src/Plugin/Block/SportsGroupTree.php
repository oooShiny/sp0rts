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

    $groups = [];

    // Get all sports groups.
    $sport_ids = \Drupal::entityQuery('group')
      ->condition('type', 'sport')
      ->execute();

    $sports = Group::loadMultiple($sport_ids);
    foreach ($sports as $g) {
      $alias = \Drupal::service('path_alias.manager')->getAliasByPath('/group/'.$g->id());
      $groups[strtolower($g->label())] = [
        'url' => $alias,
        'leagues' => [],
      ];
    }

    // Get all league groups.
    $league_ids = \Drupal::entityQuery('group')
      ->condition('type', 'league')
      ->execute();

    $leagues = Group::loadMultiple($league_ids);
    foreach ($leagues as $g) {
      $sport = strtolower($g->get('field_parent_sport')
        ->referencedEntities()[0]->label());
      $alias = \Drupal::service('path_alias.manager')->getAliasByPath('/group/'.$g->id());
      $groups[$sport]['leagues'][$g->label()] = [
        'url' => $alias,
        'teams' => [],
      ];
    }

    // Get all team groups.
    $team_ids = \Drupal::entityQuery('group')
      ->condition('type', 'team')
      ->execute();

    $teams = Group::loadMultiple($team_ids);
    foreach ($teams as $g) {
      $alias = \Drupal::service('path_alias.manager')->getAliasByPath('/group/'.$g->id());
      $league = $g->get('field_parent_league')->referencedEntities()[0];
      $sport = strtolower($league->get('field_parent_sport')
        ->referencedEntities()[0]->label());
      $groups[$sport]['leagues'][$league->label()]['teams'][$g->label()] = [
        'url' => $alias,
      ];
    }


    return [
      '#theme' => 'sports_group_tree',
      '#groups' => $groups,
    ];
  }


}
