<?php
use Markzero\App;

include("../core/App.php");

# start the application
App::bootstrap();
App::$router->dispatch();
