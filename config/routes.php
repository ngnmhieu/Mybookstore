<?php
use Markzero\App;

/*
 * Version 0 
 * all the mapping must be regular expression
 * Version 1 proposal: {name:enforced_regular_expression} or {name}
 */

App::$router->draw(function($r) {
  $r->root('ProductController','index');

  /** Product **/
  $r->get('/product/','ProductController','index');
  $r->get('/product/([0-9]+)','ProductController','show');
  $r->post('/product/','ProductController','create');
  $r->post('/product/([0-9]+)','ProductController','update');
  $r->get('/product/add','ProductController','add');
  $r->get('/product/([0-9]+)/edit','ProductController','edit');
  $r->post('/product/([0-9]+)/rate','ProductController','rate');
  $r->post('/product/([0-9]+)/rate/([0-9]+)','ProductController','updateRate');

  $r->delete('/product/([0-9]+)/delete','ProductController','delete');
  $r->post('/product/([0-9]+)/delete','ProductController','delete');
  $r->get('/product/([0-9]+)/delete','ProductController','delete');


  /** User **/
  $r->get('/user/register','UserController','register');
  $r->post('/user/','UserController','create');

  /** Session **/
  $r->get('/session/sign_in','SessionController','signIn');
  $r->get('/session/sign_out','SessionController','delete');
  $r->post('/session/','SessionController','create');

});

