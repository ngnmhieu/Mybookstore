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

    $user_ratings = array();
    $user = UserSession::getUser();
    foreach ($products as $product) {
      $rating = $product->ratingByUser($user);
      $user_ratings[$product->id] = !$rating ? null : array(
        'id' => $rating->id,
        'value' => $rating->value
      );
    }

    $this->respondTo('html', function() use ($products, $user_ratings) {
      $data['products'] = $products;
      $data['rating_values'] = Rating::$VALID_VALUES;
      $data['user_ratings'] = $user_ratings;

      $this->render(new View\HtmlView($data, 'product/index'));
    });

    $this->respondTo('json', function() use ($products) {
      $data = array_map(function($product) {
        return $product->toArray();
      }, $products);
      $this->render(new View\JsonView($data));
    });
  }

  public function create() {
    try {
      $product = Product::create($this->getRequest()->request);

      $this->respondTo('html', function() {
        $this->getResponse()->redirect('ProductController', 'index');
      });
    } catch(ValidationException $e) {

      $flash = $this->getSession()->getFlashBag();

      $flash->set('errors', $e->getErrors());
      $flash->set('inputs', $this->getRequest()->getParams()->all());

      $this->respondTo('html', function() {
        $this->getResponse()->redirect('ProductController', 'add');
      });

    }
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

  public function add() {

    $this->respondTo('html', function() {
      $flash = $this->getSession()->getFlashBag();
      $this->render(new View\HtmlView(array(), 'product/add'));
    });

  }

  public function edit($id) {
    try {
      $product = Product::find($id);

      $this->respondTo('html', function() use($product) {
        $data['product'] = $product;
        $this->render(new View\HtmlView($data, 'product/edit'));
      });

    } catch(ResourceNotFoundException $e) {

      $this->respondTo('html', function() use($id) {
        $this->getResponse()->redirect('ProductController', 'index');
      });

    }
    
  }

  public function update($id) {
    try {

      $product = Product::update($id, $this->getRequest()->getParams());

      $this->respondTo('html', function() {
        $this->getResponse()->redirect('ProductController', 'index');
      });

    } catch(ResourceNotFoundException $e) {

      $this->respondTo('html', function() use($id) {
        $this->getResponse()->redirect('ProductController', 'edit', array($id));
      });

    } catch(ValidationException $e) {

      $this->respondTo('html', function() use($id) {
        $this->getResponse()->redirect('ProductController', 'edit', array($id));
      });

    }
  }

  function delete($id) {
    try {
      Product::delete($id);
      $this->respondTo('html', function() {
        $this->getResponse()->redirect('ProductController', 'index');
      });

      $this->respondTo('json', function() {
        $this->getResponse()->setStatusCode(Response::HTTP_OK, 'Transaction deleted');
      });

    } catch(ResourceNotFoundException $e) {
      
      $this->respondTo('html', function() {
        $this->getResponse()->redirect('ProductController', 'index');
      });

    } catch(\Exception $e) {
      $this->respondTo('html', function() {
        $this->getResponse()->redirect('ProductController', 'index');
      });

      $this->respondTo('json', function() use($e) {
        $this->getResponse()->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR, '[Error] Transaction could not be deleted: '.$e->getMessage());
      });
    }
  }

  function updateRate($product_id, $id) {
    if (UserSession::isSignedIn()) {
      try {
        $rating = Rating::find($id);
        if ($rating === null)
          throw new ResourceNotFoundException();

        if ($rating->user !== UserSession::getUser()) {
          throw new ActionNotAuthorizedException();
        }

        Rating::update($id, $this->getRequest()->request);

      $this->respondTo('html', function() {
        $this->getResponse()->redirect('ProductController', 'index');
      });

      } catch(ActionNotAuthorizedException $e) {

        $this->respondTo('html', function() {
          $this->getResponse()->redirect('ProductController', 'index');
        });
      } catch(ValidationException $e) {
        
        $this->respondTo('html', function() {
          $this->getResponse()->redirect('ProductController', 'index');
        });

      } catch(ResourceNotFoundException $e) {

        $this->respondTo('html', function() {
          $this->getResponse()->redirect('ProductController', 'index');
        });

      }
    } else {
     
      $this->respondTo('html', function() {
        $this->getResponse()->redirect('ProductController', 'index');
      });

    }
  }

  function rate($id) {
    if (UserSession::isSignedIn()) {
      try {
        $product = Product::find($id);
        $user = UserSession::getUser();

        Rating::create($user, $product, $this->getRequest()->request);

      $this->respondTo('html', function() {
        $this->getResponse()->redirect('ProductController', 'index');
      });

      } catch(ValidationException $e) {
        
        $this->respondTo('html', function() {
          $this->getResponse()->redirect('ProductController', 'index');
        });

      } catch(ResourceNotFoundException $e) {

        // Product or User not found
        $this->respondTo('html', function() {
          $this->getResponse()->redirect('ProductController', 'index');
        });

      }
    } else {

      $this->respondTo('html', function() {
        // User not signed in
        $this->getResponse()->redirect('ProductController', 'index');
      });

    }
  }

}
