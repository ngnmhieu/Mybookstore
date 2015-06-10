<?php
namespace App\Models;

use Markzero\Mvc\AppModel;

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
  }
}
