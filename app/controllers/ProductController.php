<?php
use Markzero\Mvc\View;
use Markzero\Mvc\AppController;
use Markzero\Auth\Exception\AuthenticationFailedException;
use Markzero\Auth\Exception\ActionNotAuthorizedException;
use Markzero\Http\Exception\ResourceNotFoundException;
use Markzero\Validation\Exception\ValidationException;

class ProductController extends AppController {

  public function index() {
    $products = Product::findAll();

    $this->respondTo('html', function() use ($products) {

      $data['products'] = $products;

      $this->render(new View\TwigView('product/index.html', $data));
    });

    $this->respondTo('json', function() use ($products) {
      $data = array_map(function($product) {
        return $product->toArray();
      }, $products);
      $this->render(new View\JsonView($data));
    });
  }

  public function show($id) {
    try {
      $product = Product::find($id);
      $ratings = $product->ratings;
      $top_related = $product->getTopRelated(5);

      $this->respondTo('html', function() use($product, $ratings, $top_related) {
        $data['product'] = $product;
        $data['ratings'] = array();
        $data['top_related'] = $top_related;

        foreach (Rating::$VALID_VALUES as $value) {
          $data['ratings'][$value] = array();
        }

        foreach ($ratings as $rating) {
          $data['ratings'][(int) $rating->value][] = $rating;
        }
        $this->render(new View\HtmlView($data, 'product/show'));
      });

    } catch(ResourceNotFoundException $e) {

      $this->respondTo('html', function() {
        $this->getResponse()->redirect('ProductController','index');
      });

    }

  }
}
