<?php $this->partial('admin/common/header'); ?>

<div id="ProductForm">
  <h3>Update Product</h3>

  <form action="<?=webpath('Admin\ProductController#update', array($product->id))?>" method="post">
    
    <div role="tabpanel">
      <!-- Nav tabs -->
      <ul class="nav nav-tabs" role="tablist">
        <li role="presentation" class="active"><a href="#maininfo" aria-controls="maininfo" role="tab" data-toggle="tab">Product Information</a></li>
      </ul>

      <!-- Tab panes -->
      <div class="tab-content">

        <div role="tabpanel" class="tab-pane active" id="maininfo">
          <?php $this->partial('admin/product/_maininfo_form'); ?>    
        </div>

      </div>
    </div>

    <a class="btn btn-default" href="<?=webpath('Admin\ProductController#index')?>">Return</a>
    <input type="submit" class="btn btn-primary" name="submit" value="Save">

  </form>

</div>

<?php $this->partial('admin/common/footer'); ?>
