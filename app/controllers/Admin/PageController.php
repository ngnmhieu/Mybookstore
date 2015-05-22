<?php
namespace Admin;

use Markzero\Mvc\View\HtmlView;
use Markzero\Mvc\AppController;
use Markzero\Auth\Exception\AuthenticationFailedException;
use Markzero\Auth\Exception\ActionNotAuthorizedException;
use Markzero\Http\Exception\ResourceNotFoundException;
use Markzero\Validation\Exception\ValidationException;

class PageController extends AppController {

  public function index() {
    $this->respondTo('html', function() {
      $this->render(new HtmlView(array(), 'admin/index'));
    });
  }
}
