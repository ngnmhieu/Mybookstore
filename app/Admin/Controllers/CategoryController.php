<?php
namespace App\Admin\Controllers;

use App\Controllers\ApplicationController;
use App\Admin\Models\Category;
use Markzero\Mvc\View\TwigView;
use Markzero\Http\Response;
use Markzero\Validation\Exception\ValidationException;
use Markzero\Http\Exception\ResourceNotFoundException;

class CategoryController extends ApplicationController 
{

  function index() 
  {
    $this->respondTo('html', function() {

      $flash      = $this->getSession()->getFlashBag();
      $categories = Category::findAll();

      $data['categories'] = $categories;
      $data['errors']     = $flash->get('errors');

      $this->render(new TwigView('admin/category/index.html', $data));

    });
  }

  function show($id) 
  {
    $category = Category::find($id);
    $products = $category->products;
    $this->respondTo('html', function() use($category, $products) {
      $data['category'] = $category;
      $data['products'] = $products;
      $this->render(new TwigView('admin/category/show.html', $data));
    });
  }

  function add() 
  {
    $this->respondTo('html', function() {
    
      $session  = $this->getSession();
      $category = new Category();
      $inputs   = $session->getOldInputBag();
      $errors   = $session->getErrorBag();

      $this->render(new TwigView('admin/category/add.html', compact('category', 'inputs', 'errors')));
    });
  }

  function edit($id) 
  {
    $this->respondTo('html', function() use($id) {

      $category = Category::find($id);
      $session  = $this->getSession();
      $inputs   = $session->getOldInputBag();
      $errors   = $session->getErrorBag();

      $this->render(new TwigView('admin/category/edit.html', compact('category', 'inputs', 'errors')));
    });
  }

  function create() 
  {
    $this->respondTo('html', function() {

      $request  = $this->getRequest();
      $response = $this->getResponse();

      try {

        Category::create($request->getParams());
        $response->redirect('App\Admin\Controllers\CategoryController', 'index');

      } catch(ValidationException $e) {

        $flash = $this->getSession()->getFlashBag();
        $flash->set('inputs', $request->getParams()->all());
        $flash->set('errors', $e->getErrors());

        $response->redirect('App\Admin\Controllers\CategoryController', 'add');

      }
    });
  }

  function update($id) 
  {
    $this->respondTo('html', function() use($id) {

      $response = $this->getResponse();
      $request  = $this->getRequest();

      try {
        Category::update($id, $this->getRequest()->getParams());

        $response->redirect('App\Admin\Controllers\CategoryController', 'index');

      } catch(ValidationException $e) {

        $flash = $this->getSession()->getFlashBag();
        $flash->set('errors', $e->getErrors());
        $flash->set('inputs', $request->getParams()->all());

        $response->redirect('App\Admin\Controllers\CategoryController', 'edit', [$id]);

      }

    });
  } 

  function delete($id) 
  {

    $this->respondTo('html', function() use($id) {

      $response = $this->getResponse();
      $flash    = $this->getSession()->getFlashBag();

      try {
        $cat        = Category::find($id);
        $categories = $cat->findAllOthers();
        $products   = $cat->products;

        $this->render(new TwigView('admin/category/delete.html', compact('cat', 'products', 'categories')));
      } catch (ResourceNotFoundException $e) {

        $flash->add('errors', "Product #$id not found");
        $response->redirect('App\Admin\Controllers\CategoryController', 'index');

      }

    });
  }

  /**
   * Migrates products in a category to another
   */
  function migrate($id)
  {
    $this->respondTo('html', function() use($id) {

      $response   = $this->getResponse();
      $flash      = $this->getSession()->getFlashBag();
      $params     = $this->getRequest()->getParams();
      $alt_cat_id = $params->get('alt_category_id');

      $cat     = Category::find($id);
      $alt_cat = Category::find($alt_cat_id);

      if ($cat == null || $alt_cat == null) {

        $flash->add('errors', "Category #$id or #$cat_id not found");
        $response->redirect('App\Admin\Controllers\CategoryController', 'delete', [$id]);
        return;
      }

      $cat->migrateTo($alt_cat);

      $response->redirect('App\Admin\Controllers\CategoryController', 'delete', [$id]);
    });
     
  }

  function doDelete($id) 
  {

    $this->respondTo('html', function() use($id) {

      $response = $this->getResponse();
      $flash    = $this->getSession()->getFlashBag();

      try {

        Category::delete($id);
        $response->redirect('App\Admin\Controllers\CategoryController', 'index');

      } catch (ResourceNotFoundException $e) {

        $flash->add('errors', "Cannot find Category #$id");
        $response->redirect('App\Admin\Controllers\CategoryController', 'index');

      } catch (\Exception $e) {

        $flash->add('errors', 'Cannot delete Category #'.$id.' ('.$e->getMessage().')');
        $response->redirect('App\Admin\Controllers\CategoryController', 'index');
      }

    });
  }
}
