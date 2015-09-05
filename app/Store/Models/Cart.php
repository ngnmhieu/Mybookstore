<?php

/**
 * Represent a shopping cart
 */
class Cart 
{
  /**
   * @var Markzero\Http\Session
   */
  protected $session;

  /**
   * @var Cart
   */
  protected static $instance = null;

  public static function getInstance()
  {
    if (self::$instance == null)
      self::$instance = new static(App::$session);

    return self::$instance;
  }

  protected function __construct(Session $session)
  {
    $this->session = $session;
  } 
}
