<?php

namespace Drupal\sports_users\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\group\Entity\Group;
use Drupal\image\Entity\ImageStyle;

/**
 * Provides a block that displays a link on content to the associated Group.
 *
 * @Block(
 *   id = "welcome_group_select_block",
 *   admin_label = @Translation("Welcome Page Group Selection Block"),
 *   category = @Translation("Sports"),
 *   context_definitions = {
 *     "node" = @ContextDefinition("entity:node", label = @Translation("Node"))
 *   } * )
 */
class WelcomeGroupSelectBlock extends BlockBase {

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
      if ($g->field_logo->isEmpty()) {
        $img = '';
      }
      else {
        $img = ImageStyle::load('square_100x100')
          ->buildUrl($g->field_logo->entity->getFileUri());
      }
      $groups[strtolower($g->label())] = [
        'img' => $img,
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
      if ($g->field_logo->isEmpty()) {
        $img = '';
      }
      else {
        $img = ImageStyle::load('square_100x100')
          ->buildUrl($g->field_logo->entity->getFileUri());
      }
      $groups[$sport]['leagues'][$g->label()] = [
        'img' => $img,
        'teams' => [],
      ];
    }

    // Get all team groups.
    $team_ids = \Drupal::entityQuery('group')
      ->condition('type', 'team')
      ->execute();

    $teams = Group::loadMultiple($team_ids);
    foreach ($teams as $g) {
      if ($g->field_logo->isEmpty()) {
        $img = '';
      }
      else {
        $img = ImageStyle::load('square_100x100')
          ->buildUrl($g->field_logo->entity->getFileUri());
      }
      $league = $g->get('field_parent_league')->referencedEntities()[0];
      $sport = strtolower($league->get('field_parent_sport')
        ->referencedEntities()[0]->label());
      $groups[$sport]['leagues'][$league->label()]['teams'][$g->label()] = [
        'img' => $img,
      ];
    }
    return [
      '#theme' => 'welcome_group_select_block',
      '#groups' => $groups,
      '#cache' => [
        'contexts' => ['user'],
        'tags' => ['user:' . \Drupal::currentUser()->id()],
      ],
    ];
  }


}
