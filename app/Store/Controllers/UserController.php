<?php

use App\Models\UserSession; 
use App\Models\User; 
use App\Controllers\ApplicationController;
use Markzero\Mvc\View;
use Markzero\Mvc\AppController;
use Markzero\Validation\Exception\ValidationException;

class UserController extends ApplicationController 
{

  function register() 
  {
    $this->respondTo('html', function() {
      $this->render(new View\TwigView('user/register.html'));
    });
  }

  function create() 
  {
    try {
      $user = User::create($this->getRequest()->request);
      UserSession::setUser($user);

      $this->respondTo('html', function() use($user) {
        $data['user'] = $user;
        $this->render(new View\TwigView('user/registered.html', $data));
      });
    } catch(ValidationException $e) {

      $this->respondTo('html', function() use($e) {
        $this->getResponse()->redirect('App\Store\Controllers\UserController','register');
      });

    }
  }

}