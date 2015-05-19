<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Products</title>
</head>
<body>
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
