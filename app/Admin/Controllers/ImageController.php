<?php
namespace App\Admin\Controllers;

use App\Controllers\ApplicationController;
use App\Models\Image;

class ImageController extends ApplicationController 
{

  function delete($id) 
  {

    $this->respondTo('html', function() use($id) {

      $response = $this->getResponse();
      $flash    = $this->getSession()->getFlashBag();

      $image      = Image::find($id);
      $product_id = $image->product->id;

      if ($image == null) {
        $flash->add('errors', "Image #$id not found");
        $response->redirect('App\Admin\Controllers\ProductController', 'editPictures', [$product_id]);
        return;
      }

      try {

        $image->destroy();
        $response->redirect('App\Admin\Controllers\ProductController', 'editPictures', [$product_id]);

      } catch (\Exception $e) {

        $flash->add('errors', $e->getMessage());
        $response->redirect('App\Admin\Controllers\ProductController', 'editPictures', [$product_id]);
      }

    });
  }

  /**
   * Migrates products in a category to another
   */
  function migrate($id)
  {

  }

  function doDelete($id) 
  {

    $this->respondTo('html', function() use($id) {

      $response = $this->getResponse();
      $flash    = $this->getSession()->getFlashBag();

      try {

        Category::delete($id);
        $response->redirect('App\Admin\Controllers\CategoryController', 'index');

      } catch (ResourceNotFoundException $e) {

        $flash->add('errors', "Cannot find Category #$id");
        $response->redirect('App\Admin\Controllers\CategoryController', 'index');

      } catch (\Exception $e) {

        $flash->add('errors', 'Cannot delete Category #'.$id.' ('.$e->getMessage().')');
        $response->redirect('App\Admin\Controllers\CategoryController', 'index');
      }

    });
  }
}
