
<aside id="MainMenuWrap">
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


      <!-- <li> -->
      <!--   <a href="#"> -->
      <!--     <span class="sidebar-nav-item-icon fa fa-github fa-lg"></span> -->
      <!--     <span class="sidebar-nav-item">metisMenu</span> -->
      <!--     <span class="fa arrow"></span> -->
      <!--   </a> -->
      <!--   <ul> -->
      <!--     <li> -->
      <!--     <a href="https://github.com/onokumus/metisMenu"> -->
      <!--       <span class="sidebar-nav-item-icon fa fa-code-fork"></span> -->
      <!--       Fork -->
      <!--     </a> -->
      <!--     </li> -->
      <!--   </ul> -->
      <!-- </li> -->
    </ul>
  </nav>
</aside>

<script type="text/javascript" charset="utf-8">
$(function() {
    $("#MainMenu").metisMenu()
});
</script>

