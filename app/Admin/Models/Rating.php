<?php
namespace App\Admin\Models; 

use Markzero\Mvc\AppModel;
use Markzero\Validation\Validator;
use Markzero\Http\Exception\ResourceNotFoundException;
use Markzero\Http\Exception\DuplicateResourceException;

/**
 * @Entity
 * @Table(name="ratings")
 */
class Rating extends \App\Models\Rating
{
  function _validate()
  {
    $vm = self::createValidationManager();

    $vm->register('value', new Validator\RequireValidator($this->value), 'Value of the rating is required');

    $vm->register('value', new Validator\FunctionValidator(function() {
      return in_array((int) $this->value, Rating::$VALID_VALUES);
    }), 'Rating value is invalid');

    $vm->doValidate();
  }

  /**
   * @throw Markzero\Validation\Exception\ValidationException
   * @throw Markzero\Http\Exception\ResourceNotFoundException
   * @throw Markzero\Http\Exception\DuplicateResourceException
   */
  public static function create($user, $product, $params)
  {
    if ($user === null) {
      throw new ResourceNotFoundException();
    } 

    if ($product === null) {
      throw new ResourceNotFoundException();
    } 

    // one user can only rate a product once
    if (self::findOneBy(array('user' => $user, 'product' => $product))) {
      throw new DuplicateResourceException();
    }
    
    $rating = new static();
    $rating->value = $params->get('rating[value]', null, true); 
    $rating->user = $user;
    $rating->product = $product;
    
    $em = self::getEntityManager();
    $em->persist($rating);
    $em->flush();

    return $rating;
  }

  /** 
   * @throw Markzero\Http\Exception\ResourceNotFoundException
   * @throw Markzero\Validation\Exception\ValidationException
   */
  public static function update($id, $params)
  {
    $rating = self::find($id);
    if ($rating === null ) {
      throw new ResourceNotFoundException();
    }
    
    $rating->value = $params->get('rating[value]', null, true); 
    
    $em = self::getEntityManager();
    $em->persist($rating);
    $em->flush();

    return $rating;
  }

}
