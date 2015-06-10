<table class="table table-striped">
  <?php $name = isset($product) ? $product->name : '' ?>
  <tr class="form-group <?=($errors->get("product[name]", null, true) ? 'has-error' : '') ?>">
    <td><label for="name">Name</label></td>
    <td><input type="text" name="product[name]" value="<?=$inputs->get('product[name]', $name, true)?>" class="form-control" id="name"></td>
  </tr>



  <?php $price = isset($product) ? $product->price : '' ?>
  <tr>
    <td><label for="price">Price</label></td>
    <td><input type="text" name="product[price]" class="form-control" id="price" value="<?=$inputs->get("product[price]", $price, true)?>" /></td>
  </tr>

  <?php $short_desc = isset($product) ? $product->short_desc : '' ?>
  <tr>
    <td><label for="short_desc">Short Description</label></td>
    <td>
      <textarea id="short_desc" name="product[short_desc]" class="form-control" cols="30" rows="3"><?=$inputs->get("product[short_desc]", $short_desc, true)?></textarea>
    </td>
  </tr>


  <?php $description = isset($product) ? $product->description : '' ?>
  <tr>
    <td><label for="description">Description</label></td>
    <td>
      <textarea id="description" name="product[description]" class="form-control" cols="30" rows="10"><?=$inputs->get("product[description]", $description, true)?></textarea>
    </td>
  </tr>
</table>
