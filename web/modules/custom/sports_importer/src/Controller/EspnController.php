<?php

namespace Drupal\sports_importer\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\group\Entity\Group;
use Drupal\node\Entity\Node;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

/**
 * An example controller.
 */
class EspnController extends ControllerBase {

  /**
   * Builds new nodes based on daily game data from ESPN.
   */
  public function get_data($sport = 'football', $league = 'nfl', $team_count = '32') {
    $client = new Client();
    $games = [];

    try {
      // Get all games from the scoreboard.
      $scoreboard = $client->get('https://site.api.espn.com/apis/site/v2/sports/'.$sport.'/'.$league.'/scoreboard');
      $scoreboard_json = json_decode($scoreboard->getBody(), TRUE);

      // Check all games from ESPN scoreboard to see if there are any games today.
      $today = date_create(date('c', time()));
      foreach ($scoreboard_json['events'] as $g) {
        $game_date = date_create($g['date']);
        $diff = date_diff($today, $game_date);
        $days_apart = $diff->format('%R%a days');
        // If there are games, run the import.
        if ($days_apart == '+0 days' || $days_apart == '-0 days') {

          $team1 = [
            'name' => $g['competitions'][0]['competitors'][0]['team']['displayName'],
            'record' => $g['competitions'][0]['competitors'][0]['records'][0]['summary'],
            'logo' => $g['competitions'][0]['competitors'][0]['team']['logo']
          ];
          $team2 = [
            'name' => $g['competitions'][0]['competitors'][1]['team']['displayName'],
            'record' => $g['competitions'][0]['competitors'][1]['records'][0]['summary'],
            'logo' => $g['competitions'][0]['competitors'][1]['team']['logo']
          ];

          // Create the title text for the node.
          $week = 'Week ' . $g['week']['number'];
          $game_title = $g['season']['year'] . ' ' . $week . ': ' . $g['name'];
          if (isset($g['competitions'][0]['odds'])) {
            $odds = explode(' ', $g['competitions'][0]['odds'][0]['details']);
          }
          else {
            $odds[] = '';
          }

          if ($g["competitions"][0]["competitors"][0]["team"]["abbreviation"] == $odds[0]) {
            $favorite = $team1['name'];
          }
          elseif ($g["competitions"][0]["competitors"][1]["team"]["abbreviation"] == $odds[0]) {
            $favorite = $team2['name'];
          }
          else {
            $favorite = '';
          }
          // Check to see if there's any content with this title already.
          $nodes = \Drupal::entityTypeManager()
            ->getStorage('node')
            ->loadByProperties(['title' => $game_title]);

          // If the nodes haven't been created, create 'em.
          if (empty($nodes)) {
            // Add the node to the group.
            $query = \Drupal::entityQuery('group');
            $group = $query
              ->orConditionGroup()
              ->condition('label', $team1['name'])
              ->condition('label', $team2['name']);
            $ids = $query->condition($group)
              ->execute();
            $groups = Group::loadMultiple($ids);

            // Create body text for node.
            $text = $team1['name'] . '('.$team1['record'].') : ' . $team2['name'] . '('.$team2['record'].')';

            foreach ($groups as $gr) {
              foreach ($gr->get('field_parent_sport')->referencedEntities() as $sport) {
                $game_sport = strtolower($sport->label());
              }
              foreach ($gr->get('field_parent_league')->referencedEntities() as $league) {
                $game_league = strtolower($league->label());
              }

              $endpoint = 'https://sports.core.api.espn.com/v2/sports/'.$game_sport.'/leagues/'.$game_league.'/events/'.$g['id'].'/competitions/'.$g['id'].'/plays?limit=400';

              $node = Node::create([
                'type' => 'game',
                'title' => $game_title,
                'uid' => 1,
                'body' => [
                  'summary' => '',
                  'value' => '<p>' . $text . '</p>',
                  'format' => 'full_html',
                ],
                'field_espn_endpoint' => $endpoint,
                'field_espn_id' => $g['id'],
                'field_team_1' => $team1['name'],
                'field_team_1_logo' => $team1['logo'],
                'field_team_1_record' => $team1['record'],
                'field_team_2' => $team2['name'],
                'field_team_2_logo' => $team2['logo'],
                'field_team_2_record' => $team2['record'],
                'field_favorite' => $favorite,
                'field_line' => end($odds),
                'field_weather' => $g['weather']['displayValue'],
                'field_temperature' => $g['weather']['temperature'],
                'field_venue' => $g['competitions'][0]['venue']['fullName'],
                'field_game_date' => $game_date->format('Y-m-d\TH:i:s'),
                'publish_on' => $game_date->format('U') - 3600,
              ]);

              $node->enforceIsNew();
              $node->save();
              $games[] = [
                'title' => $node->label(),
                'url' => $node->toUrl()->toString(),
                'group' => $gr->label()
              ];
              $relation = $gr->getRelationshipsByEntity($node, 'group_node:game');
              if (!$relation) {
                $gr->addRelationship($node, 'group_node:game');
              }
            }
          }
        }
      }

      return [
        '#theme' => 'sports_importer_admin',
        '#games' => $games,
      ];
    }
    catch (RequestException $e) {
      $e->getMessage();
    }
  }
}
