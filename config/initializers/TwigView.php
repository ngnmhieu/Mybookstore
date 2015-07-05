<?php
use Markzero\App;
use Markzero\Mvc\View\TwigView;

TwigView::setTemplatePaths(array(App::$APP_PATH.'app/Twigs/'));

TwigView::configEnvironment(function($twig) {
});
