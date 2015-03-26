<?php

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
   * @ManyToMany(targetEntity="Book", inversedBy="users")
   * @JoinTable(name="ratings")
   */
  protected $books;

  protected function _default() {
  }

  protected function _validate() {
    $vm = self::createValidationManager();

    $vm->validate('name', new FunctionValidator(function($name) {
      return !empty($name);
    }, array($this->name)), "Name is required");

    $vm->validate('email', new FunctionValidator(function($email) {
      return !empty($email);
    }, array($this->email)), "Email address is required");

    $vm->validate('email', new EmailValidator($this->email), "Invalid Email address");

    $vm->validate('email', new FunctionValidator(function($email) {
      $existed_user = self::findOneBy(array('email' => $email));
      return $existed_user === null;
    }, array($this->email)), "Email address already existed");

    $vm->do_validate();
  }

  static function create($params) {
    $em = self::getEntityMananger();

    $obj = new static();
    $obj->name   = $params->get('name');
    $obj->email   = $params->get('email');

    // validate
    $obj->_validate();

    // extra validate
    $vm = self::createValidationManager();

    $vm->validate('password', new FunctionValidator(function($password) {
      return !empty($password);
    }, array($params->get('password'))), "Password is required");

    $vm->do_validate();

    $obj->password_hash = password_hash($params->get('password'), PASSWORD_BCRYPT);

    $em->persist($obj);
    $em->flush();

    return $obj;
  }


}
