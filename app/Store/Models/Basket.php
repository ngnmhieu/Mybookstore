<?php
namespace App\Store\Models;

use Markzero\Mvc\AppModel;
use Doctrine\Common\Collections\ArrayCollection;
use Markzero\Http\Exception\ResourceNotFoundException;

/**
 * A shopping basket
 * @Entity
 * @Table(name="basket")
 */
class Basket extends AppModel
{
  protected static $readable = ['id', 'items', 'total', 'userSession'];

  /** @Id @Column(type="integer") @GeneratedValue **/
  protected $id;

  /**
   * @OneToMany(targetEntity="BasketItem", mappedBy="basket", cascade={"persist", "remove"})
   */
  protected $items;

  /**
   * @OneToOne(targetEntity="UserSession")
   * @JoinColumn(name="user_session_id", referencedColumnName="id")
   */
  protected $userSession;

  protected function __construct() { }

  protected function _validate() { }
  /**
   * @param UserSession $userSession  
   * @return Basket
   */
  public static function getInstance(UserSession $userSession)
  {
    $basket = self::findOneBy(['userSession' => $userSession]);

    if ($basket == null)
      $basket = self::createBasket($userSession);

    return $basket;
  }

  /**
   * @param $userSession UserSession which the basket belongs to
   * @return Basket|null if there is no UserSession with $usid, null is returned
   */
  protected static function createBasket(UserSession $userSession)
  {
    $basket              = new Basket();
    $basket->items       = new ArrayCollection();
    $basket->userSession = $userSession;

    $em = self::getEntityManager();
    $em->persist($basket);
    $em->flush();

    return $basket;
  }

  /**
   * @return double
   */
  public function getTotal()
  {
    $items = $this->items->toArray();

    return array_reduce($items, function($total, $item) {
      return $total + ($item->product->price * $item->amount);
    }, 0.0);
  }

  /**
   * @param int $productId 
   * @param int $amount amount to add 
   * @throw ResourceNotFoundException if product not found
   */
  public function addItem($productId, $amount = 1)
  {
    $product = Product::find($productId);

    if ($product == null)
      throw new ResourceNotFoundException();

    // find existing basket item
    $item = BasketItem::findOneBy(['product' => $product, 'basket' => $this]);

    if ($item != null) {
      $item->amount += 1;
    } else {
      $this->items->add(new BasketItem($this, $product, $amount));
    }
      

    $em = self::getEntityManager();
    $em->persist($this);
    $em->flush();
  }

  /**
   * @param int $itemId ID of item to be removed
   * @throw ResourceNotFoundException if item not found
   */
  public function removeItem($itemId)
  {
    $item = BasketItem::find($itemId);

    if ($item == null)
      throw new ResourceNotFoundException();

    if ($item->basket->id == $this->id)
      $item->destroy();
  }

  /**
   * Update the quantity of the items in basket
   * @param array contains key as itemId, value as quantity
   * [ 2 => 1, 4 => 3 ]
   */
  public function updateItemsQty($itemQtys)
  {
    foreach ($this->items as $item) {
      if (array_key_exists($item->id, $itemQtys) && $itemQtys[$item->id] > 0)
        $item->amount = $itemQtys[$item->id];
    }

    $em = self::getEntityManager();
    $em->persist($this);
    $em->flush();
  }

  /**
   * @return bool is basket empty
   */
  public function isEmpty()
  {
    return count($this->items) == 0;
  }

  /**
   * Empty the basket
   */
  public function clear()
  {
    $this->destroy();
  }
}
