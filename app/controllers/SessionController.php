<?php
use Markzero\Mvc\View;
use Markzero\Mvc\AppController;
use Markzero\Auth\Exception\AuthenticationFailedException;
use Markzero\Http\Exception\ResourceNotFoundException;
use Markzero\Validation\Exception\ValidationException;

class SessionController extends AppController {

  function signIn() {
    $this->respondTo('html', function() {
      $this->render(new View\HtmlView(array(), 'session/sign_in'));
    });
  }

  function create() {
    try {
      UserSession::create($this->getRequest()->request);

      $this->respondTo('html', function() {
        $this->getResponse()->redirect('ProductController', 'index');
      });

    } catch(ValidationException $e) {
      // Validation failed
      $this->respondTo('html', function() {
        $this->getResponse()->redirect('SessionController', 'signIn');
      });

    } catch(ResourceNotFoundException $e) {
      // User not found
      $this->respondTo('html', function() {
        $this->getResponse()->redirect('SessionController', 'signIn');
      });

    } catch(AuthenticationFailedException $e) {

      // User authentication failed
      $this->respondTo('html', function() {
        $this->getResponse()->redirect('SessionController', 'signIn');
      });
    }
    
  }

  function delete() {
    UserSession::delete();

    $this->respondTo('html', function() {
      $this->getResponse()->redirect('ProductController', 'index');
    });
  }
}
