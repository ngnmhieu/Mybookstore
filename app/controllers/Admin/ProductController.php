<?php
namespace Admin;

use Markzero\Mvc\View\TwigView;
use App\Lib\GoogleBook;
use App\Models\Product;
use App\Models\Category;
use App\Controllers\ApplicationController;
use Markzero\Auth\Exception\AuthenticationFailedException;
use Markzero\Auth\Exception\ActionNotAuthorizedException;
use Markzero\Http\Exception\ResourceNotFoundException;
use Markzero\Validation\Exception\ValidationException;

class ProductController extends ApplicationController {

  public function index() {
    $products = Product::findAll();

    $this->respondTo('html', function() use($products) {
      $this->render(new TwigView('admin/product/index.html',compact('products')));
    });
  }

  public function create() {
    $request = $this->getRequest();
    $response = $this->getResponse();

    try {

      $product = Product::create($request->getParams());

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

  public function importGoogle() {

    $request = $this->getRequest();

    $keywords = $request->getParams()->get('keywords', null);

    if ($keywords) {

      $result = GoogleBook::search($keywords);

      $this->respondTo('html', function() use ($result) {

        $data['count'] = $result->totalItems;
        $books = [];
        foreach ($result->items as $item) {
          $book = [
            'id' => $item->id,
            'image' => $item->volumeInfo->imageLinks->smallThumbnail,
            'title' => $item->volumeInfo->title,
            'authors' => $item->volumeInfo->authors
          ];
          $books[] = $book;
        }
        $data['books'] = $books;

        $this->render(new TwigView('admin/product/import_google.html', $data));
      });

    } else {

      $this->respondTo('html', function() {

        $data['books'] = [];
        $this->render(new TwigView('admin/product/import_google.html', $data));

      });
    }

  }

  public function addFromGoogle() {
    
    $id = $this->getRequest()->getParams()->get('book_id', null);
    $gbook = new GoogleBook($id);
    
    $book = Product::createFromGoogleBook($gbook);

    $this->respondTo('html', function() use($book) {
      $this->getResponse()->redirect('Admin\ProductController','show', [$book->id]);
    });
  }

  public function show($id) {
  }

  public function add() {
    $this->respondTo('html', function() {
      $session = $this->getSession();

      $inputs = $session->getOldInputBag();
      $errors = $session->getErrorBag();
      $product = new Product();
      $categories = Category::findAll();
      $data = compact('errors', 'inputs', 'product', 'categories'); 

      $this->render(new TwigView('admin/product/add.html', $data));
    });

  }

  public function edit($id) {
    try {
      $product = Product::find($id);

      $this->respondTo('html', function() use($product) {
        $categories = Category::findAll();
        $session = $this->getSession();
        $inputs = $session->getOldInputBag();
        $errors = $session->getErrorBag();
        
        $data = compact('product','inputs', 'errors', 'categories');
        $this->render(new TwigView('admin/product/edit.html', $data));
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

      $product = Product::update($id, $request->getParams());

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
      Product::delete($id);
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
