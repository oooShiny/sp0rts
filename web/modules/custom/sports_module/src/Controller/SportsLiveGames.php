<?php

namespace Drupal\sports_module\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\file\Entity\File;
use Drupal\group\Entity\Group;
use Drupal\group\Entity\GroupRelationship;
use Drupal\views\Views;

/**
 * Display a grid of cards for games happening today.
 */
class SportsLiveGames extends ControllerBase {

  function build() {
    $games = [];
    $view = Views::getview('live_games');
    if ($view) {
      $view->execute();
      foreach ($view->result as $rid => $row) {
        $game_node = $row->_entity;
        $espn_id = $game_node->get('field_espn_id')->value;

        if (empty($games[$espn_id]['data'])) {
          $date = new DrupalDateTime($game_node->field_game_date->value, 'UTC');
          $timestamp = $date->getTimestamp();
          $formatted_date = \Drupal::service('date.formatter')->format($timestamp, 'medium');
          $games[$espn_id]['data'] = [
            'team_1_name' => $game_node->field_team_1->value,
            'team_1_record' => $game_node->field_team_1_record->value,
            'team_1_logo' => $game_node->field_team_1_logo->value,
            'team_2_name' => $game_node->field_team_2->value,
            'team_2_record' => $game_node->field_team_2_record->value,
            'team_2_logo' => $game_node->field_team_2_logo->value,
            'venue' => $game_node->field_venue->value,
            'date' => $formatted_date
          ];
        }
        $group_contents = GroupRelationship::loadByEntity($game_node);
        if ($group_contents) {
          foreach ($group_contents as $group_content) {
            $group = Group::load($group_content->getGroup()->id());
            $uri = File::load($group->get('field_logo')->target_id)->getFileUri();
            $games[$espn_id][] = [
              'group_name' => $group->label(),
              'group_id' => $group->id(),
              'group_logo' => \Drupal::service('file_url_generator')->generateAbsoluteString($uri)
            ];
          }
        }
      }
    }

    return [
      '#theme' => 'sports_live_games',
      '#games' => $games,
    ];
  }
}
