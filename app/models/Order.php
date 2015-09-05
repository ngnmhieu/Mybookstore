<?php
namespace App\Models;

use Markzero\Mvc\AppModel;

/**
 * @MappedSuperclass
 */
abstract class Order extends AppModel
{
  protected static $readable   = ['id'];
  protected static $accessible = ['user', 'positions', 'created_at'];

  /** @Id @Column(type="integer") @GeneratedValue **/
  protected $id;

  /** @Column(type="datetime") **/
  protected $created_at;

  /**
   * @ManyToOne(targetEntity="User", inversedBy="orders")
   */
  protected $user;

  /**
   * @OneToMany(targetEntity="OrderPosition", mappedBy="order", cascade={"remove"})
   */
  protected $positions;
}
