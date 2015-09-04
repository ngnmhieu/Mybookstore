<?php
namespace App\Models;

use Markzero\Mvc\AppModel;
use Markzero\Validation\Validator\RequireValidator;
use Markzero\Validation\Validator\FunctionValidator;
use Doctrine\ORM\UnitOfWork;

/**
 * @MappedSuperclass
 */
abstract class Barcode extends AppModel
{
  protected static $readable = ['id'];
  protected static $accessible = ['value', 'type', 'product'];

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
}
