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

/**
 * @Entity 
 * @Table(name="products")
 **/
class Product extends AppModel 
{
  protected static $readable = array('id');
  protected static $accessible = ['name', 'price', 'short_desc', 'description', 'ratings', 'category', 'barcodes', 'authors', 'updated_at', 'created_at'];

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
   * @ManyToMany(targetEntity="Author", mappedBy="books")
   */
  protected $authors;

  public function __construct() 
  {
    $this->ratings  = new ArrayCollection();
    $this->barcodes = new ArrayCollection();
  }

  /**
   * @return Barcode | null
   */
  public function getIssn()
  {
    return $this->getBarcode(Barcode::ISSN);
  }

  /**
   * @return Barcode | null
   */
  public function getIsbn13()
  {
    return $this->getBarcode(Barcode::ISBN_13);
  }
  
  /**
   * @return Barcode | null
   */
  public function getIsbn10()
  {
    return $this->getBarcode(Barcode::ISBN_10);
  }

  /**
   * @return string name
   */
  public function getAuthorNames()
  {
    return array_map(function($author) { 
      return $author->name; 
    }, $this->authors->toArray());
  }

  /**
   * @param string $type one of the barcode types (Barcode::$BARCODE_TYPES)
   * @return Barcode | null 
   */
  public function getBarcode($type)
  {
    $found_barcodes = $this->barcodes->filter(function($b) use($type) {
      return $b->type == $type;
    });

    if (!$found_barcodes->isEmpty()) {
      return $found_barcodes->first();
    }

    return null;
  }

  protected function _validate()
  {
    $vm = self::createValidationManager();

    $vm->register('name', new FunctionValidator(function($name) { return !empty($name);
    }, array($this->name)));

    if ($this->getIsbn10()) 
    {
      $vm->register('product[isbn_10]', new FunctionValidator(function() {
        return strlen($this->getIsbn10()->value) == 10;
      }), 'ISBN10 must have length of 10');

      $vm->register('product[isbn_10]', new FunctionValidator(function() {
        return empty(Barcode::findDuplicate(Barcode::ISBN_10, $this->getIsbn10()->value, $this));
      }), 'Duplicated ISBN10');
    }

    if ($this->getIsbn13()) 
    {
      $vm->register('product[isbn_13]', new FunctionValidator(function() {
        return strlen($this->getIsbn13()->value) == 13;
      }), 'ISBN13 must have length of 13');

      $vm->register('product[isbn_13]', new FunctionValidator(function() {
        return empty(Barcode::findDuplicate(Barcode::ISBN_13, $this->getIsbn13()->value, $this));
      }), 'Duplicated ISBN13');
    }

    if ($this->getIssn()) 
    {
      $vm->register('product[issn]', new FunctionValidator(function() {
        return strlen($this->getIssn()->value) == 8;
      }), 'ISSN must have length of 8');

      $vm->register('product[issn]', new FunctionValidator(function() {
        return empty(Barcode::findDuplicate(Barcode::ISSN, $this->getIssn()->value, $this));
      }), 'Duplicated ISSN');
    }

    $vm->register('product[price]', new Functionvalidator(function() {
      return $this->price > 0;
    }), 'Price must greater than 0');

    $vm->doValidate();
  }

