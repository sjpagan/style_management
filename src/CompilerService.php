<?php

namespace Drupal\style_management;

use Drupal\style_management\Controller;

/**
 * Class CompilerService.
 */
class CompilerService {

  /**
   * @var mixed
   */
  private $state;
  private $config;
  private $processable_file = ['less','scss'];
  private $processable_file_config;
  private $messenger;


  /**
   * Constructs a new CompilerService object.
   */
  public function __construct() {
    $state = \Drupal::state();
    $this->state = $state;

    // Get all configuration
    $this->config = $state->get('style_management.config','');

    // Get All processalbe file
    $this->processable_file_config = (isset($this->config['processable_file']) && !empty($this->config['processable_file'])) ? $this->config['processable_file'] : [];

    // Messages
    $this->messenger = \Drupal::messenger();


  }

  /**
   * @return array
   */
  public function compileAll() {
    $files = [];
    $less = [];
    $scss = [];
    foreach ($this->processable_file_config as $type => $config) {
      if(in_array($type, $this->processable_file)){
        switch ($type) {
          case 'less':
              $less = $this->compileLess($config);
            break;
          case 'scss':
            //@TODO
            $scss = $this->compileScss($config);
            break;
        }
      }
    }
    return array_merge($less,$scss);
  }

  /**
   * @param $config
   * @return array
   */
  private function compileLess($config){

    $files = [];


    $this->getOptionLess($options);

    // Less Parser
      $parser = new \Less_Parser($options, DRUPAL_ROOT);

      foreach ($config as $source => $info) {
        if ($info['watch'] === true) {


          // Get Realpath
          $real_path = \Drupal::service('file_system')->realpath($info['destination_path']);

          // Remove subfolder, start at path by DRUPAL_ROOT
          $path_from_root = str_replace(DRUPAL_ROOT, '', $real_path);

          $path_from_root = substr($path_from_root, 1);
          // define destination empty

          try {
            $compiled_info['destination'] = $path_from_root;
            $compiled_info['source'] = $source;

            $parser->parseFile($source);
            // Cache less
            //$compiled_info['content'] = \Less_Cache::Get($source,$options);


            $compiled_info['content'] = $parser->getCss();


            $files[] = $compiled_info;
          } catch (\Exception $exception) {
            $error = $exception->getMessage();
            $this->messenger->addError('CompilerLess error: '.$error);
          }
        }
      }
      return $files;
    /*
 }

 else {
   $this->messenger->addError('Less Compiler Class not present @TODO');
   return [];
 }
 */
  }


  /**
   * @param array $options
   */
  private function getOptionLess(& $options = []){
    $config =  \Drupal::config('style_management.settings');

    // Get Realpath
    $uri_cache_folder = $config->get('setting.less_cache_folder');

    $real_path = \Drupal::service('file_system')->realpath($uri_cache_folder);

    // Remove subfolder, start at path by DRUPAL_ROOT
    $cache_folder = str_replace(DRUPAL_ROOT, '', $real_path);

    $cache_folder = substr($cache_folder, 1);
    $options = [
      'compress'=> $config->get('setting.less_compress'),
      'cache_dir' =>  $cache_folder
    ];
  }

  /**
   * @param $path
   * @return array
   */
  public function getVariablesLessFromPath($path){
    $variables = [];
    try {
      $parser = new \Less_Parser();
      $parser->parseFile($path);
      $variables = $parser->getVariables();

    } catch (\Exception $exception){
      $error = $exception->getMessage();
      $this->messenger->addError('getVariablesLess error: '.$error);
    }
    return $variables;
  }


  private function compileScss($config){
    return [];
  }



}
