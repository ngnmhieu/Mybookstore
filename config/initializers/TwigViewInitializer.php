<?php
use Markzero\App;
use Markzero\Mvc\View\TwigView;

TwigView::setTemplatePath(App::$APP_PATH.'app/twigs/');

TwigView::setTwigConfig(array());
