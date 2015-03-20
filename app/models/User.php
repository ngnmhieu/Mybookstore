<?php

/**
 * @Entity
 * @Table(name="users")
 */
class User extends AppModel {
  protected static $attr_reader = array('id');
  protected static $attr_accessor = array('username', 'name');

  /** @Id @Column(type="integer") GeneratedValue **/
  protected $id;
  /** @Column(type="string") **/
  protected $username;
  /** @Column(type="string") **/
  protected $name;

  
}
