<?php

namespace Drupal\sports_module\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a block that displays text above the Join Group form.
 *
 * @Block(
 *   id = "sports_group_join_text",
 *   admin_label = @Translation("Sports Group Join Text"),
 *   category = @Translation("Sports"),
 *   context_definitions = {
 *     "group" = @ContextDefinition("entity:group")
 *   } * )
 */
class SportsGroupJoinText extends BlockBase {

    /**
     * {@inheritdoc}
     */
    public function build() {
        $group_name = $this->getContextValue('group')->label();
        $text = 'To join the '.$group_name.' group, please click the button below.';
        return [
            '#markup' => $this->t($text),
        ];
    }

}
