<?php
namespace App\Store\Models;

use Markzero\Mvc\AppModel;
use Markzero\Validation\ValidationManager;
use Markzero\Validation\Validator\FunctionValidator;

/**
 * @Entity
 * @Table(name="basket_items")
 */
class BasketItem extends AppModel
{
  protected static $readable   = ['id', 'product', 'basket'];
  protected static $accessible = ['amount'];

  /** @Id @Column(type="integer") @GeneratedValue **/
  protected $id;

  /** @Column(type="integer") **/
  protected $amount;

  /**
   * @OneToOne(targetEntity="Product")
   */
  protected $product;

  /**
   * @ManyToOne(targetEntity="Basket", inversedBy="items")
   */
  protected $basket;

  /**
   * @param Product $product
   * @param int $amount
   */
  public function __construct(Basket $basket, Product $product, $amount)
  {
    $this->basket  = $basket;
    $this->product = $product;
    $this->amount  = $amount;
  }

  protected function _validate()
  {
    ValidationManager::validate(function($vm) {

      $vm->register('amount', new FunctionValidator(function() {
        return $this->amount > 0;
      }));

    });
  }
}
