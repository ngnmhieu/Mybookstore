<?php
namespace App\Models; 

use Markzero\Mvc\AppModel;
use App\Lib\GoogleBook\Book;
use \Symfony\Component\HttpFoundation\ParameterBag;
use \Doctrine\Common\Collections\ArrayCollection;
use Markzero\Validation\Validator\RequireValidator;
use Markzero\Validation\Validator\FunctionValidator;
use Markzero\Validation\Exception\ValidationException;
use Markzero\Http\Exception\ResourceNotFoundException;

/**
 * @Entity 
 * @Table(name="products")
 **/
class Product extends AppModel {

  protected static $readable = array('id');
  protected static $accessible = array('name', 'price', 'short_desc', 'description', 'ratings', 'category', 'barcodes');

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
   * @OneToMany(targetEntity="Barcode", mappedBy="product")
   */
  protected $barcodes;

  public function __construct() {
    $this->ratings = new ArrayCollection();
    $this->barcodes = new ArrayCollection();
  }

  /**
   * @return string | null
   */
  public function getIssn()
  {
    $issn = Barcode::findOneBy([
      'type'    => Barcode::ISSN,
      'product' => $this
    ]);

    return $issn ? $issn->value : null;
  }

  /**
   * @return string | null
   */
  public function getIsbn13()
  {
    $isbn13 = Barcode::findOneBy([
      'type'    => Barcode::ISBN_13,
      'product' => $this
    ]);

    return $isbn13 ? $isbn13->value : null;
  }
  /**
   * @return string | null
   */
  public function getIsbn10()
  {
    $isbn10 = Barcode::findOneBy([
      'type'    => Barcode::ISBN_10,
      'product' => $this
    ]);

    return $isbn10 ? $isbn10->value : null;
  }

  protected function _validate() {
    $vm = self::createValidationManager();

    $vm->register('name', new FunctionValidator(function($name) {
      return !empty($name);
    }, array($this->name)));

    $vm->doValidate();
  }

  /**
   * @param 
   * @throw Markzero\Validation\Exception\ValidationException
   * @return Product
   */
  static function create(ParameterBag $params) {
    $em = self::getEntityManager();
    $vm = self::createValidationManager();

    $isbn10 = $params->get('product[isbn_10]', null, true);
    $isbn13 = $params->get('product[isbn_13]', null, true);
    $issn   = $params->get('product[issn]', null, true);

    $vm->validate(function($vm) use ($params, $isbn10, $isbn13, $issn) {

      $name = $params->get('product[name]', '', true) ;
      $vm->register('product[name]', new FunctionValidator(function() use($name) {
        return !empty($name);
      }), 'Product name is required');

      if ($isbn10) {
        $vm->register('product[isbn_10]', new FunctionValidator(function() use($isbn10) {
          return strlen($isbn10) == 10;
        }), 'ISBN10 must have length of 10');

        $vm->register('product[isbn_10]', new FunctionValidator(function() use($isbn10) {
          return empty(Barcode::findOneBy(['type' => Barcode::ISBN_10, 'value' => $isbn10]));
        }), 'Duplicated ISBN10');
      }

      if ($isbn13) {
        $vm->register('product[isbn_13]', new FunctionValidator(function() use($isbn13) {
          return strlen($isbn13) == 13;
        }), 'ISBN13 must have length of 13');

        $vm->register('product[isbn_13]', new FunctionValidator(function() use($isbn13) {
          return empty(Barcode::findOneBy(['type' => Barcode::ISBN_13, 'value' => $isbn13]));
        }), 'Duplicated ISBN13');
      }

      if ($issn) {
        $vm->register('product[issn]', new FunctionValidator(function() use($issn) {
          return strlen($issn) == 8;
        }), 'ISSN must have length of 8');

        $vm->register('product[issn]', new FunctionValidator(function() use($issn) {
          return empty(Barcode::findOneBy(['type' => Barcode::ISSN, 'value' => $issn]));
        }), 'Duplicated ISSN');
      }

    });

    $product = new static();
    if ($isbn10) {
      $isbn10_entity = new Barcode($isbn10, Barcode::ISBN_10, $product);
      $product->barcodes[] = $isbn10_entity;
      $em->persist($isbn10_entity);
    }
    
    if ($isbn13) {
      $isbn13_entity = new Barcode($isbn13, Barcode::ISBN_13, $product);
      $product->barcodes[] = $isbn13_entity;
      $em->persist($isbn13_entity);
    }

    if ($issn) {
      $issn_entity = new Barcode($issn, Barcode::ISSN, $product);
      $product->barcodes[] = $issn_entity;
      $em->persist($issn_entity);
    }

    $product->name = $params->get('product[name]', '', true);
    $product->short_desc   = $params->get('product[short_desc]', '', true);
    $product->description  = $params->get('product[description]', '', true);
    $price                 = floatval($params->get('product[price]', 0.0, true));
    $product->price        = $price;

    $catid = $params->get('product[category_id]', null, true);
    $cat = Category::find($catid); 

    if ($cat === null)
      throw new ResourceNotFoundException('category cannot be found');

    $product->category = $cat;

    $em->persist($product);
    $em->flush();

    return $product;
  }

