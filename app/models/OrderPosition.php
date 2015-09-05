<?php
namespace App\Models;

use Markzero\Mvc\AppModel;

/**
 * @MappedSuperclass
 */
abstract class OrderPosition extends AppModel
{
  protected static $readable   = ['id'];
  protected static $accessible = ['amount', 'price', 'order', 'product'];

  /** @Id @Column(type="integer") @GeneratedValue **/
  protected $id;

  /**
   * @ManyToOne(targetEntity="User", inversedBy="orderPositions")
   */
  protected $product;

  /**
   * @OneToMany(targetEntity="OrderPosition", mappedBy="order", cascade={"remove"})
   */
  protected $positions;
}
