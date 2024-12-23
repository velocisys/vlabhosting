<?php
class emyui_main{
  private static $_instance = null;
  public static function instance()
  {
    if (is_null(self::$_instance)) {
      self::$_instance = new self();
    }
    return self::$_instance;
  }


  /**
   * 18-12-2024
   * 
   * Constructor call
   **/
  public function __construct(){
    
  }
}
emyui_main::instance();