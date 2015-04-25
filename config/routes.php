<?php
use Markzero\App;

/*
 * Version 0 
 * all the mapping must be regular expression
 * Version 1 proposal: {name:enforced_regular_expression} or {name}
 */

App::$router->draw(function($r) {
  $r->root('BookController','index');

  /** Book **/
  $r->get('/book/','BookController','index');
  $r->get('/book/([0-9]+)','BookController','show');
  $r->post('/book/','BookController','create');
  $r->post('/book/([0-9]+)','BookController','update');
  $r->get('/book/add','BookController','add');
  $r->get('/book/([0-9]+)/edit','BookController','edit');
  $r->post('/book/([0-9]+)/rate','BookController','rate');
  $r->post('/book/([0-9]+)/rate/([0-9]+)','BookController','updateRate');

  $r->delete('/book/([0-9]+)/delete','BookController','delete');
  $r->post('/book/([0-9]+)/delete','BookController','delete');
  $r->get('/book/([0-9]+)/delete','BookController','delete');


  /** User **/
  $r->get('/user/register','UserController','register');
  $r->post('/user/','UserController','create');

  /** Session **/
  $r->get('/session/sign_in','SessionController','signIn');
  $r->get('/session/sign_out','SessionController','delete');
  $r->post('/session/','SessionController','create');

});

