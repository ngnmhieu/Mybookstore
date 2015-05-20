<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <link rel="stylesheet" href="/bower_components/bootstrap/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="/font-awesome/css/font-awesome.min.css">
  <link rel="stylesheet" href="/css/general.css">
  <title>Products</title>
</head>
<body>
  <div id="PageWrapper">
    <div>
      [ <a href="<?=webpath('ProductController#index')?>">Products</a> ]
      <?php if(UserSession::isSignedIn()) { ?>
      <?php $user = UserSession::getUser(); ?>
      Welcome, <?=$user->name?>!
      [ <a href="<?=webpath('SessionController#delete')?>">Sign Out</a> ]
      <?php } else { ?>
      [ <a href="<?=webpath('UserController#register')?>">Register</a> ]
      [ <a href="<?=webpath('SessionController#signIn')?>">Sign In</a> ]
      <?php } ?>
    </div>
    <hr />
