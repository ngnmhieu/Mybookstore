<?php
use Doctrine\ORM\Tools\Console\ConsoleRunner;

require_once('./core/app.php');

App::bootstrap();

return ConsoleRunner::createHelperSet(App::$em);
