<?php $this->partial('common/header') ?>

<h1>Edit Book #<?=$book->id?></h1>
  <form action="<?=webpath('BookController#update', array($book->id))?>" method="post">

  <div>
    <label for="name">Name: </label>
    <input type="text" name="name" id="name" value="<?=$book->name?>">
  </div>

  <div>
    <label for="description">Description: </label>
    <textarea name="description" id="description"></textarea>
  </div>
  <input type="submit" name="submit" value="Update">
</form>

<a href="<?=webpath('BookController#index')?>">Return to book list.</a>

<?php $this->partial('common/footer') ?>
