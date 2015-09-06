<?php
namespace App\Models; 

use Markzero\Mvc\AppModel;
use Markzero\Validation\Validator;

/**
 * @MappedSuperclass
 */
abstract class User extends AppModel 
{
  protected static $readable   = ['id', 'created_at', 'updated_at'];
  protected static $accessible = ['email', 'name', 'password_hash', 'ratings'];

  /** @Id @Column(type="integer") @GeneratedValue **/
  protected $id;

  /** @Column(type="string") **/
  protected $email;

  /** @Column(type="string") **/
  protected $name;

  /** @Column(type="string") **/
  protected $password_hash;

  /** @Column(type="datetime") **/
  protected $created_at;

  /** @Column(type="datetime") **/
  protected $updated_at;

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
