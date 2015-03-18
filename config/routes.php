<?php
/*
 * Version 0 
 * all the mapping must be regular expression
 * Version 1 proposal: {name:enforced_regular_expression} or {name}
 */

App::$router->draw(function($r) {
  $r->root('book#index');
  $r->map('get','/book/([0-9]+)','book#show');
  $r->map('post','/book/','book#create');
});

