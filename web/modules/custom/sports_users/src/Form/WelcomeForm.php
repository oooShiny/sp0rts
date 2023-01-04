<?php

namespace Drupal\sports_users\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Markup;
use Drupal\group\Entity\Group;
use Drupal\image\Entity\ImageStyle;
use Drupal\user\Entity\User;

class WelcomeForm extends FormBase {

  /**
   * Returns a unique string identifying the form.
   *
   * The returned ID should be a unique string that can be a valid PHP function
   * name, since it's used in hook implementation names such as
   * hook_form_FORM_ID_alter().
   *
   * @return string
   *   The unique string identifying the form.
   */
  public function getFormId() {
    return 'sports_users_welcome_form';
  }

  /**
   * Form constructor.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   The form structure.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Get all groups, display as checkboxes.
    $form['groups'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Select any groups below to join them.'),
    ];
    $groups = \Drupal::entityQuery('group')
      ->execute();
    foreach (Group::loadMultiple($groups) as $group) {
      if ($group->field_logo->isEmpty()) {
        $img = '';
      }
      else {
        $img = ImageStyle::load('square_100x100')
          ->buildUrl($group->field_logo->entity->getFileUri());
      }
      $form['groups']['#options'][$group->id()] =  Markup::create(
        '<img src="'.$img.'" alt="'.$group->label().'" height="100" width="100">'.$group->label()
      );
    }

    // Group submit handlers in an actions element with a key of "actions" so
    // that it gets styled correctly, and so that other modules may add actions
    // to the form. This is not required, but is convention.
    $form['actions'] = [
      '#type' => 'actions',
    ];

    // Add a submit button that handles the submission of the form.
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Join Groups'),
    ];

    return $form;

  }

  /**
   * Form submission handler.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    foreach ($form_state->getValue('groups') as $user_group) {
      if ($user_group !== 0) {
        $g = Group::load($user_group);
        $u = User::load($this->currentUser()->id());
        $g->addMember($u);
        $g->save();
      }
    }

    $messenger = \Drupal::messenger();
    $messenger->addMessage("You've joined your first groups!");

    // Redirect to home page (which should now be populated with group content).
    $form_state->setRedirect('<front>');

  }
}
