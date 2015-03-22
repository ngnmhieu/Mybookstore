<?php
class UserController extends AppController {

  function register() {
    $this->respond_to('html', function() {
      $this->render(new HtmlView(array(), 'user/register'));
    });
  }

  function create() {
    try {
      $user = User::create($this->request()->request);
      UserSession::setUser($user);

      $this->respond_to('html', function() use($user) {
        $data['user'] = $user;
        $this->render(new HtmlView($data, 'user/registered'));
      });
    } catch(ValidationException $e) {

      $this->respond_to('html', function() use($e) {
        $this->response()->redirect('user','register');
      });

    }
  }

}
