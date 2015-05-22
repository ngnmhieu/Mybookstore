<?php $this->partial('admin/common/header') ?>

    <div id="ProductControls">
      <a href="<?=webpath('Admin\ProductController#add')?>">
        <button type="button" class="btn btn-primary"><span class="fa fa-plus fa-lg"></span> Add Product</button>
      </a>
      <div class="clear"></div>
    </div>


    <table class="table table-striped">
      <tr>
        <th style="width: 20px"></th>
        <th style="width: 70px">ProductID</th>
        <th>Name</th>
        <th>Barcode</th>
        <th>Barcode type</th>
        <th>Price</th>
      </tr>

      <?php foreach ($products as $product) { ?>
      <tr>
        <td><a href="<?=webpath('Admin\ProductController#edit', [$product->id])?>"><span class="fa fa-pencil fa-lg"></span></a></td>
        <td><?=$product->id?></td>
        <td><?=$product->name?></td>
        <td><?=$product->barcode?></td>
        <td><?=$product->barcode_type?></td>
        <td><?=$product->price?></td>
      </tr>
      <?php }?>

  </table>
<?php $this->partial('admin/common/footer') ?>
