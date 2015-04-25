<?php $this->partial('common/header'); ?>

<h1>Sign In</h1>
  <form action="<?=webpath('SessionController#create')?>" method="post">
  <div>
    <label for="email">Email: </label>
    <input type="text" name="user[email]" id="email">
  </div>

  <div>
    <label for="password">Password: </label>
    <input type="password" name="user[password]" id="password">
  </div>

  <br />
  <input type="submit" name="submit" value="Sign In">
</form>

<?php $this->partial('common/footer'); ?>

