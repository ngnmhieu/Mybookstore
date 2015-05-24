<?php
use Markzero\Mvc\View;
use Markzero\Mvc\AppController;
use Markzero\Validation\Exception\ValidationException;

class UserController extends AppController {

  function register() {
    $this->respondTo('html', function() {
      $this->render(new View\TwigView(array(), 'user/register'));
    });
  }

  function create() {
    try {
      $user = User::create($this->getRequest()->request);
      UserSession::setUser($user);

      $this->respondTo('html', function() use($user) {
        $data['user'] = $user;
        $this->render(new View\TwigView($data, 'user/registered'));
      });
    } catch(ValidationException $e) {

      $this->respondTo('html', function() use($e) {
        $this->getResponse()->redirect('user','register');
      });

    }
  }

}
