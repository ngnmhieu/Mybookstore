<?php
namespace App\Auth\Controllers;

use App\Store\Models\UserSession; 
use Markzero\Mvc\View;
use App\Controllers\ApplicationController;
use Markzero\Auth\Exception\AuthenticationFailedException;
use Markzero\Http\Exception\ResourceNotFoundException;
use Markzero\Validation\Exception\ValidationException;

class SessionController extends ApplicationController 
{

  function signIn() 
  {
    $this->respondTo('html', function() {
      $this->render(new View\TwigView('session/sign_in.html'));
    });
  }

  function create() 
  {
    try {
      $params   = $this->getRequest()->request;
      $email    = $params->get('user[email]', null, true);
      $password = $params->get('user[password]', null, true);

      UserSession::signIn($email, $password);

      $this->respondTo('html', function() {
        $this->getResponse()->redirect('App\Store\Controllers\ProductController', 'index');
      });

    } catch(ValidationException $e) {

      // Validation failed
      $this->respondTo('html', function() {
        $this->getResponse()->redirect('App\Auth\Controllers\SessionController', 'signIn');
      });

    } catch(ResourceNotFoundException $e) {
      // User not found
      $this->respondTo('html', function() {
        $this->getResponse()->redirect('App\Auth\Controllers\SessionController', 'signIn');
      });

    } catch(AuthenticationFailedException $e) {

      // User authentication failed
      $this->respondTo('html', function() {
        $this->getResponse()->redirect('App\Auth\Controllers\SessionController', 'signIn');
      });
    }
    
  }

  function delete() 
  {
    UserSession::signOut();

    $this->respondTo('html', function() {
      $this->getResponse()->redirect('App\Store\Controllers\ProductController', 'index');
    });
  }
}