  /**
   * @throw Markzero\Http\Exception\ResourceNotFoundException
   *        Markzero\Validation\Exception\ValidationException
   */
  static function update($id, ParameterBag $params) {
    $em = self::getEntityManager();
    $vm = self::createValidationManager();

    $product = static::find($id);
    if ($product === null) {
      throw new ResourceNotFoundException();
    }

    $vm->validate(function($vm) use ($params) {
      $name = $params->get('product[name]', '', true) ;
      $vm->register('product[name]', new FunctionValidator(function() use($name) {
        return !empty($name);
      }));
    });

    $product->name         = $params->get('product[name]', '', true);
    $product->short_desc   = $params->get('product[short_desc]', '', true); 
    $product->description  = $params->get('product[description]', '', true); 
    $price = floatval($params->get('product[price]', 0.0, true));
    $product->price = $price;

    $catid = $params->get('product[category_id]', null, true);
    $cat = Category::find($catid); 

    if ($cat === null)
      throw new ResourceNotFoundException('category cannot be found');

    $product->category = $cat;

    $em->persist($product);
    $em->flush();

    return $product;
  }

  /**
   * @throw Markzero\Http\Exception\ResourceNotFoundException
   *        Exception
   */
  static function delete($id) {
    $product = Product::find($id);
    if ($product === null) {
      throw new ResourceNotFoundException();
    }

    $conn = App::$em->getConnection();
    $conn->beginTransaction();

    try {
      App::$em->remove($product); 
      App::$em->flush();

      $conn->commit();
    } catch(Exception $e) {
      $conn->rollback();
      throw $e;
    }
  }

  /**
   * Return latest books
   */
  static function getLatest() {
    return static::findBy(array(), array('id' => 'desc'), 12);
  }

  /**
   * Create new book with data from GoogleBook
   * @param App\Lib\GoogleBook\Book
   * @return Product
   */
  static function createFromGoogleBook(Book $gbook) {
    $em = self::getEntityManager();
    $book = new static();

    $book->name         = $gbook->getTitle();
    $book->barcode      = $gbook->getIsbn10();

    $book->description  = $gbook->getDescription();
    $book->short_desc   = '';
    $book->price        = $gbook->getListPrice();

    $em->persist($book);
    $em->flush();

    return $book;
  }

  /**
   * @return Rating | null
   */
  function ratingByUser($user) {
    if ($user === null)
      return null;
    $rating = Rating::findOneBy(array('user' => $user, 'product' => $this));

    return $rating;
  }

  function meanRating() {
    $ratings = Rating::findBy(array('product' => $this));
    $rating_sum = array_reduce($ratings, function($sum, $rating) {
      return $sum + $rating->value; 
    });

    $mean = count($ratings) > 0 ? $rating_sum / count($ratings) : 0;

    return $mean;
  }

  function positiveRatingPercent() {
    $ratings = Rating::findBy(array('product' => $this));

    $positive_ratings = array_filter($ratings, function($rating) {
      return ((int) $rating->value) >= 4; 
    });

    if (!count($ratings)) {
      return 0;
    }

    return 100 * count($positive_ratings) / count($ratings);
  }

  /**
   * @param int $num top $num related Products
   */
  function getTopRelated($num) {
    $em = self::getEntityManager();

    // products other than this
    $query = $em->createQuery('SELECT b FROM Product b WHERE b.id != :product_id');
    $query->setParameter(':product_id', $this->id);
    $all_products = $query->getResult();

    // users rated this product
    $query = $em->createQuery('
      SELECT u.id FROM User u JOIN u.products b 
      WHERE b.id = :product_id GROUP BY u
    ');
    $query->setParameter(':product_id', $this->id);
    $uids_this = $query->getResult();
    $uids_this = array_flatten($uids_this);

    // calculate
    $scores = array();
    $products_map = array();
    foreach($all_products as $product) {
      $query = $em->createQuery('
        SELECT u.id FROM User u JOIN u.products b 
        WHERE b.id = :product_id GROUP BY u
      ');
      $query->setParameter(':product_id', $product->id);
      $uids_that = $query->getResult();
      $uids_that = array_flatten($uids_that);

      $uids_both = array_intersect($uids_that, $uids_this);
      $scores[$product->id] = count($uids_both) / count($uids_this);

      // store for later access by id
      $products_map[$product->id] = $product;
    }
  
    // sort with max on top
    arsort($scores);

    // get the products
    $top_related_products = array();
    $i = 0;
    foreach ($scores as $product_id => $score) {
      if ($i++ >= $num)
        break;
      $top_related_products[] = $products_map[$product_id];
    }

    return $top_related_products;
  }

}
