<?php

/**
 * @Entity 
 * @Table(name="books")
 **/
class Book extends AppModel {
  protected static $attr_reader = array('id');
  protected static $attr_accessor = array('name');

  /** @Id @Column(type="integer") @GeneratedValue **/
  protected $id;
  /** @Column(type="string") **/
  protected $name;

  public function _default() {
  }

  protected function _validate() {
    $vm = self::createValidationManager();

    $vm->validate('name', new FunctionValidator(function($name) {
      return !empty($name);
    }, array($this->name)));

    $vm->do_validate();
  }


  /**
   * @throw ValidationException
   */
  static function create($params) {
    $em = self::getEntityMananger();

    $obj = new static();
    $obj->name   = $params->get('name');

    $em->persist($obj);
    $em->flush();

    return $obj;
  }

  /**
   * @throw ResourceNotFoundException
   *        ValidationException
   */
  static function update($params) {
    $em = self::getEntityMananger();

    $obj = static::find($params->get('id'));
    if ($obj === null) {
      throw new ResourceNotFoundException();
    }

    $obj->name = $params->get('name');

    $em->persist($obj);
    $em->flush();

    return $obj;
  }

  static function destroy() {
  }
}
