<!-- =====================================================
  Bootstrap JS
===================================================== -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- =====================================================
  ページ固有JS（呼び出し元で定義した $pageScripts を読み込む）
===================================================== -->
<?php if (!empty($pageScripts)): ?>
  <?php foreach ($pageScripts as $script): ?>
    <script src="<?= ADMIN_BASE_PATH ?>/admin/js/<?= htmlspecialchars($script, ENT_QUOTES, 'UTF-8') ?>"></script>
  <?php endforeach; ?>
<?php endif; ?>