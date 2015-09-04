<?php

namespace App\Admin\Controllers;

use App\Controllers\ApplicationController;
use App\Admin\Models\Author;
use Markzero\Mvc\View\TwigView;
use Markzero\Http\Response;
use Markzero\Validation\Exception\ValidationException;
use Markzero\Http\Exception\ResourceNotFoundException;

class AuthorController extends ApplicationController
{
  
  function index() 
  {
    $this->respondTo('html', function() {

      $authors = Author::findAll();
      $flash   = $this->getSession()->getFlashBag();

      $data['authors'] = $authors;
      $data['errors']  = $flash->get('errors');

      $this->render(new TwigView('admin/author/index.html', $data));
    });
  }

  function show($id) 
  {
    $this->respondTo('html', function() use($id) {
      
      $author = Author::find($id);

      if ($author != null)
      {
        $data['author'] = $author;
        $data['books']  = $author->books;

        $this->render(new TwigView('admin/author/show.html', $data));

      } else {

        $flash = $this->getSession()->getFlashBag();
        $flash->add('errors', "Author #$id not found.");
        $this->getResponse()->redirect('App\Admin\Controllers\AuthorController', 'index');
      }

    });
  }

  function add() 
  {
    $this->respondTo('html', function() {
    
      $session = $this->getSession();
      $author  = new Author();
      $inputs  = $session->getOldInputBag();
      $errors  = $session->getErrorBag();

      $this->render(new TwigView('admin/author/add.html', compact('author', 'inputs', 'errors')));

    });
  }

  function edit($id) 
  {

    $this->respondTo('html', function() use($id) {

      try {
          
        $author  = Author::find($id);
        $session = $this->getSession();
        $inputs  = $session->getOldInputBag();
        $errors  = $session->getErrorBag();

        $this->render(new TwigView('admin/author/edit.html', compact('author', 'inputs', 'errors')));

      } catch (ResourceNotFoundException $e) {

        $this->getResponse()->redirect('App\Admin\Controllers\AuthorController', 'index');

      }
    });
  }

  function create() 
  {
    $this->respondTo('html', function() {

      $request  = $this->getRequest();
      $response = $this->getResponse();

      try {
        Author::create($request->getParams());
        $response->redirect('App\Admin\Controllers\AuthorController', 'index');

      } catch(ValidationException $e) {

        $flash = $this->getSession()->getFlashBag();
        $flash->set('inputs', $request->getParams()->all());
        $flash->set('errors', $e->getErrors());

        $response->redirect('App\Admin\Controllers\AuthorController', 'add');
      }
    });
  }

  function update($id) 
  {
    $this->respondTo('html', function() use($id) {

      $response = $this->getResponse();
      $request  = $this->getRequest();

      try {
        Author::update($id, $this->getRequest()->getParams());

        $response->redirect('App\Admin\Controllers\AuthorController', 'index');

      } catch(ValidationException $e) {

        $flash = $this->getSession()->getFlashBag();
        $flash->set('errors', $e->getErrors());
        $flash->set('inputs', $request->getParams()->all());

        $response->redirect('App\Admin\Controllers\AuthorController', 'edit', [$id]);

      }

    });
  }

  function delete($id) 
  {
    $this->respondTo('html', function() use($id) {

      $response = $this->getResponse();
      $flash    = $this->getSession()->getFlashBag();

      try {

        Author::delete($id);
        $response->redirect('App\Admin\Controllers\AuthorController', 'index');

      } catch (ResourceNotFoundException $e) {

        $flash->add('errors', "Cannot find Author #$id");
        $response->redirect('App\Admin\Controllers\AuthorController', 'index');

      } catch (\Exception $e) {

        $flash->add('errors', 'Cannot delete Author #'.$id.' ('.$e->getMessage().')');
        $response->redirect('App\Admin\Controllers\AuthorController', 'index');
      }

    });
  }
}
