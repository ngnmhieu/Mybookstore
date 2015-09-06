<?php
namespace App\Store\Controllers;

use App\Store\Models\Basket;
use App\Store\Models\UserSession;
use App\Store\Models\Order;
use App\Controllers\ApplicationController;
use Markzero\Mvc\View\TwigView;
use Markzero\Mvc\View\JsonView;
use Markzero\Mvc\View\HtmlView;
use Markzero\Auth\Exception\AuthenticationFailedException;
use Markzero\Auth\Exception\ActionNotAuthorizedException;
use Markzero\Http\Exception\ResourceNotFoundException;
use Markzero\Http\Exception\DuplicateResourceException;
use Markzero\Validation\Exception\ValidationException;

class BasketController extends StoreController 
{
  public function index()
  {
    $this->respondTo('html', function() {

      $basket = Basket::getInstance(UserSession::getInstance());

      $data = $this->getCommonData();

      if ($basket->isEmpty()) {

        $this->render(new TwigView('basket/empty.html', $data));

      } else {

        $this->render(new TwigView('basket/index.html', $data));
      }

    });
  }

  public function addItem()
  {
    $params    = $this->getRequest()->getParams();
    $productId = $params->get('product_id');

    $this->respondTo('html', function() use($productId) {
      try {
        $basket = Basket::getInstance(UserSession::getInstance());

        $basket->addItem($productId);

        $this->getResponse()->redirect('App\Store\Controllers\BasketController', 'index');

      } catch (ResourceNotFoundException $e) {

        $this->getResponse()->redirect('App\Store\Controllers\ProductController', 'show', [$productId]);
      }
    });
  }
  
  public function removeItem($itemId)
  {
    $this->respondTo('html', function() use($itemId) {
      try {
        $basket = Basket::getInstance(UserSession::getInstance());

        $basket->removeItem($itemId);

        $this->getResponse()->redirect('App\Store\Controllers\BasketController', 'index');

      } catch (ResourceNotFoundException $e) {

        $this->getResponse()->redirect('App\Store\Controllers\BasketController', 'index');
      }
    });
  }

  public function update()
  {
    $this->respondTo('html', function() {
      try {
        
        $params = $this->getRequest()->getParams();

        $itemQtyParams = $params->get('items');

        $itemQtys = [];
        foreach ($itemQtyParams as $id => $amount) {
          $itemQtys[(int) $id] = (int) $amount;
        }

        $basket = Basket::getInstance(UserSession::getInstance());

        $basket->updateItemsQty($itemQtys);

        $this->getResponse()->redirect('App\Store\Controllers\BasketController', 'index');

      } catch (ResourceNotFoundException $e) {

        $this->getResponse()->redirect('App\Store\Controllers\BasketController', 'index');
      }
    });
  }

  public function confirm()
  {
    $this->respondTo('html', function() {

      $basket = Basket::getInstance(UserSession::getInstance());

      if ($basket->isEmpty()) {
        $this->getResponse()->redirect('App\Store\Controllers\BasketController', 'index');
        return;
      }

      $data = $this->getCommonData();

      $this->render(new TwigView('basket/confirmation.html', $data));
    });
  }

  public function buy()
  {
    $this->respondTo('html', function() {

      try {
        $userSession = UserSession::getInstance();

        if (!$userSession->isSignedIn())
          throw new ActionNotAuthorizedException();

        $basket = Basket::getInstance($userSession);

        if ($basket->isEmpty()) {
          $this->getResponse()->redirect('App\Store\Controllers\BasketController', 'index');
          return;
        }

        $order = Order::create($basket, $userSession->getUser());

        $basket->clear();

        $data = array_merge($this->getCommonData(), ['order' => $order]);

        $this->render(new TwigView('basket/success.html', $data));

      } catch (ActionNotAuthorizedException $e) {

        $this->getResponse()->redirect('App\Auth\Controllers\AuthController', 'signIn');
      }
    });
  }
}
