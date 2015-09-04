<?php
namespace App\Admin\Models; 

use Doctrine\Common\Collections\ArrayCollection;
use Markzero\Mvc\AppModel;
use Markzero\Validation\Validator\RequireValidator;
use Markzero\Http\Exception\ResourceNotFoundException;

/**
 * @Entity
 * @Table(name="categories")
 */
class Category extends \App\Models\Category 
{
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

  public function migrateTo(Category $alt)
  {
    $em = self::getEntityManager();
    $products = $this->products;

    foreach ($products as $product) {
      $product->category = $alt;
      $em->persist($product);
    }

    $em->flush();
  }

  /**
   * @return array All categories exclude this
   */
  public function findAllOthers()
  {
    $em = self::getEntityManager();
    $query = $em->createQuery('SELECT c FROM App\Admin\Models\Category c WHERE c.id != :cat_id');
    $query->setParameter('cat_id', $this->id);

    return $query->getResult();
  }

  public static function create($params) 
  {
    $em = self::getEntityManager();
    
    $cat             = new Category();
    $cat->name       = $params->get('name');
    $cat->created_at = new \DateTime("now");
    $cat->updated_at = new \DateTime("now");
    
    $em->persist($cat);
    $em->flush();

    return $cat;
  }

  /**
   * @throw ValidationException
   */
  public static function update($id, $params)
  {
    $em = self::getEntityManager();

    $cat = static::find($id);
    if ($cat === null) {
      throw new ResourceNotFoundException();
    }
    $cat->name       = $params->get('name');
    $cat->updated_at = new \DateTime("now");

    $em->persist($cat);
    $em->flush();

    return $cat;
  }
}
