<?php
namespace App\Store\Models;

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
}
