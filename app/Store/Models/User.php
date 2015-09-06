<?php
namespace App\Store\Models; 

use Markzero\Mvc\AppModel;
use Markzero\Validation\Validator;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @Entity
 * @Table(name="users")
 */
class User extends \App\Models\User 
{
  public function __construct()
  {
    $this->ratings    = new ArrayCollection();
    $this->created_at = new \DateTime("now");
    $this->updated_at = new \DateTime("now");
  }

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

  public static function create($params)
  {
    $em = self::getEntityManager();

    $obj = new static();
    $obj->name  = $params->get('name');
    $obj->email = $params->get('email');

    // validate
    $obj->_validate();

    // extra validate
    $vm = self::createValidationManager();

    $vm->register('password', new Validator\FunctionValidator(function($password) {
      return !empty($password);
    }, array($params->get('password'))), "Password is required");

    $vm->doValidate();

    $obj->password_hash = password_hash($params->get('password'), PASSWORD_BCRYPT);

    $em->persist($obj);
    $em->flush();

    return $obj;
  }

}
