<?php
namespace App\Store\Models; 

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
 *
 * @Entity
 * @Table(name="user_sessions")
 */
class UserSession extends AppModel
{
  protected static $readable = ['id', 'sid', 'userId', 'createdTime', 'lastActivityTime'];

  const MODULE_PREFIX = 'user_session.';

  /** @Id @Column(type="integer") @GeneratedValue **/
  protected $id;

  /** @Column(type="integer", name="user_id", nullable=true)**/
  protected $userId;

  /** @Column(type="string", nullable=true) **/
  protected $sid;

  /** @Column(type="datetime", name="created_time") **/
  protected $createdTime;

  /** @Column(type="datetime", name="last_activity_time") **/
  protected $lastActivityTime;

  /**
   * @var UserSession
   */
  protected static $instance = null;

  protected function _validate()
  {
    ValidationManager::validate(function($vm) {

      // userId and sid mustn't both be null / empty
      $vm->register('identifier', new Validator\FunctionValidator(function() {
        return $this->userId != null || !empty($this->sid);
      }));

    });
  }

  /**
   * Initialize object with default values
   * Object is not in valid state until either userId or sid is given a valid value
   */
  protected function __construct()
  {
    $this->userId           = null;
    $this->sid              = null;
    $this->createdTime      = new \DateTime("now");
    $this->lastActivityTime = new \DateTime("now");
  }

  /**
   * Returns singleton UserSession instance
   *
   * @return UserSession
   */
  public static function getInstance()
  {
    if (self::$instance == null)
      self::$instance = self::createInstance();
      
    return self::$instance; 
  }

  /**
   * Create a new UserSession instance
   *
   * @return UserSession
   */
  protected static function createInstance()
  {
    $userSession = null;

    $userId = App::$session->get(self::MODULE_PREFIX.'user.id', null);

    // user signed-in, $userSession is null if no user with $userId found
    if ($userId != null && ($userSession = self::findOneBy(['userId' => $userId])) == null)
      $userSession = self::createForUser($userId);

    // user not signed-in or $userId is invalid, initiate a guess session
    if ($userSession == null && ($userSession = self::findOneBy(['sid' => App::$session->getId()])) == null)
      $userSession = self::createForGuest();

    $userSession->writeLastActivityTime();

    return $userSession;
  }

  /**
   * Create new UserSession for registered user
   *
   * @param int $userId a valid user-id; if $userId is invalid, nothing is created
   * @return UserSession|null if $userId is invalid, null is returned
   */
  protected static function createForUser($userId)
  {
    if (User::find($userId) == null)
      return null;

    $userSession = new UserSession();

    $userSession->userId = $userId;

    $em = self::getEntityManager();
    $em->persist($userSession);
    $em->flush();

    return $userSession;
  }

  /**
   * Create new UserSession for non-registered / anonymous user
   *
   * @return UserSession|null if session is is empty or null
   */
  protected static function createForGuest()
  {
    $sid = App::$session->getId();

    if (empty($sid))
      return null;

    $userSession = new UserSession();

    $userSession->sid = $sid;

    $em = self::getEntityManager();
    $em->persist($userSession);
    $em->flush();

    return $userSession;
  }

  /**
   * @param User $user
   */
  public static function signInWithUser(User $user)
  {
    App::$session->set(self::MODULE_PREFIX.'user.id', $user->id);

    self::$instance = self::createInstance();
  }

  /**
   * Try to sign user in
   *
   * @param string $email
   * @param string $password
   *
   * @throw Markzero\Http\Exception\ResourceNotFoundException
   *        Markzero\Auth\Exception\AuthenticationFailedException
   *        Markzero\Validation\Exception\ValidationException
   */
  public static function signIn($email, $password)
  {
    ValidationManager::validate(function($vm) use($email, $password) {

      $vm->register('user.email', new Validator\EmailValidator($email));
      $vm->register('user.password', new Validator\RequireValidator($password), 'Password is required');

    });

    $user = User::findOneBy(['email' => $email]);

    if ($user === null)
      throw new ResourceNotFoundException();

    if (password_verify($password, $user->password_hash))
      self::signInWithUser($user);
    else
      throw new AuthenticationFailedException();
  }

  /**
   * Sign user out
   */
  public static function signOut() 
  {
    App::$session->remove(self::MODULE_PREFIX.'user.id');

    self::$instance = null;
  }

  /**
   * @return boolean is user signed in
   */
  public function isSignedIn()
  {
    return $this->userId != null;
  }

  /**
   * @return User|null if user is a guest, null is returned
   */
  public function getUser()
  {
    return $this->isSignedIn() ? User::find($this->userId) : null;
  }

  /**
   * @return string session id
   */
  public function getSid()
  {
    return App::$session->getId();
  }

  /**
   * Save lastActivityTime to current time
   */
  protected function writeLastActivityTime()
  {
    $this->lastActivityTime = new \DateTime("now");
    $em = self::getEntityManager();
    $em->persist($this);
    $em->flush();
  }
}
