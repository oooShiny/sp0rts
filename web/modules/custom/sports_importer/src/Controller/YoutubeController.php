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
class YoutubeController extends ControllerBase {

  /**
   * Creates Youtube videos from a channel.
   */
  public function get_data() {
    $baseUrl = 'https://www.googleapis.com/youtube/v3/';
    // https://developers.google.com/youtube/v3/getting-started
    $apiKey = 'AIzaSyAc_6LgNHiENX4kXSuEkNpW44Gpk-kOp90';

    $ids = \Drupal::entityQuery('group')
      ->condition('type', ['team', 'league'], 'IN')
      ->execute();
    $groups = Group::loadMultiple($ids);
    $videos = [];
    foreach ($groups as $group) {
      if ($group->get('field_group_youtube_channel')->value) {
        $params = [
          'id'=> $group->get('field_group_youtube_channel')->value,
          'part'=> 'contentDetails',
          'key'=> $apiKey
        ];
        $url = $baseUrl . 'channels?' . http_build_query($params);
        $json = json_decode(file_get_contents($url), true);

        $playlist = $json['items'][0]['contentDetails']['relatedPlaylists']['uploads'];

        $params = [
          'part'=> 'snippet',
          'playlistId' => $playlist,
          'maxResults'=> '50',
          'key'=> $apiKey
        ];
        $url = $baseUrl . 'playlistItems?' . http_build_query($params);
        $json = json_decode(file_get_contents($url), true);


        $today = date_create(date('c', time()));
        foreach($json['items'] as $video) {
          // TODO: Check date to only pull today's videos.
          $vid_date = date_create($video['snippet']['publishedAt']);
          $diff = date_diff($today, $vid_date);
          $days_apart = $diff->format('%a days');
          if ($days_apart != '0 days') {
            continue;
          }

          // Check to see if there's any content with this title already.
          $nodes = \Drupal::entityTypeManager()
            ->getStorage('node')
            ->loadByProperties(['title' => $video['snippet']['title']]);
          if (empty($nodes)) {
            // Create the video content and post it to the correct group.
            $node = Node::create([
              'type' => 'youtube_video',
              'title' => $video['snippet']['title'],
              'body' => [
                'summary' => '',
                'value' => '<p>' . $video['snippet']['description'] . '</p>',
                'format' => 'full_html',
              ],
              'field_youtube_video_link' => 'https://www.youtube.com/embed/' . $video['snippet']['resourceId']['videoId'],
              'field_youtube_thumbnail' => $video['snippet']['thumbnails']['medium']['url']
            ]);

            $node->save();

            $relation = $group->getContentByEntityId('group_node:youtube_video', $node->id());
            if (!$relation) {
              $group->addContent($node, 'group_node:youtube_video');
            }

            // Add videos to an array for display on admin page.
            $videos[$group->label()][] = [
              'id' => $video['snippet']['resourceId']['videoId'],
              'title' => $video['snippet']['title'],
              'thumbnail' => $video['snippet']['thumbnails']['medium']['url'],
            ];
          }
        }
      }
    }


//    while(isset($json['nextPageToken'])){
//      $nextUrl = $url . '&pageToken=' . $json['nextPageToken'];
//      $json = json_decode(file_get_contents($nextUrl), true);
//      foreach($json['items'] as $video)
//        $videos[] = $video['snippet']['resourceId']['videoId'];
//    }
    return [
      '#theme' => 'youtube_importer_admin',
      '#videos' => $videos,
    ];
  }

}