  /**
   * @param $params
   * @param $product
   * @throw Markzero\Validation\Exception\ValidationException
   */
  private static function validateParams(ParameterBag $params, Product $product)
  {
    $vm = self::createValidationManager();

    $vm->validate(function($vm) use ($params, $product) {

      $catid  = $params->get('product[category_id]', null, true);
      $name   = $params->get('product[name]', '', true) ;
      $isbn10 = $params->get('product[isbn_10]', null, true);
      $isbn13 = $params->get('product[isbn_13]', null, true);
      $issn   = $params->get('product[issn]', null, true);

      $vm->register('product[name]', new RequireValidator($name), 'Product Name is required');

      $vm->register('product[category_id]', new RequireValidator($catid), 'Category is required');

      if ($isbn10) {
        $vm->register('product[isbn_10]', new FunctionValidator(function() use($isbn10) {
          return strlen($isbn10) == 10;
        }), 'ISBN10 must have length of 10');

        $vm->register('product[isbn_10]', new FunctionValidator(function() use($isbn10, $product) {
          return empty(Barcode::findDuplicate(Barcode::ISBN_10, $isbn10, $product));
        }), 'Duplicated ISBN10');
      }

      if ($isbn13) {
        $vm->register('product[isbn_13]', new FunctionValidator(function() use($isbn13) {
          return strlen($isbn13) == 13;
        }), 'ISBN13 must have length of 13');

        $vm->register('product[isbn_13]', new FunctionValidator(function() use($isbn13, $product) {
          return empty(Barcode::findDuplicate(Barcode::ISBN_13, $isbn13, $product));
        }), 'Duplicated ISBN13');
      }

      if ($issn) {
        $vm->register('product[issn]', new FunctionValidator(function() use($issn) {
          return strlen($issn) == 8;
        }), 'ISSN must have length of 8');

        $vm->register('product[issn]', new FunctionValidator(function() use($issn, $product) {
          return empty(Barcode::findDuplicate(Barcode::ISSN, $issn, $product));
        }), 'Duplicated ISSN');
      }
    });
  }

