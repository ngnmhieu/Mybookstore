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
  $r->get('/product/([0-9]+)/delete','ProductController','delete');

  /** Admin\Product **/
  $r->get('/admin/','Admin\PageController','index');
  $r->get('/admin/product/','Admin\ProductController','index');
  $r->get('/admin/product/([0-9]+)','Admin\ProductController','show');
  $r->get('/admin/product/add','Admin\ProductController','add');
  $r->get('/admin/product/([0-9]+)/edit','Admin\ProductController','edit');
  $r->get('/admin/product/([0-9]+)/delete','Admin\ProductController','delete');
  $r->post('/admin/product/','Admin\ProductController','create');
  $r->post('/admin/product/([0-9]+)','Admin\ProductController','update');
  $r->delete('/admin/product/([0-9]+)/delete','Admin\ProductController','delete');
  $r->get('/admin/product/googlebook/search','Admin\ProductController','searchGoogleBook');
  $r->post('/admin/product/googlebook/add','Admin\ProductController','addFromGoogle');

  /** Admin\Category **/
  $r->get('/admin/category/','Admin\CategoryController','index');
  $r->get('/admin/category/([0-9]+)','Admin\CategoryController','show');
  $r->get('/admin/category/([0-9]+)/edit','Admin\CategoryController','edit');
  $r->get('/admin/category/add','Admin\CategoryController','add');
  $r->post('/admin/category/','Admin\CategoryController','create');
  $r->get('/admin/category/([0-9]+)/delete','Admin\CategoryController','delete');

  /** ProductDetail **/
  $r->get('/product/([0-9]+)/detail/add','ProductDetailController','add');
  $r->post('/product/([0-9]+)/detail','ProductDetailController','create');
  $r->get('/product/detail/([0-9]+)/edit','ProductDetailController','edit');
  $r->get('/product/detail/([0-9]+)/delete','ProductDetailController','delete');


  /** User **/
  $r->get('/user/register','UserController','register');
  $r->post('/user/','UserController','create');

  /** Session **/
  $r->get('/session/sign_in','SessionController','signIn');
  $r->get('/session/sign_out','SessionController','delete');
  $r->post('/session/','SessionController','create');

});

