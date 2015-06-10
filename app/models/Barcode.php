<?php
namespace App\Models;

use Markzero\Mvc\AppModel;
use Markzero\Validation\Validator\RequireValidator;
use Markzero\Validation\Validator\FunctionValidator;

/**
 * @Entity
 * @Table(name="barcodes")
 */
class Barcode extends AppModel
{
  protected static $readable = array('id');
  protected static $accessible = array('value', 'type', 'product');

  const ISBN_10 = 'ISBN_10';
  const ISBN_13 = 'ISBN_13';
  const ISSN    = 'ISSN';
  protected static $BARCODE_TYPES = [self::ISBN_10, self::ISBN_13, self::ISSN];

  /** @Id @Column(type="integer") @GeneratedValue **/
  protected $id;

  /** @Column(type="string") **/
  protected $value;

  /** @Column(type="string") **/
  protected $type;

  /**
   * @ManyToOne(targetEntity="Product", inversedBy="barcodes")
   */
  protected $product;

  /**
   * @param string
   * @param string
   */
  public function __construct($value, $type, Product $product = null)
  {
    $this->value   = $value;
    $this->type    = $type;
    $this->product = $product;
  }

  public function _validate()
  { 
    $vm = self::createValidationManager();

    $vm->validate(function() use ($vm) {

      $vm->register('barcode_type', new FunctionValidator(function() {
        return in_array($this->type, self::$BARCODE_TYPES); 
      }), 'Barcode type must be one of those: ', implode(', ', self::$BARCODE_TYPES));

      $vm->register('barcode', new FunctionValidator(function() {
        return empty(Barcode::findOneBy(['type' => $this->type,'value' => $this->value]));
      }), 'Duplicated Barcode');

    });
  }
}
