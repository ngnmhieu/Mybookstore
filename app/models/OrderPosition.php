<?php
namespace App\Models;

use Markzero\Mvc\AppModel;

/**
 * @MappedSuperclass
 */
abstract class OrderPosition extends AppModel
{
  protected static $readable = ['id','amount', 'price', 'order', 'product'];

  /** @Id @Column(type="integer") @GeneratedValue **/
  protected $id;

  /** @Column(type="float") **/
  protected $price;

  /** @Column(type="integer") **/
  protected $amount;

  /**
   * @ManyToOne(targetEntity="Product", inversedBy="orderPositions")
   */
  protected $product;

  /**
   * @ManyToOne(targetEntity="Order", inversedBy="positions")
   */
  protected $order;
}
