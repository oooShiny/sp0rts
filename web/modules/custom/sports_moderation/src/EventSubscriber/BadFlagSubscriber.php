<?php

/**
 * @file
 * Contains \Drupal\sports_moderation\EventSubscriber\BadFlagSubscriber.
 */

namespace Drupal\sports_moderation\EventSubscriber;

use Drupal\group\Entity\Group;
use Drupal\group\Entity\GroupRelationship;
use Drupal\node\Entity\Node;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\flag\Event\FlagEvents;
use Drupal\flag\Event\FlaggingEvent;
use Drupal\flag\Event\UnflaggingEvent;

class BadFlagSubscriber implements EventSubscriberInterface {

  public function onFlag(FlaggingEvent $event) {
    $flagging = $event->getFlagging();
    if ($flagging->getFlag()->getOriginalId() == 'flag_inappropriate_content') {
      self::getRemovalParams($flagging, 'removed', 'removed', '>');
    }
  }

  public function onUnflag(UnflaggingEvent $event) {
    $flagging = $event->getFlaggings();
    $flagging = reset($flagging);
    if ($flagging->getFlag()->getOriginalId() == 'flag_inappropriate_content') {
      self::getRemovalParams($flagging, 'removed', 'published', '<');
    }
  }

  public static function getRemovalParams($flagging, $state, $set_state_to, $comparison) {
    $node = Node::load($flagging->getFlaggable()->id());
    $flag_service = \Drupal::service('flag.count');
    $flag_count = $flag_service->getEntityFlagCounts($node);
    // Get the max flags allowed before removal from group config.
    $group = _get_group($node, 'node');
    $max_flags = $group->get('field_number_of_flags_to_remove_')->value() ?? 5;
    $current_state = $node->get('moderation_state')->value;
    switch ($comparison) {
      case '>':
        if ($flag_count['flag_inappropriate_content'] >= $max_flags && $current_state !== $state) {
          $node->set('moderation_state', $set_state_to);
          $node->save();
        }
        break;
      case '<':
        if ($flag_count['flag_inappropriate_content'] < $max_flags && $current_state !== $state) {
          $node->set('moderation_state', $set_state_to);
          $node->save();
        }
        break;
    }

  }

  public static function getSubscribedEvents() {
    $events = [];
    $events[FlagEvents::ENTITY_FLAGGED][] = ['onFlag'];
    $events[FlagEvents::ENTITY_UNFLAGGED][] = ['onUnflag'];
    return $events;
  }
}
