<?php
namespace Admin;

use Markzero\Mvc\View\HtmlView;
use Markzero\Mvc\View\TwigView;
use App\Controllers\ApplicationController;
use Markzero\Auth\Exception\AuthenticationFailedException;
use Markzero\Auth\Exception\ActionNotAuthorizedException;
use Markzero\Http\Exception\ResourceNotFoundException;
use Markzero\Validation\Exception\ValidationException;

class PageController extends ApplicationController {

  public function index() {
    $this->respondTo('html', function() {
      $this->render(new TwigView('admin/index.html'));
    });
  }
}
