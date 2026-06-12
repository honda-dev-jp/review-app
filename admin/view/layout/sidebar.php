<!-- =====================================================
  サイドバー
===================================================== -->
<nav class="col-md-2 border-end bg-light admin-sidebar">

  <div class="navbar admin-sidebar-toggle">
    <span class="mb-0 h6">管理メニュー</span>
    <button
      class="navbar-toggler"
      type="button"
      data-bs-toggle="collapse"
      data-bs-target="#adminSidebarMenu"
      aria-controls="adminSidebarMenu"
      aria-expanded="false"
      aria-label="管理メニューを開閉"
    >
      <span class="navbar-toggler-icon"></span>
    </button>
  </div>

  <div class="collapse d-md-block" id="adminSidebarMenu">
    <div class="admin-sidebar-content">

      <!-- 管理メニュー -->
      <ul class="nav flex-column">

        <li class="nav-item mb-2">
          <a class="nav-link active" href="<?= ADMIN_BASE_PATH ?>/admin/item_list.php">
            作品一覧
          </a>
        </li>

        <li class="nav-item mb-2">
          <a class="nav-link" href="<?= ADMIN_BASE_PATH ?>/admin/item_add.php">
            作品追加
          </a>
        </li>

        <li class="nav-item mb-2">
          <a class="nav-link" href="<?= ADMIN_BASE_PATH ?>/mypage/mypage.php">
            マイページに戻る
          </a>
        </li>

      </ul>

      <hr>

      <!-- ログアウト -->
      <form action="<?= ADMIN_BASE_PATH ?>/login/logout.php" method="post">
        <?php embedCSRFToken(); ?>
        <button type="submit" class="btn btn-outline-secondary w-100">
          ログアウト
        </button>
      </form>

    </div>
  </div>

</nav>
