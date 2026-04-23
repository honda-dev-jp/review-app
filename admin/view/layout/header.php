<!-- =====================================================
  ヘッダー
===================================================== -->
<header class="bg-dark text-white py-3">

  <div class="container-fluid d-flex justify-content-between align-items-center">

    <!-- 管理画面タイトル -->
    <h1 class="h4 mb-0">管理画面</h1>

    <!-- ログインユーザー表示 -->
    <div class="small">
      ログイン中：<?= sanitize($_SESSION['name'] ?? '') ?>
    </div>

  </div>

</header>