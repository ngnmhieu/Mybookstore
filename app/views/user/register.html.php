<?php $this->partial('common/header'); ?>

<h1>Register</h1>
  <form action="<?=webpath('UserController#create')?>" method="post">
  <div>
    <label for="email">Email: </label>
    <input type="text" name="email" id="email">
  </div>

  <div>
    <label for="name">Name: </label>
    <input type="text" name="name" id="name">
  </div>

  <div>
    <label for="password">Password: </label>
    <input type="password" name="password" id="password">
  </div>

  <br />
  <input type="submit" name="submit" value="Create">
</form>

<?php $this->partial('common/footer'); ?>

