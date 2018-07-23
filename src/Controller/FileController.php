<?php

namespace Drupal\style_management\Controller;

use Drupal\Core\Controller\ControllerBase;



/**
 * Class FileController.
 */
class FileController extends ControllerBase {
  private $processable_files;


  public function __construct() {
    $this->processable_files = ['less','scss'];


  }

  /**
   * @param $config array
   * @param $file_path string
   */
  public function isProcessable(&$config,$file_path){

    $file_type = substr($file_path, -4);
    $file_type_to_lower = strtolower($file_type);

    if(in_array($file_type_to_lower,$this->processable_files)){

      /**
       * Verify if confie exist, if not exist provide a default configuration
       * watch => boolean | watch file true or false
       * aggregate => boolean | aggregate file in a single file compiled
       * alter_variables => array | replace value of values present on file before compile
       */
      if(!isset($config['processable_file'][ $file_type_to_lower][$file_path]) && empty($config['processable_file'][ $file_type_to_lower][$file_path])){
        switch($file_type_to_lower){
          case 'less':
            $empty_config = [
              'watch' => true,
              'destination_path' => '',
              'aggregate' => false,
              'alter_variables' => []
            ];
            break;
          case 'scss':
            $empty_config = [
              'watch' => true,
              'destination_path' => '',
            ];
            break;

        }
        $config['processable_file'][ $file_type_to_lower ][ $file_path ] = $empty_config;
      }
    }
  }


  /**
   * @param $source
   *
   * @return mixed
   */
  public function getCompiledFileName($source){

    $source_to_lowercase = strtolower($source);

    $fileType = substr($source_to_lowercase, -4);

    $exploded_path = explode('/', $source);
    $current_file_name = end($exploded_path);

    return str_replace($fileType,'css',$current_file_name);
  }

  /**
   * @param $files
   *
   * source_with_file_name
   * destination_path
   * content
   */
  public function writeFiles($files = ''){
    $messenger = \Drupal::messenger();
    $state = \Drupal::state();
    if($files != '') {
      foreach ($files as $info) {
        try {

          $default_config =  \Drupal::config('style_management.settings');
          $destination_path = $default_config->get('setting.less_default_destination_folder');
          if(isset($file_info['destination_path']) && !empty($file_info['destination_path'])){
            $destination_path = $file_info['destination_path'];
          }


          if (file_prepare_directory($destination_path, FILE_CREATE_DIRECTORY)) {
            $new_file_name = $this->getCompiledFileName($info['source']);

            $destination = '';

            if (!empty($info['content'])) {
              $destination = $destination_path . '/' . $new_file_name;

              //@TODO write this function with unmanaged file create
              $sm_file = fopen($destination, 'w');
              fwrite($sm_file, $info['content']);
              fclose($sm_file);
            }

            // write complete uri of compiled file in config
            if ($destination == !'') {
              $current_config = $state->get('style_management.config', '');
              $source = $info['source'];
              $current_config['processable_file']['less'][$source]['compiled_file_path'] = $destination;
              $state->set('style_management.config', $current_config);
            }
          } else {
            $error = $this->t('impossible create folder ') . $info['destination'];
            $messenger->addError($error);
          }
        } catch (\Exception $exception) {
          $error = $this->t('impossible compile css ') . $exception;
          $messenger->addError($error);
        }
      }
    }
  }
}
