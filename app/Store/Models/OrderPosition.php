<?php
namespace App\Store\Models;

/**
 * @Entity
 * @Table(name="order_positions")
 */
class OrderPosition extends \App\Models\OrderPosition
{
  protected function _validate()
  {
  }

  /**
   * @param Order $order
   * @param Product $product
   * @param double $price > 0.0
   * @param int $amount > 0
   */
  public function __construct(Order $order, Product $product, $price, $amount)
  {
    $this->order   = $order;
    $this->product = $product;
    $this->price   = $price;
    $this->amount  = $amount;
  }
}
