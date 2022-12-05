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
    $teams = [];

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
            'record' => $g['competitions'][0]['competitors'][0]['records'][0]['summary']
          ];
          $team2 = [
            'name' => $g['competitions'][0]['competitors'][1]['team']['displayName'],
            'record' => $g['competitions'][0]['competitors'][1]['records'][0]['summary']
          ];

          // Create the title text for the node.
          $week = 'Week ' . $g['week']['number'];
          $game_title = $g['season']['year'] . ' ' . $week . ': ' . $g['name'];

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

              $node = Node::create([
                'type' => 'game',
                'title' => $game_title,
                'uid' => 1,
                'body' => [
                  'summary' => '',
                  'value' => '<p>' . $text . '</p>',
                  'format' => 'full_html',
                ],
                'field_team_1' => $team1['name'],
                'field_team_2' => $team2['name'],
                'field_game_date' => $game_date->format('Y-m-d\TH:i:s'),
                'publish_on' => $game_date->format('U') - 3600,
              ]);

              $node->enforceIsNew();
              $node->save();

              $relation = $gr->getContentByEntityId('group_node:game', $node->id());
              if (!$relation) {
                $gr->addContent($node, 'group_node:game');
              }
            }
          }
        }
      }

          $response = $client->get('https://sports.core.api.espn.com/v2/sports/'.$sport.'/leagues/'.$league.'/teams?limit='.$team_count);
          $result = json_decode($response->getBody(), TRUE);
          foreach($result['items'] as $item) {

            // Get initial team data.
            $team_res = $client->get($item['$ref']);
            $team_data = json_decode($team_res->getBody(), TRUE);

            // Get team record.
            $team_rec = $client->get($team_data['record']['$ref']);
            $team_record = json_decode($team_rec->getBody(), TRUE);

            // Get team schedule.
//            $team_sched = $client->get($team_data['events']['$ref']);
//            $team_sched_links = json_decode($team_sched->getBody(), TRUE);
//            $games = [];
//            foreach ($team_sched_links['items'] as $game_link) {
//              $team_g = $client->get($game_link['$ref']);
//              $team_game = json_decode($team_g->getBody(), TRUE);
//              $games[] = $team_game;
//            }
            // Create team array from all the fetches.
            $teams[$team_data['id']] = $team_data;
            $teams[$team_data['id']]['record'] = $team_record['items'];
//            $teams[$team_data['id']]['schedule'] = $games;

          return [
            '#theme' => 'sports_importer_admin',
            '#teams' => $teams,
          ];
        }
    }
    catch (RequestException $e) {
      $e->getMessage();
    }
  }

  public function create_game_node() {

  }

}