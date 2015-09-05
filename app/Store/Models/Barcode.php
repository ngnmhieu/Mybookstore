<?php
namespace App\Store\Models;

use Markzero\Mvc\AppModel;
use Markzero\Validation\ValidationManager;
use Markzero\Validation\Validator\RequireValidator;
use Markzero\Validation\Validator\FunctionValidator;
use Doctrine\ORM\UnitOfWork;

/**
 * @Entity
 * @Table(name="barcodes")
 */
class Barcode extends \App\Models\Barcode 
{
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
    ValidationManager::validate(function() use ($vm) {

      $vm->register('barcode_type', new FunctionValidator(function() {
        return in_array($this->type, self::$BARCODE_TYPES); 
      }), 'Barcode type must be one of those: ', implode(', ', self::$BARCODE_TYPES));

      $vm->register('barcode', new FunctionValidator(function() {
        return empty(Barcode::findDuplicate($this->type, $this->value, $this->product));
      }), sprintf('There is a duplicated barcode of type %s with the value %s - Product: %s', $this->type, $this->value, $this->product->name));

      $required_length = 0;
      switch ($this->type) {
        case Barcode::ISBN_10 : 
          $required_length = 10; break;
        case Barcode::ISBN_13 :
          $required_length = 13; break;
        case Barcode::ISSN :
          $required_length = 8; break;
      }

      $vm->register('barcode', new FunctionValidator(function() use($required_length) {
        return $required_length > 0 && strlen($this->value) == $required_length;
      }), 'ISSN must have length of 8');

    });
  }
}
