<?php
namespace App\Models;

use Markzero\Mvc\AppModel;

/**
 * @MappedSuperclass
 */
abstract class Order extends AppModel
{
  protected static $readable = ['id', 'created_at', 'updated_at', 'user', 'positions'];

  /** @Id @Column(type="integer") @GeneratedValue **/
  protected $id;

  /**
   * @ManyToOne(targetEntity="User", inversedBy="orders")
   */
  protected $user;

  /**
   * @OneToMany(targetEntity="OrderPosition", mappedBy="order", cascade={"remove", "persist"})
   */
  protected $positions;

  /** @Column(type="datetime") **/
  protected $created_at;

  /** @Column(type="datetime") **/
  protected $updated_at;
}
