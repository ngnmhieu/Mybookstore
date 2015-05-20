<?php
use Markzero\Mvc\View\HtmlView;
use Markzero\Mvc\AppController;
use Markzero\Auth\Exception\AuthenticationFailedException;
use Markzero\Auth\Exception\ActionNotAuthorizedException;
use Markzero\Http\Exception\ResourceNotFoundException;
use Markzero\Validation\Exception\ValidationException;
use Symfony\Component\HttpFoundation\ParameterBag;

class ProductDetailController extends AppController {

  public function add($product_id) {
    $product = Product::find($product_id);

    $this->respondTo('html', function() use($product) {

      $flash = $this->getSession()->getFlashBag();

      $errors = $flash->has('errors') ? new ParameterBag($flash->get('errors')) : new ParameterBag();
      $inputs = $flash->has('inputs') ? new ParameterBag($flash->get('inputs')) : new ParameterBag();

      $this->render(new HtmlView(compact('product', 'errors', 'inputs'), 'product/add_productdetail'));
    });
  }

  public function create($product_id) {
    $request = $this->getRequest();
    $response = $this->getResponse();

    try {

      ProductDetail::create($product_id, $request->getParams());

      $this->respondTo('html', function() use($product_id, $response) {
        $response->redirect('ProductController', 'edit', [$product_id]);
      });

    } catch(ValidationException $e) {

      $this->respondTo('html', function() use($product_id, $response, $request,$e) {

        $flash = $this->getSession()->getFlashBag();

        $flash->set('errors', $e->getErrors());
        $flash->set('inputs', $request->getParams()->all());

        $response->redirect('ProductDetailController', 'add', [$product_id]);
      });

    } catch(ResourceNotFoundException $e) {
    }

  }

  function delete($product_id, $id) {
    try {
      ProductDetail::delete($id);
      $this->respondTo('html', function() use($product_id) {
        $this->getResponse()->redirect('ProductController', 'edit', [$product_id]);
      });

    } catch(ResourceNotFoundException $e) {
      
      $this->respondTo('html', function() use($product_id) {
        $this->getResponse()->redirect('ProductController', 'edit', [$product_id]);
      });
    }

  }
}
