<?php
namespace App\Store\Models; 

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
 * @Entity 
 * @Table(name="products")
 **/
class Product extends \App\Models\Product 
{
  public function __construct() 
  {
    $this->ratings  = new ArrayCollection();
    $this->barcodes = new ArrayCollection();
    $this->authors  = new ArrayCollection();
    $this->images   = new ArrayCollection();
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
   * @return string name
   */
  public function getAuthorNames()
  {
    return array_map(function($author) { 
      return $author->name; 
    }, $this->authors->toArray());
  }

  /**
   * @return Image|null thumbnail of the book
   *         null if no image is found
   */
  public function getThumb()
  {
    if ($this->images->isEmpty())
      return null;

    if ($this->thumb == null)
      $this->thumb = $this->images->first();

    return $this->thumb;
  }

  /**
   * Return latest books
   * @param int $count number of books to return
   */
  static function getLatest($count = null)
  {
    return static::findBy([], ['id' => 'desc'], $count ?: 20);
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
    $query = $em->createQuery('SELECT b FROM App\Store\Models\Product b WHERE b.id != :product_id');
    $query->setParameter(':product_id', $this->id);
    $all_products = $query->getResult();

    // users rated this product
    $query = $em->createQuery('
      SELECT u.id FROM App\Store\Models\User u JOIN u.products b 
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
        SELECT u.id FROM App\Store\Models\User u JOIN u.products b 
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
