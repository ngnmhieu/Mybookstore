<?php
namespace App\Store\Models; 

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
}
