<?php
namespace App\Store\Controllers;

use App\Store\Models\Product; 
use App\Store\Models\Rating; 
use App\Store\Models\UserSession; 
use App\Store\Models\User;
use App\Controllers\ApplicationController;
use Markzero\Mvc\View\TwigView;
use Markzero\Mvc\View\JsonView;
use Markzero\Mvc\View\HtmlView;
use Markzero\Auth\Exception\AuthenticationFailedException;
use Markzero\Auth\Exception\ActionNotAuthorizedException;
use Markzero\Http\Exception\ResourceNotFoundException;
use Markzero\Http\Exception\DuplicateResourceException;
use Markzero\Validation\Exception\ValidationException;

class ProductController extends ApplicationController 
{

  public function getUserReplacements()
  {
    $replacements = [];

    $userSession = UserSession::getInstance();

    $signedIn = $userSession->isSignedIn();

    $replacements['user']         = $signedIn ?  $userSession->getUser() : new User();
    $replacements['is_signed_in'] = $signedIn;

    return $replacements;
  }

  public function index() 
  {

    $this->respondTo('html', function() {

      $latests = Product::getLatest(12);

      $data = [
        'latests'  => $latests
      ];

      $user_replacements = $this->getUserReplacements();

      $data = array_merge($user_replacements, $data);

      $this->render(new TwigView('product/index.html', $data));
    });
  }

  public function show($id) 
  {
    try {
      $this->respondTo('html', function() use($id) {

        $product      = Product::find($id);
        $topRelated   = $product->getTopRelated(6);
        $ratingScalar = Rating::getScalar();

        $data = [
          'product'       => $product,
          'top_related'   => $topRelated,
          'rating_scalar' => $ratingScalar,
          'ratings'       => []
        ];

        $count = [];
        foreach ($ratingScalar as $value) {
          $count[$value] = 0;
        }

        foreach ($product->ratings as $rating) {
          ++$count[$rating->value];
        }

        foreach ($ratingScalar as $value) {

          $rating = array(
            'value' => $value,
            'count' => $count[$value]
        );

          $data['ratings'][] = $rating;
        }


        $data = array_merge($this->getUserReplacements(), $data);

        $this->render(new TwigView('product/show.html',$data));
      });

    } catch(ResourceNotFoundException $e) {

      $this->respondTo('html', function() {
        $this->getResponse()->redirect('App\Store\Controllers\ProductController','index');
      });

    }
  }

  public function rate($id)
  {
    $this->respondTo('html', function() use($id) {

      try {
        $userSession = UserSession::getInstance();
        if (!$userSession->isSignedIn())
          throw new ActionNotAuthorizedException();

        Rating::create($userSession->getUser(), Product::find($id), $this->getRequest()->getParams());

        $this->getResponse()->redirect('App\Store\Controllers\ProductController','show', [$id]);

      } catch (ResourceNotFoundException $e) {

        $this->getResponse()->redirect('App\Store\Controllers\ProductController','show', [$id]);

      } catch (ValidationException $e) {

        $this->getResponse()->redirect('App\Store\Controllers\ProductController','show', [$id]);

      } catch (DuplicateResourceException $e) {

        $this->getResponse()->redirect('App\Store\Controllers\ProductController','show', [$id]);

      } catch (ActionNotAuthorizedException $e) {

        $this->getResponse()->redirect('App\Store\Controllers\ProductController','show', [$id]);
      }

    });

  }
}
