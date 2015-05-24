<?php
use Markzero\Mvc\AppModel;
use Symfony\Component\HttpFoundation\ParameterBag;
use Markzero\Validation\Validator\RequireValidator;
use Markzero\Validation\Validator\FunctionValidator;
use Markzero\Validation\Exception\ValidationException;
use Markzero\Http\Exception\ResourceNotFoundException;

/**
 * @Entity 
 * @Table(name="products")
 **/
class Product extends AppModel {
  protected static $attr_reader = array('id');
  protected static $attr_accessor = array('name', 'barcode', 'barcode_type', 'price', 'short_desc', 'description', 'ratings');

  /** @Id @Column(type="integer") @GeneratedValue **/
  protected $id;
  /** @Column(type="string") **/
  protected $name;

  /** @Column(type="string") **/
  protected $barcode;
  /** @Column(type="string") **/
  protected $barcode_type; 
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

  public function __construct() {
    $this->ratings = new \Doctrine\Common\Collections\ArrayCollection();
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

    $vm->validate(function($vm) use ($params) {
      $name = $params->get('product[name]', '', true) ;
      $vm->register('product[name]', new FunctionValidator(function() use($name) {
        return !empty($name);
      }));
    });

    $product = new static();
    $product->name         = $params->get('product[name]', '', true);
    $product->barcode      = $params->get('product[barcode]', '', true); 
    $product->barcode_type = $params->get('product[barcode_type]', '', true); 
    $product->short_desc   = $params->get('product[short_desc]', '', true); 
    $product->description  = $params->get('product[description]', '', true); 
    $price = floatval($params->get('product[price]', 0.0, true));
    $product->price = $price;

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
    $product->barcode      = $params->get('product[barcode]', '', true); 
    $product->barcode_type = $params->get('product[barcode_type]', '', true); 
    $product->short_desc   = $params->get('product[short_desc]', '', true); 
    $product->description  = $params->get('product[description]', '', true); 
    $price = floatval($params->get('product[price]', 0.0, true));
    $product->price = $price;

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
