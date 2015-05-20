<?php

use Markzero\Mvc\AppModel;
use Markzero\Validation\Validator\RequireValidator;
use Markzero\Validation\Validator\FunctionValidator;
use Markzero\Validation\Exception\ValidationException;
use Markzero\Http\Exception\ResourceNotFoundException;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * @Entity
 * @Table(name="product_details")
 */
class ProductDetail extends AppModel {

  static protected $attr_reader = array('id');
  static protected $attr_accessor = array('title', 'barcode', 'barcode_type', 'price', 'short_desc', 'description', 'product');

  /** @Id @Column(type="integer") @GeneratedValue **/
  protected $id;
  /** @Column(type="string") **/
  protected $title;
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
   * @ManyToOne(targetEntity="Product", inversedBy="details")
   */
  protected $product;

  protected function _default() {
  }

  protected function _validate() {
    $vm = self::createValidationManager();

    $vm->validate(function($vm) {
      $vm->register('title', new RequireValidator($this->title), 'Title is required');
      $vm->register('title', new FunctionValidator(function() {
        return strlen($this->title) >= 3;
      }), 'Title must at least 3 characters');
    });
  }

  /**
   * @throw Markzero\Validation\Exception\ValidationException
   *        Markzero\Http\Exception\ResourceNotFoundException
   */
  static function create($product_id, ParameterBag $params) {
    $em = self::getEntityManager();

    $product = Product::find($product_id);

    if ($product == null) {
      throw new ResourceNotFoundException();
    }

    $detail = new static();

    $detail->title        = $params->get('title', ''); 
    $detail->barcode      = $params->get('barcode', ''); 
    $detail->barcode_type = $params->get('barcode_type', ''); 
    $price = floatval($params->get('price', 0.0));
    $detail->price        = $price;
    $detail->short_desc   = $params->get('short_desc', ''); 
    $detail->description  = $params->get('description', ''); 
    $detail->product      = $product;

    $em->persist($detail);
    $em->flush();

    return $detail;
  }

  /**
   * @throw Markzero\Http\Exception\ResourceNotFoundException
   *        Exception
   */
  static function delete($id) {
    $em = self::getEntityManager();
    $detail = ProductDetail::find($id);
    if ($detail === null) {
      throw new ResourceNotFoundException();
    }

    $conn = $em->getConnection();
    $conn->beginTransaction();

    try {
      $em->remove($detail); 
      $em->flush();

      $conn->commit();
    } catch(Exception $e) {
      $conn->rollback();
      throw $e;
    }
  }

}
