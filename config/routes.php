<?php
/*
 * Version 0 
 * all the mapping must be regular expression
 * Version 1 proposal: {name:enforced_regular_expression} or {name}
 */

App::$router->draw(function($r) {
  $r->root('book#index');

  /** Book **/
  $r->get('/book/','book#index');
  $r->get('/book/([0-9]+)','book#show');
  $r->post('/book/','book#create');
  $r->post('/book/([0-9]+)','book#update');
  $r->get('/book/add','book#add');
  $r->get('/book/([0-9]+)/edit','book#edit');

  $r->delete('/book/([0-9]+)/delete','book#delete');
  $r->post('/book/([0-9]+)/delete','book#delete');
  $r->get('/book/([0-9]+)/delete','book#delete');


  /** User **/
  $r->get('/user/register','user#register');
  $r->post('/user/','user#create');

  /** Session **/
  $r->get('/session/sign_in','session#signIn');
  $r->get('/session/sign_out','session#delete');
  $r->post('/session/','session#create');
});

