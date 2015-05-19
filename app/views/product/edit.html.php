<?php $this->partial('common/header') ?>

<h1>Edit Product #<?=$product->id?></h1>
  <form action="<?=webpath('ProductController#update', array($product->id))?>" method="post">

  <div>
    <label for="name">Name: </label>
    <input type="text" name="name" id="name" value="<?=$product->name?>">
  </div>

  <div>
    <label for="description">Description: </label>
    <textarea name="description" id="description"></textarea>
  </div>
  <input type="submit" name="submit" value="Update">
</form>

<a href="<?=webpath('ProductController#index')?>">Return to product list.</a>

<?php $this->partial('common/footer') ?>
