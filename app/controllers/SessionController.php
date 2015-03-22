<?php

class SessionController extends AppController {

  function signIn() {
    $this->respond_to('html', function() {
      $this->render(new HtmlView(array(), 'session/sign_in'));
    });
  }

  function create() {
    try {
      UserSession::create($this->request()->request);

      $this->respond_to('html', function() {
        $this->response()->redirect('book', 'index');
      });

    } catch(ValidationException $e) {
      // Validation failed
      $this->respond_to('html', function() {
        $this->response()->redirect('session', 'signIn');
      });

    } catch(ResourceNotFoundException $e) {
      // User not found
      $this->respond_to('html', function() {
        $this->response()->redirect('session', 'signIn');
      });

    } catch(AuthenticationFailedException $e) {

      // User authentication failed
      $this->respond_to('html', function() {
        $this->response()->redirect('session', 'signIn');
      });
    }
    
  }

  function delete() {
    UserSession::delete();

    $this->respond_to('html', function() {
      $this->response()->redirect('book', 'index');
    });
  }
}
