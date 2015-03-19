<?php
/*
 * Version 0 
 * all the mapping must be regular expression
 * Version 1 proposal: {name:enforced_regular_expression} or {name}
 */

App::$router->draw(function($r) {
  $r->root('book#index');

  $r->get('/book/','book#index');
  $r->get('/book/([0-9]+)','book#show');
  $r->post('/book/','book#create');
  $r->post('/book/([0-9]+)','book#update');
  $r->delete('/book/delete','book#destroy');

  $r->get('/book/delete','book#destroy');
  $r->get('/book/add','book#add');
  $r->get('/book/([0-9]+)/edit','book#edit');
});

