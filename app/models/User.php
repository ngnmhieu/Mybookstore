<?php
use Markzero\Mvc\AppModel;
use Markzero\Validation\Validator;

/**
 * @Entity
 * @Table(name="users")
 */
class User extends AppModel {
  protected static $attr_reader = array('id');
  protected static $attr_accessor = array('email', 'name', 'password_hash', 'ratings');

  /** @Id @Column(type="integer") @GeneratedValue **/
  protected $id;
  /** @Column(type="string") **/
  protected $email;
  /** @Column(type="string") **/
  protected $name;
  /** @Column(type="string") **/
  protected $password_hash;
  /**
   * @OneToMany(targetEntity="Rating", mappedBy="user")
   */
  protected $ratings;
  /**
   * @ManyToMany(targetEntity="Product", inversedBy="users")
   * @JoinTable(name="ratings")
   */
  protected $products;

  protected function _default() {
  }

  protected function _validate() {
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

  static function create($params) {
    $em = self::getEntityManager();

    $obj = new static();
    $obj->name   = $params->get('name');
    $obj->email   = $params->get('email');

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
