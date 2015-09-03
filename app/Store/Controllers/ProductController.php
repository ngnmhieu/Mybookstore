<?php
namespace App\Store\Controllers;

use App\Models\Product; 
use App\Models\Rating; 
use App\Models\UserSession; 
use App\Models\User;
use App\Controllers\ApplicationController;
use Markzero\Mvc\View\TwigView;
use Markzero\Mvc\View\JsonView;
use Markzero\Mvc\View\HtmlView;
use Markzero\Auth\Exception\AuthenticationFailedException;
use Markzero\Auth\Exception\ActionNotAuthorizedException;
use Markzero\Http\Exception\ResourceNotFoundException;
use Markzero\Validation\Exception\ValidationException;

class ProductController extends ApplicationController 
{

  public function getUserReplacements()
  {
    $replacements = [];

    $user_session = UserSession::getInstance();

    $signed_in = $user_session->isSignedIn();

    $replacements['user']         = $signed_in ?  $user_session->getUser() : new User();
    $replacements['is_signed_in'] = $signed_in;

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

  public function show($id) {
    try {
      $product = Product::find($id);
      $ratings = $product->ratings;
      $top_related = $product->getTopRelated(5);

      $this->respondTo('html', function() use($product, $ratings, $top_related) {
        $data['product'] = $product;
        $data['ratings'] = [];
        $data['top_related'] = $top_related;

        $count = [];
        foreach (Rating::$VALID_VALUES as $value) {
          $count[$value] = 0;
        }

        foreach ($ratings as $rating) {
          ++$count[$rating->value];
        }

        foreach (Rating::$VALID_VALUES as $value) {
          $rating = array(
            'value' => $value,
            'count' => $count[$value]
          );

          $data['ratings'][] = $rating;
        }

        $this->render(new TwigView('product/show.html',$data));
      });

    } catch(ResourceNotFoundException $e) {

      $this->respondTo('html', function() {
        $this->getResponse()->redirect('App\Store\Controllers\ProductController','index');
      });

    }

  }
}
