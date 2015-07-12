<?php
namespace App\Admin\Controllers;

use Markzero\Mvc\View\TwigView;
use App\Libraries\GoogleBook\BookRequest;
use App\Libraries\GoogleBook\BookRequestParameter;
use App\Models\Product;
use App\Models\Image;
use App\Models\Author;
use App\Models\Category;
use App\Controllers\ApplicationController;
use Markzero\Auth\Exception\AuthenticationFailedException;
use Markzero\Auth\Exception\ActionNotAuthorizedException;
use Markzero\Http\Exception\ResourceNotFoundException;
use Markzero\Http\Exception\DuplicateResourceException;
use Markzero\Validation\Exception\ValidationException;

class ProductController extends ApplicationController 
{
  public function index()
  {
    $products = Product::findAll();

    $this->respondTo('html', function() use($products) {

      $data = [
        'page_title' => 'Admin Panel - Product List',
        'products'   => $products
      ];

      $this->render(new TwigView('admin/product/index.html', $data));
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
      
      $keywords  = $params->get('keywords', null);

      $data = array(
        'books_total' => $total,
        'books'       => BookRequest::search($book_parameter),
        'start_item'  => $offset,
        'end_item'    => $offset + $item_per_page,
        'next_link'   => $next_link,
        'prev_link'   => $prev_link,
        'page_title'  => 'Search GoogleBook'.($keywords ? ' - '.$keywords : '')
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
        $gbook     = BookRequest::get($id);
        $duplicate = Product::getDuplicateProduct([$gbook->getIsbn10(), $gbook->getIsbn13(), $gbook->getIssn()]);

        if ($duplicate) {

          $this->render(new TwigView('admin/product/duplicate_google_book.html', ['product' => $duplicate]));

        } else {

          $product = Product::newFromGoogleBook($gbook);

          $categories         = Category::findAll();
          $inputs             = $session->getOldInputBag();
          $errors             = $session->getErrorBag();
          $data               = compact('product', 'inputs', 'categories', 'errors');
          $data['page_title'] = "$product->name - Import";

          $this->render(new TwigView('admin/product/add.html', $data));
    
        }

      } catch (\RuntimeException $e) {

        $flash_bag = $session->getFlashBag();
        $flash_bag->set('errors', [$e->getMessage()]);

        $response->redirect('App\Admin\Controllers\ProductController','searchGoogleBook');
        return;
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

      $inputs     = $session->getOldInputBag();
      $errors     = $session->getErrorBag();
      $error_msgs = array_flatten($errors->all());

      $product = new Product();
      $categories = Category::findAll();

      $data = compact('error_msgs', 'errors', 'inputs', 'product', 'categories'); 
      $data['page_title'] = "Add Product";

      $this->render(new TwigView('admin/product/add.html', $data));
    });

  }

  public function edit($id) 
  {
    $this->respondTo('html', function() use($id) {

      $product = Product::find($id);

      if ($product == null) 
      {
        $this->getResponse()->redirect('App\Admin\Controllers\ProductController', 'index');
        return;
      }

      $categories = Category::findAll();
      $session    = $this->getSession();
      $inputs     = $session->getOldInputBag();
      $errors     = $session->getErrorBag();
      $error_msgs = array_flatten($errors->all());

      $data = compact('error_msgs', 'product','inputs', 'errors', 'categories');

      $data['page_title'] = "$product->name - Edit";
      $data['method']     = 'edit';

      $this->render(new TwigView('admin/product/edit_main.html', $data));
    });
  }

  public function editPictures($id)
  {
    $this->respondTo('html', function() use($id) {

      $product = Product::find($id);

      if ($product == null) 
      {
        $this->getResponse()->redirect('App\Admin\Controllers\ProductController', 'index');
        return;
      }

      $flash  = $this->getSession()->getFlashBag();
      $errors = $flash->get('errors');
      $images = $product->images;

      $data = compact('product', 'errors', 'images');

      $data['method'] = 'editPictures';

      $data['page_title'] = "$product->name - Edit";

      $this->render(new TwigView('admin/product/edit_pictures.html', $data));
    });
  }

  public function editAuthors($id)
  {
    $this->respondTo('html', function() use($id) {

      $product = Product::find($id);

      if ($product == null)
      {
        $this->getResponse()->redirect('App\Admin\Controllers\ProductController', 'index');
        return;
      }

      $flash       = $this->getSession()->getFlashBag();
      $errors      = $flash->get('errors');
      $all_authors = Author::findAll();

      $data = [
        'method'      => 'editAuthors',
        'page_title'  => sprintf("%s - Edit", $product->name),
        'product'     => $product,
        'all_authors' => $all_authors,
        'errors'      => $errors
      ];

      $this->render(new TwigView('admin/product/edit_authors.html', $data));
    });
  }

  public function addAuthor($product_id)
  {
    $this->respondTo('html', function() use($product_id) {

      $response = $this->getResponse();
      $product  = Product::find($product_id);
      $flash    = $this->getSession()->getFlashBag();

      if ($product == null) {
        $flash->add('errors', "Product #$product_id not found");
        $response->redirect('App\Admin\Controllers\ProductController', 'index');
        return;
      }

      $params = $this->getRequest()->getParams();
      $author_id = $params->get('author_id');
      $author    = Author::find($author_id);
      if ($author == null) {
        $flash->add('errors', "Author #$author_id not found");
        $response->redirect('App\Admin\Controllers\ProductController', 'editAuthors', [$product_id]);
        return;
      }
      
      try {

        $product->addAuthor($author);
        $response->redirect('App\Admin\Controllers\ProductController', 'editAuthors', [$product_id]);

      } catch(DuplicateResourceException $e) {

        $flash->add('errors', sprintf("Duplicate Author #%s - %s", $author_id, $author->name));
        $response->redirect('App\Admin\Controllers\ProductController', 'editAuthors', [$product_id]);
      }

    });
  }

  public function removeAuthor($product_id, $author_id)
  {
    $this->respondTo('html', function() use($product_id, $author_id) {

      $response = $this->getResponse();
      $product  = Product::find($product_id);
      $flash    = $this->getSession()->getFlashBag();

      if ($product == null) {
        $flash->add('errors', "Product #$product_id not found");
        $response->redirect('App\Admin\Controllers\ProductController', 'index');
        return;
      }

      $author = Author::find($author_id);
      if ($author == null) {
        $flash->add('errors', "Author #$author_id not found");
        $response->redirect('App\Admin\Controllers\ProductController', 'editAuthors', [$product_id]);
        return;
      }

      if (!$product->removeAuthor($author)) {
        $flash->add('errors', "Author #$author_id not associated with this book");
      }
      $response->redirect('App\Admin\Controllers\ProductController', 'editAuthors', [$product_id]);
    });

  }

  public function update($id) 
  {

    $flash   = $this->getSession()->getFlashBag();
    $request = $this->getRequest();

    try {

      $product = Product::update($id, $request->getParams());

      $this->respondTo('html', function() use($id) {
        $this->getResponse()->redirect('App\Admin\Controllers\ProductController', 'edit', [$id]);
      });

    } catch(ResourceNotFoundException $e) {

      $flash->set('inputs', $request->getParams()->all());

      $this->respondTo('html', function() use($id) {
        $this->getResponse()->redirect('App\Admin\Controllers\ProductController', 'edit', [$id]);
      });


    } catch(ValidationException $e) {

      $flash->set('errors', $e->getErrors());
      $flash->set('inputs', $request->getParams()->all());

      $this->respondTo('html', function() use($id) {
        $this->getResponse()->redirect('App\Admin\Controllers\ProductController', 'edit', [$id]);
      });

    }
  }

  public function uploadPicture($id)
  {
      $this->respondTo('html', function() use($id) {

        $response  = $this->getResponse();
        $flash     = $this->getSession()->getFlashBag();
        $files_bag = $this->getRequest()->getFiles();
        $picfile   = $files_bag->get('picture');

        if ($picfile == null) {

          $flash->add('errors', 'no file uploaded');
          $response->redirect('App\Admin\Controllers\ProductController', 'editPictures', [$id]);
          return;
        }

        $product = Product::find($id);

        if ($product == null) {

          $flash->add('errors', "Product #$id cannot be found");
          $response->redirect('App\Admin\Controllers\ProductController', 'index');
          return;
        }

        Image::saveUploadedImage($product, $picfile);

        $response->redirect('App\Admin\Controllers\ProductController', 'editPictures', [$id]);

      });
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
