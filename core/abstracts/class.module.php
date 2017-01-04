<?php

namespace Forge\Core\Abstracts;

use \Forge\Core\Classes\Logger;
use \Forge\Core\Interfaces\IModule;

use function \Forge\Core\Classes\i;

abstract class Module implements IModule {
  protected static $instances = array();
  abstract protected function setup();

  // initial variables for module
  public $name = null;
  public $id = null;
  public $version = "0.0.1";
  public $description = "";
  public $image = CORE_WWW_ROOT.'images/default-icon-module.svg';
  public $settingsViews = array();

  public function directory() {
      if (is_null($this->id)) {
          Logger::debug('No id for module: '.get_called_class().' Set it to the foldername for the plugin');
          return;
      }
      return DOC_ROOT.'modules/'.$this->id.'/';
  }

  public function url() {
      if (is_null($this->id)) {
          Logger::debug('No id for module: '.get_called_class().' Set it to the foldername for the plugin');
          return;
      }
      return WWW_ROOT.'modules/'.$this->id.'/';
  }

  public function start() {
      return;
  }

  public function check() {
    if (is_null($this->name)) {
        return sprintf(i('Name for Module not set. Set $name in setup Method in Module `%s`'), get_called_class());
    }
    return true;
  }

  static public function instance() {
    $class = get_called_class();
    if (!array_key_exists($class, static::$instances)) {
        static::$instances[$class] = new $class();
    }
    static::$instances[$class]->id = $class;
    static::$instances[$class]->setup();
    return static::$instances[$class];
  }
  private function __construct() {}
  private function __clone() {}

}

?>
