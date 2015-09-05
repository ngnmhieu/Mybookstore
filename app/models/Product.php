<?php
namespace App\Models; 

use Markzero\Mvc\AppModel;
use App\Libraries\GoogleBook\Book;
use Doctrine\Common\Collections\Criteria;
use Symfony\Component\HttpFoundation\ParameterBag;
use Doctrine\Common\Collections\ArrayCollection;
use Markzero\Validation\Validator\RequireValidator;
use Markzero\Validation\Validator\FunctionValidator;
use Markzero\Validation\Exception\ValidationException;
use Markzero\Http\Exception\ResourceNotFoundException;
use Markzero\Http\Exception\DuplicateResourceException;

/**
 * @MappedSuperclass
 **/
abstract class Product extends AppModel
{
  protected static $readable = ['id'];
  protected static $accessible = ['name', 'price', 'short_desc', 'description', 'thumb', 'ratings', 'category', 'barcodes', 'authors', 'images', 'updated_at', 'created_at'];

  /** @Id @Column(type="integer") @GeneratedValue **/
  protected $id;

  /** @Column(type="string") **/
  protected $name;

  /** @Column(type="float") **/
  protected $price;

  /** @Column(type="text") **/
  protected $short_desc;

  /** @Column(type="text") **/
  protected $description;

  /**
   * @OneToMany(targetEntity="Rating", mappedBy="product")
   */
  protected $ratings;

  /**
   * @ManyToMany(targetEntity="User", mappedBy="products")
   */
  protected $users;

  /**
   * @ManyToOne(targetEntity="Category", inversedBy="products")
   */
  protected $category;

  /**
   * @OneToMany(targetEntity="Barcode", mappedBy="product", cascade={"persist"})
   */
  protected $barcodes;

  /**
   * @Column(type="datetime");
   */
  protected $created_at;

  /**
   * @Column(type="datetime");
   */
  protected $updated_at;

  /**
   * @ManyToMany(targetEntity="Author", mappedBy="books", cascade={"remove"})
   */
  protected $authors;

  /**
   * @OneToMany(targetEntity="Image", mappedBy="product", cascade={"persist"})
   */
  protected $images;

  /**
   * @var Image thumbnail
   */
  protected $thumb;
}
