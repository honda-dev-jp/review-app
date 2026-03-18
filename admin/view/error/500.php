<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>500 - サーバーエラー | 管理画面</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

  <div class="container py-5">
    <div class="row justify-content-center">
      <div class="col-md-6">

        <div class="card border-danger">
          <div class="card-header bg-danger text-white">
            <h1 class="h5 mb-0">500 Internal Server Error</h1>
          </div>
          <div class="card-body">
            <p class="mb-3">サーバー内部でエラーが発生しました。</p>
            <p class="text-muted small mb-4">
              エラーの詳細はサーバーログに記録されました。<br>
              しばらく時間をおいてから再度お試しください。
            </p>
            <a href="<?= defined('ADMIN_BASE_PATH') ? htmlspecialchars(ADMIN_BASE_PATH, ENT_QUOTES, 'UTF-8') : '/' ?>/admin/item_list.php"
               class="btn btn-outline-secondary btn-sm">
              作品一覧に戻る
            </a>
          </div>
        </div>

      </div>
    </div>
  </div>

</body>
</html>
