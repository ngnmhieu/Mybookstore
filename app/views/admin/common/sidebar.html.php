<div class="col-xs-2"  id="MainMenuWrap">
  <aside>
    <nav class="sidebar-nav">
      <ul id="MainMenu">
        <li>
        <a href="#">
          <span class="sidebar-nav-item-icon fa fa-cubes fa-lg fa-fw"></span>
          <span class="sidebar-nav-item">Inventory</span>
          <span class="fa arrow"></span>
        </a>
        <ul>
          <li>
          <a href="<?=webpath('Admin\ProductController#index')?>">
            <span class="sidebar-nav-item">Products</span>
          </a>
          </li>
        </ul>
        </li>
        <li>
        <a href="<?=webpath('Admin\ProductController#index')?>">
          <span class="sidebar-nav-item-icon fa fa-book fa-lg fa-fw"></span>
          <span class="sidebar-nav-item">Categories</span>
        </a>
        </li>
        <li>
        <a href="<?=webpath('Admin\ProductController#index')?>">
          <span class="sidebar-nav-item-icon fa fa-user-secret fa-lg fa-fw"></span>
          <span class="sidebar-nav-item">Authors</span>
        </a>
        </li>
        <li>
        <a href="<?=webpath('Admin\ProductController#index')?>">
          <span class="sidebar-nav-item-icon fa fa-users fa-lg fa-fw"></span>
          <span class="sidebar-nav-item">Users</span>
        </a>
        </li>
      </ul>
    </nav>
  </aside>
</div>

<script type="text/javascript" charset="utf-8">
$(function() {
  $("#MainMenu").metisMenu()
  });
</script>
