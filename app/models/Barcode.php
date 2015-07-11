<?php
namespace App\Models;

use Markzero\Mvc\AppModel;
use Markzero\Validation\Validator\RequireValidator;
use Markzero\Validation\Validator\FunctionValidator;
use Doctrine\ORM\UnitOfWork;

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
        return empty(Barcode::findOneBy(['type' => $this->type, 'value' => $this->value]));
      }), 'There is a duplicated barcode of type '.$this->type.' with the value '.$this->value);

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

  /**
   * @param string barcode type
   * @param string barcode value
   * @param Product the product that the barcode belongs to
   *
   * @return Barcode | null duplicate barcode
   */
  public static function findDuplicate($type, $value, Product $product = null)
  {
    $em = self::getEntityManager();

    $is_product_new = $product == null || $em->getUnitOfWork()->getEntityState($product) == UnitOfWork::STATE_NEW;

    $query = "
      SELECT b FROM App\Models\Barcode b
      JOIN b.product p
      WHERE b.type = :type AND b.value = :value";

    $query .= $is_product_new ? '' : ' AND p.id != :product_id'  ;

    $query = $em->createQuery($query);

    if (!$is_product_new) 
      $query->setParameter('product_id', $product->id);

    $query->setParameter('type', $type);
    $query->setParameter('value', $value);
    $result = $query->getResult();

    return !empty($result) ? array_shift($result) : null;
  }
}
