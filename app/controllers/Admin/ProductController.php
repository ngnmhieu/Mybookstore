<?php
namespace Admin;

use Markzero\Mvc\View\HtmlView;
use Markzero\Mvc\AppController;
use Markzero\Auth\Exception\AuthenticationFailedException;
use Markzero\Auth\Exception\ActionNotAuthorizedException;
use Markzero\Http\Exception\ResourceNotFoundException;
use Markzero\Validation\Exception\ValidationException;

class ProductController extends AppController {

  public function index() {
    $products = \Product::findAll();

    $this->respondTo('html', function() use($products) {
      $this->render(new HtmlView(compact('products'), 'admin/product/index'));
    });
  }

  public function create() {
    $request = $this->getRequest();
    $response = $this->getResponse();

    try {

      $product = \Product::create($request->getParams());

      $this->respondTo('html', function() use($response) {
        $response->redirect('Admin\ProductController', 'index');
      });

    } catch(ValidationException $e) {

      $flash = $this->getSession()->getFlashBag();

      $flash->set('errors', $e->getErrors());
      $flash->set('inputs', $request->getParams()->all());

      $this->respondTo('html', function() use($response) {
        $response->redirect('Admin\ProductController', 'add');
      });

    }
  }

  public function show($id) {
  }

  public function add() {
    $this->respondTo('html', function() {
      $session = $this->getSession();

      $inputs = $session->getOldInputBag();
      $errors = $session->getErrorBag();

      $this->render(new HtmlView(compact('errors', 'inputs'), 'admin/product/add'));
    });

  }

  public function edit($id) {
    try {
      $product = \Product::find($id);

      $this->respondTo('html', function() use($product) {
        $session = $this->getSession();
        $inputs = $session->getOldInputBag();
        $errors = $session->getErrorBag();
        
        $data = compact('product','inputs', 'errors');
        $this->render(new HtmlView($data, 'admin/product/edit'));
      });

    } catch(ResourceNotFoundException $e) {

      $this->respondTo('html', function() use($id) {
        $this->getResponse()->redirect('Admin\ProductController', 'index');
      });

    }
    
  }

  public function update($id) {

    $flash = $this->getSession()->getFlashBag();
    $request = $this->getRequest();

    try {

      $product = \Product::update($id, $request->getParams());

      $this->respondTo('html', function() use($id) {
        $this->getResponse()->redirect('Admin\ProductController', 'edit', array($id));
      });

    } catch(ResourceNotFoundException $e) {

      $flash->set('inputs', $request->getParams()->all());

      $this->respondTo('html', function() use($id) {
        $this->getResponse()->redirect('Admin\ProductController', 'edit', array($id));
      });

    } catch(ValidationException $e) {

      $flash->set('errors', $e->getErrors());
      $flash->set('inputs', $request->getParams()->all());

      $this->respondTo('html', function() use($id) {
        $this->getResponse()->redirect('Admin\ProductController', 'edit', array($id));
      });

    }
  }

  function delete($id) {
    try {
      \Product::delete($id);
      $this->respondTo('html', function() {
        $this->getResponse()->redirect('Admin\ProductController', 'index');
      });

    } catch(ResourceNotFoundException $e) {
      
      $this->respondTo('html', function() {
        $this->getResponse()->redirect('Admin\ProductController', 'index');
      });

    } catch(\Exception $e) {
      $this->respondTo('html', function() {
        $this->getResponse()->redirect('Admin\ProductController', 'index');
      });
    }
  }

}
