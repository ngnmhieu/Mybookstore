<?php
namespace App\Store\Models; 

use Markzero\Mvc\AppModel;
use Markzero\Validation\Validator;

/**
 * @Entity
 * @Table(name="users")
 */
class User extends \App\Models\User 
{
  protected function _validate() 
  {
    $vm = self::createValidationManager();

    $vm->register('name', new Validator\FunctionValidator(function($name) {
      return !empty($name);
    }, array($this->name)), "Name is required");

    $vm->register('email', new Validator\FunctionValidator(function($email) {
      return !empty($email);
    }, array($this->email)), "Email address is required");

    $vm->register('email', new Validator\EmailValidator($this->email), "Invalid Email address");

    $vm->register('email', new Validator\FunctionValidator(function($email) {
      $existed_user = self::findOneBy(array('email' => $email));
      return $existed_user === null;
    }, array($this->email)), "Email address already existed");

    $vm->doValidate();
  }

}
