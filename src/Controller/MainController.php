<?php

namespace Drupal\style_management\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Class MainController.
 */
class MainController extends ControllerBase {


  private $state;
  private $config;
  private $fileController;

  public function __construct() {

    // State
    $state = \Drupal::state();
    $this->state = $state;

    // Config
    $this->config = $this->state->get('style_management.config','');

    // File Controller
    $this->fileController = new FileController();

  }


  /**
   * @param $css
   */
  public function rebuildTree( & $css ){
    // Get all style's path
    $files_path = array_keys($css);

    /**
     * Generate Map of processable file
     * @see src/Controller/FileController
     */
    foreach ($files_path as $item) {
      /**
       * @see \Drupal\style_management\Controller\FileController
       * This method initialize empty config,
       * Return $config
       */
      $this->fileController->isProcessable($this->config, $item);
    }

    // Set config
    $this->state->set('style_management.config', $this->config);
  }


  /**
   *
   */
  public function build(){
    $files = \Drupal::service('style_management.compiler')->compileAll();
    $this->writeFiles($files);
  }

  /**
   * @param $css
   */
  public function alterCss(&$css){
    $processable_files = (isset($this->config['processable_file']) && !empty($this->config['processable_file'])) ? $this->config['processable_file'] : [];
    foreach ($processable_files as $types => $files) {
      $this->override($css, $files);
    }
  }

  /**
   * @param array $files
   */
  private function writeFiles($files = []){
    $this->fileController->writeFiles($files);
  }



  /**
   * Alter $css with compiled file
   *
   * @param $css
   * @param $files
   */
  private function override( & $css, $files){
    foreach ($files as $source => $info){

      // Check if file is in watch mode
      $watch = (isset($info['watch']) && !empty($info['watch'])) ? $info['watch'] : false;

      // Get Compiled file path
      $compiled_file_path = (isset($info['compiled_file_path']) && !empty($info['compiled_file_path'])) ? $info['compiled_file_path'] : false;

      if(isset($css[$source]) && !empty($css[$source])){
        if($watch  && ($compiled_file_path !== false)){
          $current_source_file_info =  $css[$source];

          // unset current file on style info
          unset($css[$source]);

          // make new config of compiled file
          $newInfo = $current_source_file_info;
          $newInfo['data'] = $compiled_file_path;
          $css[$compiled_file_path] = $newInfo;
        }
      }
      else{

        // delete from cache source file if not exist
        unset($css[$source]);
      }
    }
  }

  //**************** Helphers *******

  /**
   * @param array $variables
   * @param array $from_config
   * @param array $option
   * @return string | array
   */
  public static function MergeVariables($variables = [], $from_config = [],$option = ['serialize' => true]){
    $merged_variables = $variables;
    $keys_variables = array_keys($variables);

    if(count($merged_variables) > 0) {
      $tmp = [];
      foreach ($keys_variables as $key) {
        $tmp[$key]['from_file'] = trim($merged_variables[$key]);
        $tmp[$key]['from_config'] = '';
      }
      $merged_variables = $tmp;
    }

    if(count($from_config) > 0) {

      foreach ($from_config as $key => $value) {
        if(isset($merged_variables[$key]) && !empty($merged_variables[$key])){
          $merged_variables[$key]['from_config'] = $value;
        }
        else {
          $merged_variables[$key]['from_file'] = '';
          $merged_variables[$key]['from_config'] = $value;
        }
      }
    }

    if($option['serialize']) {
      return serialize($merged_variables);
    }
    return $merged_variables;
  }
}
