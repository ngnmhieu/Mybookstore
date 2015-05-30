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
class Category extends AppModel {
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

  public function __construct() {
    $this->products = new ArrayCollection();
  }

  protected function _validate() {
    $vm = static::createValidationManager();

    $vm->validate(function($vm) {
      $vm->register('name', new RequireValidator($this->name));
    });
  }

  public static function create($params) {

    $em = self::getEntityManager();
    
    $cat = new static();
    $cat->name = $params->get('name', '');
    
    $em->persist($cat);
    $em->flush();

    return $cat;
  }

  /**
   * @throw Exception
   */
  static function delete($id) {
    $em = self::getEntityManager();

    $em->getConnection()->beginTransaction();
    try {
      $cat = static::find($id);
      $em->remove($cat); 

      $em->flush();
      $em->getConnection()->commit();
    } catch(Exception $e) {
      $em->getConnection()->rollback();
      throw $e;
    }
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
