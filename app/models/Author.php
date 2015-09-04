<?php
namespace App\Models;

use Markzero\Mvc\AppModel;
use Symfony\Component\HttpFoundation\ParameterBag;
use Markzero\Validation\Validator\RequireValidator;
use Markzero\Http\Exception\ResourceNotFoundException;

/**
 * @MappedSuperclass
 */
abstract class Author extends AppModel
{
  protected static $readable = ['id'];
  protected static $accessible = ['name', 'books', 'created_at', 'updated_at'];

  /** @Id @Column(type="integer") @GeneratedValue **/
  protected $id;
  
  /** @Column(type="string") **/
  protected $name;

  /** @Column(type="datetime") **/
  protected $created_at;

  /** @Column(type="datetime") **/
  protected $updated_at;
  /**
   * @ManyToMany(targetEntity="Product", inversedBy="authors")
   * @JoinTable(name="authors_products")
   */
  protected $books;
}