  /**
   * @param 
   * @throw Markzero\Validation\Exception\ValidationException
   * @return Product
   */
  static function create(ParameterBag $params) 
  {
    $em = self::getEntityManager();

    $product = new Product();

    self::validateParams($params, $product);

    $isbn10 = $params->get('product[isbn_10]', null, true);
    if ($isbn10) {
      $isbn10_entity = new Barcode($isbn10, Barcode::ISBN_10, $product);
      $product->barcodes[] = $isbn10_entity;
      $em->persist($isbn10_entity);
    }
    
    $isbn13 = $params->get('product[isbn_13]', null, true);
    if ($isbn13) {
      $isbn13_entity = new Barcode($isbn13, Barcode::ISBN_13, $product);
      $product->barcodes[] = $isbn13_entity;
      $em->persist($isbn13_entity);
    }

    $issn = $params->get('product[issn]', null, true);
    if ($issn) {
      $issn_entity = new Barcode($issn, Barcode::ISSN, $product);
      $product->barcodes[] = $issn_entity;
      $em->persist($issn_entity);
    }

    $product->name        = $params->get('product[name]', '', true);
    $product->short_desc  = $params->get('product[short_desc]', '', true);
    $product->description = $params->get('product[description]', '', true);
    $product->price       = floatval($params->get('product[price]', 0.0, true));;
    $product->created_at  = new \DateTime("now");
    $product->updated_at  = new \DateTime("now");

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
   * @param string $type barcode type
   * @param string $type barcode value
   */
  private function updateBarcode($type, $value)
  {
    $em = self::getEntityManager();

    $barcode = $this->getBarcode($type);

    if ($barcode == null)
    {
      if ($value != null) 
      {
        $barcode = new Barcode($value, $type, $this);;
        $this->barcodes[] = $barcode; 
        $em->persist($barcode);
        $em->flush();
      }
    } 
    else 
    {
      if ($value != null)
      {
        $barcode->value = $value;
        $em->persist($barcode);
        $em->flush();
      } 
      else 
      {
        $barcode->destroy();
      }
    }
  }

  /**
   * @throw Markzero\Http\Exception\ResourceNotFoundException
   *        Markzero\Validation\Exception\ValidationException
   */
  static function update($id, ParameterBag $params)
  {
    $em = self::getEntityManager();

    $product = Product::find($id);

    self::validateParams($params, $product);

    if ($product === null) 
    {
      throw new ResourceNotFoundException();
    }

    $product->name        = $params->get('product[name]', '', true);
    $product->short_desc  = $params->get('product[short_desc]', '', true);
    $product->description = $params->get('product[description]', '', true);
    $product->price       = floatval($params->get('product[price]', 0, true));
    $product->updated_at  = new \DateTime("now");

    $isbn10    = $params->get('product[isbn_10]', null, true);
    $isbn13    = $params->get('product[isbn_13]', null, true);
    $issn      = $params->get('product[issn]', null, true);
    $product->updateBarcode(Barcode::ISBN_10, $isbn10);
    $product->updateBarcode(Barcode::ISBN_13, $isbn13);
    $product->updateBarcode(Barcode::ISSN, $issn);

    $catid = $params->get('product[category_id]', null, true);
    $cat   = Category::find($catid);

    if ($cat === null)
      throw new ResourceNotFoundException('Category cannot be found');

    $product->category = $cat;

    $em->persist($product);
    $em->flush();

    return $product;
  }

  /**
   * @throw Markzero\Http\Exception\ResourceNotFoundException
   *        Exception
   */
  public static function delete($id)
  {
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
  static function getLatest()
  {
    return static::findBy(array(), array('id' => 'desc'), 12);
  }

  static function newFromGoogleBook(Book $gbook) 
  {
    $em = self::getEntityManager();
    $book = new static();

    $isbn10 = $gbook->getIsbn10();
    if ($isbn10) {
      $isbn10_entity = new Barcode($isbn10, Barcode::ISBN_10, $book);
      $book->barcodes[] = $isbn10_entity;
    }
    $isbn13 = $gbook->getIsbn13();
    if ($isbn13) {
      $isbn13_entity = new Barcode($isbn13, Barcode::ISBN_13, $book);
      $book->barcodes[] = $isbn13_entity;
    }
    $issn = $gbook->getIssn();
    if ($issn) {
      $issn_entity = new Barcode($issn, Barcode::ISSN, $book);
      $book->barcodes[] = $issn_entity;
    }

    $book->name          = $gbook->getTitle();
    $book->description   = $gbook->getDescription();
    $book->short_desc    = '';
    $book->price         = $gbook->getListPrice() ?: 0.0;
    $book->created_at = new \DateTime("now");

    return $book;
  }

  /**
   * Create new book with data from GoogleBook
   * @param App\Library\GoogleBook\Book
   * @return Product
   */
  static function createFromGoogleBook(Book $gbook) 
  {
    $book = self::newFromGoogleBook($gbook);

    $em->persist($book);
    $em->flush();

    return $book;
  }

  /**
   * Search and return a product, which has one of the input barcodes
   * @param array
   * @return App\Models\Book
   */
  public static function getDuplicateProduct(array $barcodes)
  {
    $em = self::getEntityManager();
    $query = $em->createQuery("
      SELECT p FROM App\Models\Product p 
      JOIN p.barcodes b
      WHERE b.value IN (:barcodes)");
    $query->setParameter('barcodes', $barcodes, \Doctrine\DBAL\Connection::PARAM_STR_ARRAY);
    $result = $query->getResult();

    return !empty($result) ? array_shift($result) : null;
  }

  /**
   * @return Rating | null
   */
  function ratingByUser($user)
  {
    if ($user === null)
      return null;
    $rating = Rating::findOneBy(array('user' => $user, 'product' => $this));

    return $rating;
  }

  function meanRating()
  {
    $ratings = Rating::findBy(array('product' => $this));
    $rating_sum = array_reduce($ratings, function($sum, $rating) {
      return $sum + $rating->value; 
    });

    $mean = count($ratings) > 0 ? $rating_sum / count($ratings) : 0;

    return $mean;
  }

  function positiveRatingPercent()
  {
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
  function getTopRelated($num)
  {
    $em = self::getEntityManager();

    // products other than this
    $query = $em->createQuery('SELECT b FROM App\Models\Product b WHERE b.id != :product_id');
    $query->setParameter(':product_id', $this->id);
    $all_products = $query->getResult();

    // users rated this product
    $query = $em->createQuery('
      SELECT u.id FROM App\Models\User u JOIN u.products b 
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
        SELECT u.id FROM App\Models\User u JOIN u.products b 
        WHERE b.id = :product_id GROUP BY u
      ');
      $query->setParameter(':product_id', $product->id);
      $uids_that = $query->getResult();
      $uids_that = array_flatten($uids_that);

      $uids_both = array_intersect($uids_that, $uids_this);
      $scores[$product->id] = count($uids_this) ? count($uids_both) / count($uids_this) : 0;

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
