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
  protected static $accessible = ['name', 'books', 'created_at', 'updated_at'];

  /** @Id @Column(type="integer") @GeneratedValue **/
  protected $id;
  
  /** @Column(type="string") **/
  protected $name;

  /** @Column(type="datetime") **/
  protected $created_at;

  /** @Column(type="datetime") **/
  protected $updated_at;
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

    $author             = new Author();
    $author->name       = $params->get('name');
    $author->created_at = new \DateTime("now");
    $author->updated_at = new \DateTime("now");

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

    $author->name       = $params->get('name');
    $author->updated_at = new \DateTime("now");

    $em->persist($author);
    $em->flush();

    return $this;
  }
}
