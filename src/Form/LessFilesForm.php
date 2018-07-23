<?php

namespace Drupal\style_management\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\style_management\CompilerService;
use Drupal\style_management\Controller\MainController;


/**
 * Class LessFilesForm.
 */
class LessFilesForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'style_management.lessfiles',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'less_files_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $state = \Drupal::state();
    // Get current config


    $config = $state->get('style_management.config','');

    // Get Default config for LESS Settings
    $default_config =  \Drupal::config('style_management.settings');

    // Check if processable_file exist
    if(isset($config['processable_file']) && !empty($config['processable_file'])){
      $processable_file = $config['processable_file'];
      if(count($processable_file) > 0){

        // Check if LESS exist
        if(isset($processable_file['less']) && !empty($processable_file['less'])){
          $less = $processable_file['less'];
          if(count($less) > 0){

            // Load Compiler Service
            $CompilerService = new CompilerService();


            // Each options of single LESS file
            foreach ($less as $key => $file_info){
              if(!isset($form['groups'][$key])){
                $form['groups'][$key] = [
                  '#type' => 'details',
                  '#title' => $key,
                ];
              }

              // Option - Watch
              if(isset($file_info['watch'])){
                $form['groups'][$key]['watch'] = [
                  '#type' => 'checkbox',
                  '#title' => $this->t('Watcher'),
                  '#default_value' => $file_info['watch'],
                ];
              }

              // Option - Aggregate
              if(isset($file_info['aggregate'])){
                $form['groups'][$key]['aggregate'] = [
                  '#type' => 'checkbox',
                  '#title' => $this->t('Aggregate'),
                  '#default_value' => $file_info['aggregate'],
                ];
              }

              // Option - Destination Path
              $destination_path = $default_config->get('setting.less_default_destination_folder');
              if(isset($file_info['destination_path']) && !empty($file_info['destination_path'])){
                $destination_path = $file_info['destination_path'];
              }

              $form['groups'][$key]['destination_path'] = [
                '#type' => 'textfield',
                '#title' => $this->t('Destination Path'),
                '#default_value' => $destination_path ,
              ];
              // Option - Alter_Variables
              if(isset($file_info['alter_variables'])){




                $variables_from_file = $CompilerService->getVariablesLessFromPath($key);


                $alter_variables = MainController::MergeVariables($variables_from_file,$file_info['alter_variables'],['serialize' => false ] );
                dsm($alter_variables);
                $alter_variables =\GuzzleHttp\json_encode($alter_variables);

                $markup = "<div class='sm-header'>";
                $markup .= "<div><i class='sm-icon-less'></i></div> <div class='sm-header-actions'><i class='js-button sm-icon-add' action='add'></i> </div>";
                $markup .= "</div>";
                $markup .= "<div class='sm-body'> </div>";


                $legend = '<ul class="legend">';
                $string = [
                  'file' => 'Only File',
                  'config' => 'Only Config',
                  'override' => 'Override Config File',

                ];
                $legend .= '<li><i class="sm-icon-only-file"></i>  <span>' .$string["file"]. '</span></li>';
                $legend .= '<li><i class="sm-icon-only-config"></i>  <span>' .$string["config"]. '</span></li>';
                $legend .= '<li><i class="sm-icon-override"></i>  <span>' .$string["override"]. '</span></li>';
                $legend .= '</ul>';




                $form['groups'][$key]['alter_variables'] = [
                  '#type' => 'hidden',
                  '#title' => $this->t('Alter Variables'),
                  '#default_value' => $alter_variables,
                  '#prefix' => '<div class="js-variables-editor-wrapper">',
                  '#suffix' => '<div class="js-live-editor">' . $markup . '</div><div class="legend-wrapper">' . $legend . '</div></div>'
                ];
              }
            }
            $form['#attached']['library'][] = 'style_management/variables-editor';
          }
        }
      }
    }

    //@TODO attached file js to manage options present in alter variables
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

    $this->config('style_management.lessfiles')
      ->save();
  }

}
