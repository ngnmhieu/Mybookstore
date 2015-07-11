<?php
namespace App\Models; 

use Markzero\App;
use Markzero\Mvc\AppModel;
use Markzero\Http\Session;
use Markzero\Validation\Validator;
use Markzero\Validation\ValidationManager;
use Markzero\Http\Exception\ResourceNotFoundException;
use Markzero\Validation\Exception\ValidationException;
use Markzero\Auth\Exception\AuthenticationFailedException;

/**
 * A Singleton represents User session
 */
class UserSession
{

  /**
   * @var Markzero\Http\Session
   */
  protected $session;

  /**
   * @var UserSession
   */
  protected static $instance = null;

  /**
   * @var string
   */
  public static function getInstance()
  {
    if (self::$instance == null) {
      self::$instance = new static(App::$session);
    }

    return self::$instance;
  }

  protected function __construct(Session $session)
  {
    $this->session = $session;
  } 

  public function setUser(User $user)
  {
    $this->session->set('user.id', $user->id);
  }

  /**
   * Remove User ID in session
   */
  public function unsetUser()
  {
    $this->session->remove('user.id');
  }

  /**
   * Create a new User session
   * @throw Markzero\Http\Exception\ResourceNotFoundException
   *        Markzero\Auth\Exception\AuthenticationFailedException
   *        Markzero\Validation\Exception\ValidationException
   */
  public function create($params)
  {
    $email    = $params->get('user[email]', null, true);
    $password = $params->get('user[password]', null, true);

    $vm = new ValidationManager();

    $vm->validate(function($vm) use($email, $password) {

      $vm->register('user.email', new Validator\EmailValidator($email));
      $vm->register('user.password', new Validator\RequireValidator($password), 'Password is required');

    });

    $user = User::findOneBy(array('email' => $email));

    if ($user === null) {
      throw new ResourceNotFoundException();
    }

    if (password_verify($password, $user->password_hash)) {
      $this->setUser($user);
    } else {
      throw new AuthenticationFailedException();
    }

    return $user;
  }

  /**
   * Is user signed in
   * @return boolean
   */
  public function isSignedIn()
  {
    return $this->session->has('user.id') && (User::find($this->session->get('user.id')) !== null);
  }

  /**
   * @return User|null
   */
  public function getUser()
  {
    return $this->isSignedIn() ? User::find($this->session->get('user.id')) : null;
  }

  public function destroy() 
  {
    $this->unsetUser();
  }

}
