<?php $this->partial('common/header') ?>

<a class="btn btn-default" href="<?=webpath('ProductController#index')?>">Return to product list</a>
<br />
<h1>Add Product detail for Product #<?=$product->id?></h1>

<ul>
  <?php foreach ($errors as $error) { ?>
    <li><?=$error?></li>
  <?php } ?>
</ul>

  <form action="<?=webpath('ProductDetailController#create', array($product->id))?>" method="post">

    <table class="table table-striped">
       <tr>
         <td><label for="title">Title</label></td>
         <td><input type="text" name="title" id="title" value="<?=$inputs->get('title', '')?>" /></td>
       </tr>
       <tr>
         <td><label for="barcode">Barcode</label></td>
         <td><input type="text" name="barcode" id="barcode" value="<?=$inputs->get('barcode', '')?>" /></td>
       </tr>
       <tr>
         <td><label for="barcode_type">Barcode Type</label></td>
         <td>
           <select name="barcode_type" id="barcode_type">
              <option value="ean">EAN</option>
              <option value="isbn10">ISBN-10</option>
              <option value="isbn13">ISBN-13</option>
           </select>
         </td>
       </tr>
       <tr>
         <td><label for="price">Price</label></td>
         <td><input type="text" name="price" id="price" /></td>
       </tr>
       <tr>
         <td><label for="short_desc">Short Description</label></td>
         <td>
           <textarea id="short_desc" name="short_desc" class="form-control" cols="30" rows="3">
             <?=$inputs->get('short_desc', '')?>
           </textarea>
         </td>
       </tr>
       <tr>
         <td><label for="description">Description</label></td>
         <td>
           <textarea id="description" name="description" class="form-control" cols="30" rows="10">
             <?=$inputs->get('description', '')?>
           </textarea>
         </td>
       </tr>
    </table>

  <input type="submit" class="btn btn-primary" name="submit" value="Update">
</form>

<br />

<?php $this->partial('common/footer') ?>

