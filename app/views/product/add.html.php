<?php $this->partial('common/header'); ?>

<h1>Create a Product</h1>
  <form action="<?=webpath('ProductController#create')?>" method="post">

  <div>
    <label for="name">Name: </label>
    <input type="text" name="name" id="name">
  </div>

  <input type="submit" name="submit" value="Create">
</form>

<a class="btn btn-default" href="<?=webpath('ProductController#index')?>">Return to product list.</a>

<?php $this->partial('common/footer'); ?>
