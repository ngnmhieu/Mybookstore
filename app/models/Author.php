<?php
namespace App\Models;

use Markzero\Mvc\AppModel;
use Symfony\Component\HttpFoundation\ParameterBag;
use Markzero\Validation\Validator\RequireValidator;
use Markzero\Http\Exception\ResourceNotFoundException;

/**
 * @Entity
 * @Table(name="authors")
 */
class Author extends AppModel
{
  protected static $readable = ['id'];
  protected static $accessible = ['name', 'books'];

  /** @Id @Column(type="integer") @GeneratedValue **/
  protected $id;
  
  /** @Column(type="string") **/
  protected $name;

  /**
   * @ManyToMany(targetEntity="Product", inversedBy="authors")
   * @JoinTable(name="authors_products")
   */
  protected $books;

  protected function _validate() 
  {
    $vm = self::createValidationManager(); 

    $vm->validate(function($vm) {

      $vm->register('name', new RequireValidator($this->name), 'Author name is required');

    });
  }

  public static function create(ParameterBag $params) 
  {
    $em = self::getEntityManager();

    $author = new Author();
    $author->name = $params->get('name');

    $em->persist($author);
    $em->flush();

    return $this;
  }

  public static function update($id, ParameterBag $params) 
  {
    $em = self::getEntityManager();

    $author = Author::find($id);

    if ($author == null) {
      throw new ResourceNotFoundException();  
    }

    $author->name = $params->get('name');

    $em->persist($author);
    $em->flush();

    return $this;
  }
}
