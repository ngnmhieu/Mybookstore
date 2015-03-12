<?php
/*
 * Version 0 
 * all the mapping must be regular expression
 * Version 1 proposal: {name:enforced_regular_expression} or {name}
 */

App::$router->draw(function($r) {
  $r->root('controller#index');

  $r->map('get', '/path/', 'controller#action');

  $r->get('/controller/([REGEX])/action/([0-9]+)', 'controller#action');
  $r->post('/controller/([REGEX])/action/([0-9]+)', 'controller#action');
  $r->put('/controller/([REGEX])/action/([0-9]+)', 'controller#action');
  $r->delete('/controller/([REGEX])/action/([0-9]+)', 'controller#action');
});

