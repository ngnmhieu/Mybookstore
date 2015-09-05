<?php
namespace App\Admin\Models;

use Markzero\Mvc\AppModel;
use Markzero\Validation\ValidationManager;
use Symfony\Component\HttpFoundation\ParameterBag;
use Markzero\Validation\Validator\RequireValidator;
use Markzero\Http\Exception\ResourceNotFoundException;

/**
 * @Entity
 * @Table(name="authors")
 */
class Author extends \App\Models\Author
{
  protected function _validate() 
  {
    ValidationManager::validate(function($vm) {

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
