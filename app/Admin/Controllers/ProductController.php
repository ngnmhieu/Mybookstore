<?php
namespace App\Admin\Controllers;

use Markzero\Mvc\View\TwigView;
use App\Libraries\GoogleBook\BookRequest;
use App\Libraries\GoogleBook\BookRequestParameter;
use App\Models\Product;
use App\Models\Category;
use App\Controllers\ApplicationController;
use Markzero\Auth\Exception\AuthenticationFailedException;
use Markzero\Auth\Exception\ActionNotAuthorizedException;
use Markzero\Http\Exception\ResourceNotFoundException;
use Markzero\Validation\Exception\ValidationException;

class ProductController extends ApplicationController 
{

  public function index()
  {
    $products = Product::findAll();

    $this->respondTo('html', function() use($products) {
      $this->render(new TwigView('admin/product/index.html', compact('products')));
    });
  }

  public function create() 
  {
    $request = $this->getRequest();
    $response = $this->getResponse();

    try {

      $product = Product::create($request->getParams());

      $this->respondTo('html', function() use($response) {
        $response->redirect('App\Admin\Controllers\ProductController', 'index');
      });

    } catch(ValidationException $e) {

      $flash = $this->getSession()->getFlashBag();

      $flash->set('errors', $e->getErrors());
      $flash->set('inputs', $request->getParams()->all());

      $this->respondTo('html', function() use($response) {
        $response->redirect('App\Admin\Controllers\ProductController', 'add');
      });

    }
  }

  public function searchGoogleBook() 
  {
    $request = $this->getRequest();
    $params = $request->getParams();

    /** Construct parameters **/
    $keywords = $params->get('keywords', null);

    $special_fields = array(
      'intitle' => $params->get('intitle', null),
      'inauthor' => $params->get('inauthor', null),
      'inpublisher' => $params->get('inpublisher', null),
      'insubject' => $params->get('insubject', null),
      'isbn' => $params->get('isbn', null),
    );

    $language = $params->get('language', null);


    $book_parameter = new BookRequestParameter($keywords, $special_fields, $language);

    $this->respondTo('html', function() use ($book_parameter, $params) {

      /** Pagination **/
      $item_per_page = 20;

      $page = (int) $params->get('page', 1);
      $page = $page > 0 ? $page : 1;

      $total  = BookRequest::getTotal($book_parameter);
      $offset = ($page-1) * $item_per_page;

      $book_parameter->setLimit($item_per_page);
      $book_parameter->setOffset($offset);

      /** Build Next/Prev links **/
      $build_link = function($params) {
        $query_parts = array();
        foreach ($params as $k => $v) {
          $query_parts[] = urlencode($k).'='.urlencode($v);
        }
        return implode('&', $query_parts);
      };

      $nextpage_params = $params->all();
      $nextpage_params['page'] = $page + 1;

      $prevpage_params = $params->all();
      $prevpage_params['page'] = $page > 1 ? $page - 1 : 1;

      $next_link = webpath('Admin::ProductController','searchGoogleBook').'?'.$build_link($nextpage_params);
      $prev_link = webpath('Admin::ProductController','searchGoogleBook').'?'.$build_link($prevpage_params);
      

      $data = array(
        'books_total' => $total,
        'books'       => BookRequest::search($book_parameter),
        'start_item'  => $offset,
        'end_item'    => $offset + $item_per_page,
        'next_link'   => $next_link,
        'prev_link'   => $prev_link
      );

      $data['languages'] = BookRequest::getAvailableLanguages();

      $data['params'] = $params;

      $this->render(new TwigView('admin/product/search_google_book.html', $data));
    });
  }

  public function addFromGoogle($id) 
  {
    $this->respondTo('html', function() use ($id) {
      $response = $this->getResponse();
      $session  = $this->getSession();

      try {
        $gbook = BookRequest::get($id);

        $duplicate = Product::getDuplicateProduct([$gbook->getIsbn10(), $gbook->getIsbn13(), $gbook->getIssn()]);
        if ($duplicate) {
          // $response->redirect('');
          return $this;
        }

        $product = Product::newFromGoogleBook($gbook);

        $categories = Category::findAll();
        $inputs = $session->getOldInputBag();
        $errors = $session->getErrorBag();
        
        $this->render(new TwigView('admin/product/add.html', compact('product', 'inputs', 'categories', 'errors')));
    
      } catch (\RuntimeException $e) {
        $flash_bag = $session->getFlashBag();
        $flash_bag->set('errors', [$e->getMessage()]);

        $response->redirect('App\Admin\Controllers\ProductController','searchGoogleBook');
      }

    });

  }

  public function saveFromGoogle()
  {
    $id = $this->getRequest()->getParams()->get('book_id', null);

    $gbook = BookRequest::get($id);

    try {
      $book = Product::createFromGoogleBook($gbook);

      $this->respondTo('html', function() use($book) {
        $this->getResponse()->redirect('App\Admin\Controllers\ProductController','show', [$book->id]);
      });

    } catch(ValidationException $e) {

      $this->respondTo('html', function() use($book) {
        $this->getResponse()->redirect('App\Admin\Controllers\ProductController','searchGoogleBook');
      });

    }
  }

  public function show($id) 
  {
  }

  public function add() 
  {
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

  public function edit($id) 
  {
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
        $this->getResponse()->redirect('App\Admin\Controllers\ProductController', 'index');
      });

    }
    
  }

  public function update($id) 
  {

    $flash = $this->getSession()->getFlashBag();
    $request = $this->getRequest();

    try {

      $product = Product::update($id, $request->getParams());

      $this->respondTo('html', function() use($id) {
        $this->getResponse()->redirect('App\Admin\Controllers\ProductController', 'edit', array($id));
      });

    } catch(ResourceNotFoundException $e) {

      $flash->set('inputs', $request->getParams()->all());

      $this->respondTo('html', function() use($id) {
        $this->getResponse()->redirect('App\Admin\Controllers\ProductController', 'edit', array($id));
      });

    } catch(ValidationException $e) {

      $flash->set('errors', $e->getErrors());
      $flash->set('inputs', $request->getParams()->all());

      $this->respondTo('html', function() use($id) {
        $this->getResponse()->redirect('App\Admin\Controllers\ProductController', 'edit', array($id));
      });

    }
  }

  function delete($id) 
  {
    try {
      Product::delete($id);
      $this->respondTo('html', function() {
        $this->getResponse()->redirect('App\Admin\Controllers\ProductController', 'index');
      });

    } catch(ResourceNotFoundException $e) {
      
      $this->respondTo('html', function() {
        $this->getResponse()->redirect('App\Admin\Controllers\ProductController', 'index');
      });

    } catch(\Exception $e) {
      $this->respondTo('html', function() {
        $this->getResponse()->redirect('App\Admin\Controllers\ProductController', 'index');
      });
    }
  }

}
