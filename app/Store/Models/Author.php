<?php
namespace App\Store\Models;

use Markzero\Mvc\AppModel;
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
    $vm = self::createValidationManager(); 

    $vm->validate(function($vm) {

      $vm->register('name', new RequireValidator($this->name), 'Author name is required');

    });
  }
}
