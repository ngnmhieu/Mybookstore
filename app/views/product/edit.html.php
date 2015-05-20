<?php $this->partial('common/header') ?>

<h1>Edit Product #<?=$product->id?></h1>
  <form action="<?=webpath('ProductController#update', array($product->id))?>" method="post">

  <div>
    <label for="name">Name: </label>
    <input type="text" name="name" id="name" value="<?=$product->name?>">
  </div>

  <input type="submit" class="btn btn-primary" name="submit" value="Update">
</form>

<br />

<table class="table table-striped">
   <tr>
     <th>Detail ID</th>
     <th>Title</th>
     <th>Barcode</th>
     <th>Barcode type</th>
     <th>Price</th>
     <th style="width: 15px"></th>
   </tr>

   <?php foreach ($details as $detail) { ?>
     <tr>
       <td><?=$detail->id?></td>
       <td><?=$detail->title?></td>
       <td><?=$detail->barcode?></td>
       <td><?=$detail->barcode_type?></td>
       <td><?=$detail->price?></td>
       <td><a href="<?=webpath('ProductDetailController#delete', [$product->id, $detail->id])?>"><span class="fa fa-trash"></span></a></td>
     </tr>
   <?php }?>

</table>

<a href="<?=webpath('ProductDetailController#add', [$product->id])?>"><button class="btn btn-primary">Add Product detail</button></a>

<a class="btn btn-default" href="<?=webpath('ProductController#index')?>">Return to product list.</a>

<?php $this->partial('common/footer') ?>
