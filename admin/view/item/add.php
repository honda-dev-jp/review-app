<?php require __DIR__ . '/../layout/head.php'; ?>
<body>

<?php require __DIR__ . '/../layout/header.php'; ?>

<div class="container-fluid">
  <div class="row">

    <?php require __DIR__ . '/../layout/sidebar.php'; ?>

    <!-- =====================================================
      メインコンテンツ
    ===================================================== -->
    <main class="col-md-10 py-4">

      <h2 class="mb-4">作品追加</h2>

      <!-- フラッシュメッセージ -->
      <?php require __DIR__ . '/../parts/flash_message.php'; ?>

      <!-- バリデーションエラー -->
      <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
          <ul class="mb-0">
            <?php foreach ($errors as $error): ?>
              <li><?= sanitize($error) ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>

      <div class="card">
        <div class="card-body">

          <form method="post" action="<?= ADMIN_BASE_PATH ?>/admin/item_add.php" enctype="multipart/form-data">

            <?php embedCSRFToken(); ?>

            <!-- タイトル -->
            <div class="mb-3">
              <label for="title" class="form-label fw-bold">
                タイトル <span class="text-danger">*</span>
              </label>
              <input
                type="text"
                class="form-control"
                id="title"
                name="title"
                value="<?= sanitize($old['title'] ?? '') ?>"
                maxlength="80"
                required
              >
              <div class="form-text">80文字以内で入力してください。</div>
            </div>

            <!-- 説明文 -->
            <div class="mb-3">
              <label for="description" class="form-label fw-bold">説明文</label>
              <textarea
                class="form-control"
                id="description"
                name="description"
                rows="6"
              ><?= sanitize($old['description'] ?? '') ?></textarea>
            </div>

            <!-- サムネイル画像 -->
            <div class="mb-4">
              <label for="image" class="form-label fw-bold">サムネイル画像</label>
              <input
                type="file"
                class="form-control"
                id="image"
                name="image"
                accept="image/jpeg,image/png,image/gif,image/webp"
              >
              <div class="form-text">jpeg / png / gif / webp 形式・5MB以内</div>
            </div>

            <!-- ボタン -->
            <div class="d-flex gap-2">
              <button type="submit" class="btn btn-primary">登録する</button>
              <a href="<?= ADMIN_BASE_PATH ?>/admin/item_list.php" class="btn btn-outline-secondary">キャンセル</a>
            </div>

          </form>

        </div>
      </div>

    </main>
  </div>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>
<?php require __DIR__ . '/../layout/scripts.php'; ?>

</body>
</html>
