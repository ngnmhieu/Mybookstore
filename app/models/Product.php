<?php
use Markzero\Mvc\AppModel;
use Markzero\Validation\Validator;
use Markzero\Validation\Exception\ValidationException;
use Markzero\Http\Exception\ResourceNotFoundException;

/**
 * @Entity 
 * @Table(name="products")
 **/
class Product extends AppModel {
  protected static $attr_reader = array('id');
  protected static $attr_accessor = array('name', 'ratings', 'details');

  /** @Id @Column(type="integer") @GeneratedValue **/
  protected $id;
  /** @Column(type="string") **/
  protected $name;
  /**
   * @OneToMany(targetEntity="ProductDetail", mappedBy="product")
   */
  protected $details;
  /**
   * @OneToMany(targetEntity="Rating", mappedBy="product")
   */
  protected $ratings;
  /**
   * @ManyToMany(targetEntity="User", mappedBy="products")
   */
  protected $users;

  protected function _default() {
  }

  protected function _validate() {
    $vm = self::createValidationManager();

    $vm->register('name', new Validator\FunctionValidator(function($name) {
      return !empty($name);
    }, array($this->name)));

    $vm->doValidate();
  }

  /**
   * @throw Markzero\Validation\Exception\ValidationException
   */
  static function create($params) {
    $em = self::getEntityManager();

    $obj = new static();
    $obj->name   = $params->get('name');

    $em->persist($obj);
    $em->flush();

    return $obj;
  }

  /**
   * @throw Markzero\Http\Exception\ResourceNotFoundException
   *        Markzero\Validation\Exception\ValidationException
   */
  static function update($id, $params) {
    $em = self::getEntityManager();

    $obj = static::find($id);
    if ($obj === null) {
      throw new ResourceNotFoundException();
    }

    $obj->name = $params->get('name');

    $em->persist($obj);
    $em->flush();

    return $obj;
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

    return $rating_sum / count($ratings);
  }

  function positiveRatingPercent() {
    $ratings = Rating::findBy(array('product' => $this));

    $positive_ratings = array_filter($ratings, function($rating) {
      return ((int) $rating->value) >= 4; 
    });

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
