<?php
namespace App\Models; 

use Doctrine\Common\Collections\ArrayCollection;
use Markzero\Mvc\AppModel;
use Markzero\Validation\Validator\RequireValidator;
use Markzero\Http\Exception\ResourceNotFoundException;

/**
 * @Entity
 * @Table(name="categories")
 */
class Category extends AppModel 
{
  protected static $readable = array('id');
  protected static $accessible = array('name', 'products');
  
  /** @Id @Column(type="integer") @GeneratedValue **/
  protected $id;
  /** @Column(type="string") **/
  protected $name;
  /** 
   * @OneToMany(targetEntity="Product", mappedBy="category")
   */
  protected $products;

  public function __construct() 
  {
    $this->products = new ArrayCollection();
  }

  protected function _validate() 
  {
    $vm = static::createValidationManager();

    $vm->validate(function($vm) {

      $vm->register('name', new RequireValidator($this->name), 'Category name is required');

    });
  }

  public static function create($params) 
  {
    $em = self::getEntityManager();
    
    $cat = new Category();
    $cat->name = $params->get('name');
    
    $em->persist($cat);
    $em->flush();

    return $cat;
  }

  /**
   * @throw ValidationException
   */
  static function update($id, $params) {
    $em = self::getEntityManager();

    $cat = static::find($id);
    if ($cat === null) {
      throw new ResourceNotFoundException();
    }
    $cat->name = $params->get('name');

    $em->persist($cat);
    $em->flush();

    return $cat;
  }
}
