<?php $this->partial('book/header') ?>

<h1>Edit Book #<?=$book->id?></h1>
  <form action="<?=webpath('book#update', array($book->id))?>" method="post">
  <label for="name">Name: </label>
  <input type="text" name="name" id="name" value="<?=$book->name?>">
  <input type="submit" name="submit" value="Update">
</form>

<a href="<?=webpath('book#index')?>">Return to book list.</a>

<?php $this->partial('book/footer') ?>
