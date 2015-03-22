<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Books</title>
</head>
<body>
<div>
[ <a href="<?=webpath('book#index')?>">Books</a> ]
<?php if(UserSession::isSignedIn()) { ?>
  <?php $user = UserSession::getUser(); ?>
  Welcome, <?=$user->name?>!
  [ <a href="<?=webpath('session#delete')?>">Sign Out</a> ]
<?php } else { ?>
[ <a href="<?=webpath('user#register')?>">Register</a> ]
[ <a href="<?=webpath('session#signIn')?>">Sign In</a> ]
<?php } ?>
</div>
<hr />
