<?php

namespace Drupal\style_management\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class ScssFilesForm.
 */
class ScssFilesForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'style_management.scssfiles',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'scss_files_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $state = \Drupal::state();
    // Get current config
    $config = $state->get('style_management.config','');


    if(isset($config['processable_file']) && !empty($config['processable_file'])){
      $processable_file = $config['processable_file'];
      if(count($processable_file) > 0){
        if(isset($processable_file['scss']) && !empty($processable_file['scss'])){
          $less = $processable_file['scss'];
          if(count($less) > 0){
            dpm($less);
          }
        }
      }
    }


    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $this->config('style_management.scssfiles')
      ->save();
  }

}
