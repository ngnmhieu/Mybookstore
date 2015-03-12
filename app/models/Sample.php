<?php

/**
 * @Entity @Table(name="samples")
 **/
class Sample extends AppModel {
  protected static $attr_reader = array('id');
  protected static $attr_accessor = array('name', 'other_attributes');

  /** @Id @Column(type="integer") @GeneratedValue **/
  protected $id;
  /** @Column(type="string") **/
  protected $name;
  /** @Column(type="attribute_datatype") **/
  protected $other_attributes;

  public function _default() {
  }

  protected function _validate() {
    $vm = self::createValidationManager();

    $vm->validate("attribute", new FunctionValidator(function($amount) {
      return !empty($amount);
    }, array($this->name)) ,"Name should not be empty");

    $vm->validate("attribute", new SomeOtherValidator(...) ,"Error Message");

    $vm->do_validate();
  }

  /**
   * CRUD
   */
  static function update($id, $params) {
    $obj = static::find($id);

    $obj->name        = $params->get('name');
    $obj->attribute   = $params->get('attribute');

    App::$em->persist($obj);
    App::$em->flush();

    return $obj;
  }
}
