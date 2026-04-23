<!-- =====================================================
  サイドバー
===================================================== -->
<nav class="col-md-2 border-end py-3 bg-light admin-sidebar">

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

</nav>