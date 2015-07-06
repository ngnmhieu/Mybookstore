<?php
namespace App\Models; 

use Markzero\App;
use Markzero\Mvc\AppModel;
use Markzero\Validation\Validator;
use Markzero\Http\Exception\ResourceNotFoundException;
use Markzero\Auth\Exception\AuthenticationFailedException;

class UserSession extends AppModel 
{

  protected function _validate() { }

  static function setUser($user) {
    self::getSession()->set('user.id', $user->id);
  }

  static function unsetUser() {
    self::getSession()->remove('user.id');
  }

  static function getSession() {
    return App::$session;
  }

  /**
   *  @throw Markzero\Http\Exception\ResourceNotFoundException
   *  @throw Markzero\Auth\Exception\AuthenticationFailedException
   */
  static function create($params) {

    $email = $params->get('user[email]', null, true);
    $password = $params->get('user[password]', null, true);

    $vm = self::createValidationManager();
    $vm->register('user.email', new Validator\EmailValidator($email));
    $vm->register('user.password', new Validator\RequireValidator($password), 'Password is required');
    $vm->doValidate();

    $user = User::findOneBy(array('email' => $email));
    if ($user === null) {
      throw new ResourceNotFoundException();
    }

    if (password_verify($password, $user->password_hash)) {
      self::setUser($user);
    } else {
      throw new AuthenticationFailedException();
    }

    return $user;
  }

  /**
   * Is user signed in
   * @return boolean
   */
  static function isSignedIn() {
    $session = self::getSession();
    return $session->has('user.id') && (User::find($session->get('user.id')) !== null);
  }

  /**
   * @return User|null
   */
  static function getUser() {
    $session = self::getSession();
    return self::isSignedIn() ? User::find($session->get('user.id')) : null;
  }

  static function delete() {
    self::unsetUser();
  }

}
