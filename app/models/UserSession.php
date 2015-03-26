<?php
use Markzero\Mvc\AppModel;
use Markzero\Http\Exception\ResourceNotFoundException;
use Markzero\Auth\Exception\AuthenticationFailedException;

class UserSession extends AppModel {

  static function setUser($user) {
    $_SESSION['user.id'] = $user->id;
  }

  static function unsetUser() {
    $_SESSION['user.id'] = null;
  }

  /**
   *  @throw Markzero\Http\Exception\ResourceNotFoundException
   *  @throw Markzero\Auth\Exception\AuthenticationFailedException
   */
  static function create($params) {

    $email = $params->get('user[email]', null, true);
    $password = $params->get('user[password]', null, true);

    $vm = self::createValidationManager();
    $vm->validate('user.email', new Validator\EmailValidator($email));
    $vm->validate('user.password', new Validator\RequireValidator($password), 'Password is required');
    $vm->do_validate();

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
    return isset($_SESSION['user.id']) && (User::find($_SESSION['user.id']) !== null);
  }

  /**
   * @return User|null
   */
  static function getUser() {
    return self::isSignedIn() ? User::find($_SESSION['user.id']) : null;
  }

  static function delete() {
    self::unsetUser();
  }

}
