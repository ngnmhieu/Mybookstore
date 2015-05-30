<?php
namespace Admin;

use App\Controllers\ApplicationController;
use App\Models\Category;
use Markzero\Mvc\View\TwigView;
use Markzero\Http\Response;
use Markzero\Validation\Exception\ValidationException;
use Markzero\Http\Exception\ResourceNotFoundException;

class CategoryController extends ApplicationController {

  function index() {
    $categories = Category::findAll();

    $this->respondTo('html', function() use($categories) {
      $data['categories'] = $categories;
      $this->render(new TwigView('admin/category/index.html', $data));
    });
  }

  function show($id) {
    $category = Category::find($id);
    $products = $category->products;
    $this->respondTo('html', function() use($category, $products) {
      $data['category'] = $category;
      $data['products'] = $products;
      $this->render(new TwigView('admin/category/show.html', $data));
    });
  }

  function add() {
    $this->respondTo('html', function() {
      $this->render(new TwigView('admin/category/add.html'));
    });
  }

  function edit() {
    // $this->respondTo('html', function() {
      // $this->render(new TwigView('admin/category/add.html'));
    // });
  }

  function create() {
    try {
      $cat = Category::create($this->getRequest()->getParams());

      $this->respondTo('json', function() {
        $this->getResponse()->setStatusCode(Response::HTTP_OK, 'Category Created');
      });

    } catch(ValidationException $e) {

      $this->respondTo('json', function() use($e) {
        $this->getResponse()->setStatusCode(Response::HTTP_BAD_REQUEST, 'Bad Request (Validation Error)');
        $this->render(new JsonView($e->getErrors()));
      });

    }
  }

  function update($id) {
    try {
      $cat = Category::update($id, $this->getRequest()->getParams());

      $this->respondTo('json', function() {
        $this->getResponse()->setStatusCode(Response::HTTP_OK, 'Category Updated');
      });

    } catch(ValidationException $e) {

      $this->respondTo('json', function() use($e) {
        $this->getResponse()->setStatusCode(Response::HTTP_BAD_REQUEST, 'Bad Request (Validation Error)');
        $this->render(new JsonView($e->getErrors()));
      });

    }
  }

  function delete($id) {
    try {
      Category::delete($id);

      $this->respondTo('json', function() {
        $this->getResponse()->setStatusCode(Response::HTTP_OK, 'Category Deleted');
      });
    } catch (\Exception $e) {

      $this->respondTo('json', function() use($e) {
        $this->getResponse()->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR, 'Category could not be deleted, error occurred: '.$e->getMessage());
      });
    }

  }
}
