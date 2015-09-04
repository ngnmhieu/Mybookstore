<?php
namespace App\Models; 

use Doctrine\Common\Collections\ArrayCollection;
use Markzero\Mvc\AppModel;
use Markzero\Validation\Validator\RequireValidator;
use Markzero\Http\Exception\ResourceNotFoundException;

/**
 * @MappedSuperclass
 */
abstract class Category extends AppModel 
{
  protected static $readable   = array('id');
  protected static $accessible = array('name', 'products', 'created_at', 'updated_at');
  
  /** @Id @Column(type="integer") @GeneratedValue **/
  protected $id;

  /** @Column(type="string") **/
  protected $name;

  /** @Column(type="datetime") */
  protected $created_at;

  /** @Column(type="datetime") */
  protected $updated_at;

  /** 
   * @OneToMany(targetEntity="Product", mappedBy="category", cascade={"remove"})
   */
  protected $products;
}
