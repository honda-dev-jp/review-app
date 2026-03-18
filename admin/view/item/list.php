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

      <h2 class="mb-4">作品一覧</h2>

      <!-- フラッシュメッセージ -->
      <?php require __DIR__ . '/../parts/flash_message.php'; ?>

      <!-- 作品一覧テーブル -->
      <div class="table-responsive">
        <table class="table table-hover align-middle">
          <thead class="table-dark">
            <tr>
              <th>ID</th>
              <th>サムネイル</th>
              <th>タイトル</th>
              <th>平均評価</th>
              <th>レビュー数</th>
              <th>操作</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($items)): ?>
              <tr>
                <td colspan="6" class="text-center text-muted">作品が登録されていません。</td>
              </tr>
            <?php else: ?>
              <?php foreach ($items as $item): ?>
                <tr>
                  <td><?= $item['item_id'] ?></td>

                  <!-- サムネイル -->
                  <td>
                    <?php if (!empty($item['image'])): ?>
                      <img
                        src="<?= ADMIN_BASE_PATH ?>/images/thumbnail/<?= sanitize($item['image']) ?>"
                        alt="<?= sanitize($item['title']) ?>"
                        class="img-thumbnail"
                        style="width: 60px; height: 60px; object-fit: cover;"
                      >
                    <?php else: ?>
                      <img
                        src="<?= ADMIN_BASE_PATH ?>/images/no_image/no_image.png"
                        alt="画像なし"
                        class="img-thumbnail"
                        style="width: 60px; height: 60px; object-fit: cover;"
                      >
                    <?php endif; ?>
                  </td>

                  <!-- タイトル -->
                  <td><?= sanitize($item['title']) ?></td>

                  <!-- 平均評価 -->
                  <td>
                    <?= $item['avg_rating'] !== null
                        ? number_format((float) $item['avg_rating'], 1)
                        : '未評価'
                  ?>
                  </td>

                  <!-- レビュー数 -->
                  <td><?= $item['rating_count'] ?></td>

                  <!-- 操作ボタン -->
                  <td>
                    <div class="d-flex gap-2">

                      <!-- 詳細ボタン（モーダル） -->
                      <button
                        type="button"
                        class="btn btn-outline-secondary btn-sm"
                        data-bs-toggle="modal"
                        data-bs-target="#detailModal"
                        data-item-title="<?= sanitize($item['title']) ?>"
                        data-item-image="<?= !empty($item['image'])
                          ? ADMIN_BASE_PATH . '/images/thumbnail/' . sanitize($item['image'])
                          : ADMIN_BASE_PATH . '/images/no_image/no_image.png'
                  ?>"
                        data-item-rating="<?= $item['avg_rating'] !== null
                      ? number_format((float) $item['avg_rating'], 1)
                      : '未評価'
                  ?>"
                        data-item-description=""
                      >
                        詳細
                      </button>

                      <!-- 編集ボタン -->
                      <a
                        href="<?= ADMIN_BASE_PATH ?>/admin/item_edit.php?item_id=<?= $item['item_id'] ?>"
                        class="btn btn-outline-primary btn-sm"
                      >
                        編集
                      </a>

                      <!-- 削除ボタン（モーダル） -->
                      <button
                        type="button"
                        class="btn btn-outline-danger btn-sm"
                        data-bs-toggle="modal"
                        data-bs-target="#deleteModal"
                        data-item-id="<?= $item['item_id'] ?>"
                        data-item-title="<?= sanitize($item['title']) ?>"
                      >
                        削除
                      </button>

                    </div>
                  </td>

                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

      <!-- ページネーション -->
      <?php require __DIR__ . '/../parts/pagination.php'; ?>

    </main>
  </div>
</div>

<!-- =====================================================
  作品詳細モーダル
===================================================== -->
<div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title" id="detailModalLabel">作品詳細</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="閉じる"></button>
      </div>

      <div class="modal-body">
        <div class="d-flex gap-4">
          <img id="detailItemImage" src="" alt="" style="width: 120px; height: 120px; object-fit: cover;">
          <div>
            <h6 id="detailItemTitle" class="fw-bold"></h6>
            <p class="mb-1">平均評価：<span id="detailItemRating"></span></p>
            <p id="detailItemDescription" class="text-muted small"></p>
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">閉じる</button>
      </div>

    </div>
  </div>
</div>

<!-- =====================================================
  削除確認モーダル
===================================================== -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title" id="deleteModalLabel">削除確認</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="閉じる"></button>
      </div>

      <div class="modal-body">
        <p>以下の作品を削除します。よろしいですか？</p>
        <p class="fw-bold" id="modalItemTitle"></p>
      </div>

      <div class="modal-footer">
        <form method="post" action="<?= ADMIN_BASE_PATH ?>/admin/item_delete.php">
          <?php embedCSRFToken(); ?>
          <input type="hidden" name="item_id" id="modalItemId">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
          <button type="submit" class="btn btn-danger">削除する</button>
        </form>
      </div>

    </div>
  </div>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>
<?php require __DIR__ . '/../layout/scripts.php'; ?>

</body>
</html>
