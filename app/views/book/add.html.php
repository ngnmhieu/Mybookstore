<?php $this->partial('common/header'); ?>

<h1>Create a Book</h1>
  <form action="<?=webpath('BookController#create')?>" method="post">

  <div>
    <label for="name">Name: </label>
    <input type="text" name="name" id="name">
  </div>

  <div>
    <label for="description">Description: </label>
    <textarea name="description" id="description"></textarea>
  </div>

  <input type="submit" name="submit" value="Create">
</form>

<a href="<?=webpath('BookController#index')?>">Return to book list.</a>

<?php $this->partial('common/footer'); ?>
