<?php $this->partial('book/header'); ?>

<h1>Create a Book</h1>
  <form action="<?=webpath('book#create')?>" method="post">
  <label for="name">Name: </label>
  <input type="text" name="name" id="name">
  <input type="submit" name="submit" value="Create">
</form>

<a href="<?=webpath('book#index')?>">Return to book list.</a>

<?php $this->partial('book/footer'); ?>
