<?php
use Markzero\App;

/*
 * Version 0 
 * all the mapping must be regular expression
 * Version 1 proposal: {name:enforced_regular_expression} or {name}
 */

$r = App::$router;

$r->root('App\Store\Controllers\ProductController','index');

/** Product **/
$r->get('/product/','App\Store\Controllers\ProductController','index');
$r->get('/product/([0-9]+)','App\Store\Controllers\ProductController','show');
$r->post('/product/','App\Store\Controllers\ProductController','create');
$r->post('/product/([0-9]+)','App\Store\Controllers\ProductController','update');
$r->get('/product/add','App\Store\Controllers\ProductController','add');
$r->get('/product/([0-9]+)/edit','App\Store\Controllers\ProductController','edit');
$r->post('/product/([0-9]+)/rate','App\Store\Controllers\ProductController','rate');
$r->post('/product/([0-9]+)/rate/([0-9]+)','App\Store\Controllers\ProductController','updateRate');

/** Admin\Product **/
$r->get('/admin/','App\Admin\Controllers\PageController','index');
$r->get('/admin/product/','App\Admin\Controllers\ProductController','index');
$r->get('/admin/product/([0-9]+)','App\Admin\Controllers\ProductController','show');
$r->get('/admin/product/add','App\Admin\Controllers\ProductController','add');
$r->get('/admin/product/([0-9]+)/edit','App\Admin\Controllers\ProductController','edit');
$r->get('/admin/product/([0-9]+)/delete','App\Admin\Controllers\ProductController','delete');
$r->post('/admin/product/','App\Admin\Controllers\ProductController','create');
$r->post('/admin/product/([0-9]+)','App\Admin\Controllers\ProductController','update');
$r->delete('/admin/product/([0-9]+)/delete','App\Admin\Controllers\ProductController','delete');
$r->get('/admin/product/googlebook/search','App\Admin\Controllers\ProductController','searchGoogleBook');
$r->get('/admin/product/googlebook/(.+)/add','App\Admin\Controllers\ProductController','addFromGoogle');

/** Admin\Category **/
$r->get('/admin/category/','App\Admin\Controllers\CategoryController','index');
$r->get('/admin/category/([0-9]+)','App\Admin\Controllers\CategoryController','show');
$r->get('/admin/category/([0-9]+)/edit','App\Admin\Controllers\CategoryController','edit');
$r->get('/admin/category/add','App\Admin\Controllers\CategoryController','add');
$r->post('/admin/category/','App\Admin\Controllers\CategoryController','create');
$r->get('/admin/category/([0-9]+)/delete','App\Admin\Controllers\CategoryController','delete');

/** User **/
$r->get('/user/register','App\Store\Controllers\UserController','register');
$r->post('/user/','App\Store\Controllers\UserController','create');

/** Session **/
$r->get('/session/sign_in','App\Auth\Controllers\SessionController','signIn');
$r->get('/session/sign_out','App\Auth\Controllers\SessionController','delete');
$r->post('/session/','App\Auth\Controllers\SessionController','create');

