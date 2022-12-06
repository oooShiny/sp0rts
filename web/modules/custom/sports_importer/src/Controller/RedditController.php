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
class RedditController extends ControllerBase {

  /**
   * Creates Youtube videos from a channel.
   */
  public function get_data() {

    $client_id = 'U2cGJj78dQGkS2dPl3yPcw';
    $client_secret = 'cenTT7Tua1JpL-0kINsK0-w4A38-xQ';
    $reddit = new \reddit($client_id,$client_secret);
    $callbackUrl = 'https://sp0rts.fans/reddit-auth';
//    $url = $reddit->getLoginUrl($callbackUrl, 'read');
    $token = $reddit->getOAuthToken($_REQUEST['code'],$callbackUrl);
    $reddit->setOAuthToken($token->access_token);

    // Get subreddits from the groups.
    $ids = \Drupal::entityQuery('group')
      ->condition('type', ['team', 'league'], 'IN')
      ->execute();
    $groups = Group::loadMultiple($ids);
    $posts = [];
    foreach ($groups as $group) {
      if ($group->get('field_group_subreddit')->value) {
        $sr_array = explode('/', rtrim($group->get('field_group_subreddit')->value, '/'));
        $subreddit = end($sr_array);
        $result = $reddit->getListing($subreddit, 100);
        foreach ($result->data->children as $post) {
          $p = $post->data;
          if ($p->ups > 100) {
            $posts[] = [
              'id' => $p->id,
              'reddit_url' => $p->permalink,
              'title' => $p->title,
              'author' => $p->author,
              'popularity' => $p->ups,
              'url' => $p->url,
              'text' => $p->selftext
            ];
            // Create the Reddit node and assign it to the group.
            // Check to see if there's any content with this id already.
            $nodes = \Drupal::entityTypeManager()
              ->getStorage('node')
              ->loadByProperties(['field_reddit_id' => $p->id]);
            if (empty($nodes)) {
              if (strlen($p->title) > 255) {
                $post_title = substr($p->title, 0, 250) . '...';
                $post_text = $post_title . "\n" . $p->selftext;
              }
              else {
                $post_title = $p->title;
                $post_text = $p->selftext;
              }
              // Create the reddit post and post it to the correct group.
              $node = Node::create([
                'type' => 'reddit_post',
                'title' => $post_title,
                'uid' => 1,
                'body' => [
                  'summary' => '',
                  'value' => $post_text,
                  'format' => 'full_html',
                ],
                'field_reddit_id' => $p->id,
                'field_reddit_author' => [
                  'uri' => 'https://reddit.com/u/' . $p->author,
                  'title' => $p->author
                ],
                'field_reddit_url' => [
                  'uri' => 'https://reddit.com/' . $p->permalink,
                  'title' => $p->permalink
                ],
                'field_post_link' => [
                  'uri' => $p->url,
                  'title' => $post_title
                ],
              ]);

              $node->enforceIsNew();
              $node->save();

              $relation = $group->getContentByEntityId('group_node:reddit_post', $node->id());
              if (!$relation) {
                $group->addContent($node, 'group_node:reddit_post');
              }
            }
          }
        }
      }
    }

    return [
      '#theme' => 'reddit_importer_admin',
      '#posts' => $posts,
    ];
  }

}