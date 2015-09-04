<?php
namespace App\Admin\Models; 

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
   * @param Author 
   * @throws DuplicateResourceException
   */
  public function addAuthor(Author $author)
  {
    $em = self::getEntityManager();

    if ($this->authors->contains($author)) {

      throw new DuplicateResourceException();

    } else {

      $this->authors[] = $author; 
      $author->books[] = $this; 
      $em->persist($this);
      $em->flush();
    }
  }

  /**
   * @param Author
   * @return boolean
   */
  public function removeAuthor(Author $author)
  {

    if (!$author->books->contains($this) || !$this->authors->contains($author)) {
      return false;  
    }

    $em = self::getEntityManager();
    $this->authors->removeElement($author);
    $author->books->removeElement($this);

    $em->persist($this);
    $em->flush();

    return true;
  }

  /**
   * @param 
   * @throw Markzero\Validation\Exception\ValidationException
   * @return Product
   */
  public static function create(ParameterBag $params) 
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

    if ($images = $params->get('product[remote_images]', null, true)) {
      foreach ($images as $img_src) {
        $img = new Image($product, $img_src, Image::TYPE_REMOTE);
        $em->persist($img);
        $product->images[] = $img;
      }
    }

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
  public static function update($id, ParameterBag $params)
  {
    $em = self::getEntityManager();

    $product = Product::find($id);

    self::validateParams($params, $product);

    if ($product === null) 
      throw new ResourceNotFoundException();

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

  public static function newFromGoogleBook(Book $gbook) 
  {
    $em = self::getEntityManager();
    $book = new Product();

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

    $book->name        = $gbook->getTitle();
    $book->description = $gbook->getDescription();
    $book->short_desc  = '';
    $book->price       = $gbook->getListPrice() ?: 0.0;
    $book->created_at  = new \DateTime("now");
    $image             = new Image($book, $gbook->getImage(), Image::TYPE_REMOTE);
    $book->images[]    = $image;
    $em->persist($image);

    return $book;
  }

  /**
   * Create new book with data from GoogleBook
   * @param App\Library\GoogleBook\Book
   * @return Product
   */
  public static function createFromGoogleBook(Book $gbook) 
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
      SELECT p FROM App\Admin\Models\Product p 
      JOIN p.barcodes b
      WHERE b.value IN (:barcodes)");
    $query->setParameter('barcodes', $barcodes, \Doctrine\DBAL\Connection::PARAM_STR_ARRAY);
    $result = $query->getResult();

    return !empty($result) ? array_shift($result) : null;
  }

}
