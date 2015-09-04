<?php
use Markzero\App;
use Markzero\Mvc\View\TwigView;

include_once(App::$APP_PATH.'app/Twigs/functions.php');

TwigView::setTemplatePaths(array(App::$APP_PATH.'app/Twigs/'));

TwigView::configEnvironment(function($twig) {

  $twig->addFunction(new \Twig_SimpleFunction('get_stars', 'App\Twigs\get_stars'));

});
