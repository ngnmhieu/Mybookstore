<?php
namespace App\Models; 

use Markzero\Mvc\AppModel;
use Markzero\Validation\Validator;

/**
 * @MappedSuperclass
 */
abstract class User extends AppModel 
{
  protected static $readable   = ['id'];
  protected static $accessible = ['email', 'name', 'password_hash', 'ratings'];

  /** @Id @Column(type="integer") @GeneratedValue **/
  protected $id;

  /** @Column(type="string") **/
  protected $email;

  /** @Column(type="string") **/
  protected $name;

  /** @Column(type="string") **/
  protected $password_hash;

  /**
   * @OneToMany(targetEntity="Rating", mappedBy="user")
   */
  protected $ratings;

  /**
   * @ManyToMany(targetEntity="Product", inversedBy="users")
   * @JoinTable(name="ratings")
   */
  protected $products;
}
