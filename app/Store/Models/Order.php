<?php
namespace App\Store\Models;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * @Entity
 * @Table(name="orders")
 */
class Order extends \App\Models\Order 
{
  protected function _validate()
  {
    // validate order has a valid user
    // validate order has created_at updated_at
  }

  /**
   * @param User $user
   * @param array of OrderPosition (optional)
   */
  public function __construct(User $user, array $positions = [])
  {
    $this->created_at = new \DateTime("now");
    $this->updated_at = new \DateTime("now");
    $this->positions  = new ArrayCollection($positions);
    $this->user        = $user;
  }

  /**
   * @param Basket $basket basket contains items to be saved in to order
   * @param User $user user that the order belongs to
   */
  public static function create(Basket $basket, User $user)
  {
    $order = new Order($user);

    foreach ($basket->items as $item) {
      $order->positions->add(new OrderPosition($order, $item->product, $item->product->price, $item->amount));  
    }

    $em = self::getEntityManager();
    $em->persist($order);
    $em->flush();

    return $order;
  }
}
