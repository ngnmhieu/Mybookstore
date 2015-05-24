<?php
use Markzero\Mvc\View\TwigView;
use Markzero\Mvc\View\JsonView;
use Markzero\Mvc\View\HtmlView;
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

      $this->render(new TwigView('product/index.html', $data));
    });

    $this->respondTo('json', function() use ($products) {
      $data = array_map(function($product) {
        return $product->toArray();
      }, $products);
      $this->render(new JsonView($data));
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

        $count = array();
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
        $this->getResponse()->redirect('ProductController','index');
      });

    }

  }
}
