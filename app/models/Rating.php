<?php
namespace App\Models; 

use Markzero\Mvc\AppModel;
use Markzero\Validation\Validator;
use Markzero\Http\Exception\ResourceNotFoundException;
use Markzero\Http\Exception\DuplicateResourceException;

/**
 * @MappedSuperclass
 */
abstract class Rating extends AppModel 
{
  protected static $readable   = ['id'];
  protected static $accessible = ['value', 'user', 'product'];

  protected static $SCALAR = [1, 2, 3, 4, 5];
  
  /** @Id @Column(type="integer") @GeneratedValue **/
  protected $id;

  /** @Column(type="integer") **/
  protected $value;

  /**
   * @ManyToOne(targetEntity="User", inversedBy="ratings")
   */
  protected $user;

  /**
   * @ManyToOne(targetEntity="Product", inversedBy="ratings")
   */
  protected $product;

  /**
   * @return array possible rating values
   */
  public static function getScalar()
  {
    return self::$SCALAR;
  }
}
