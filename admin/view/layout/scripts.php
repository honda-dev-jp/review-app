<!-- =====================================================
  Bootstrap JS
===================================================== -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<?php if (!empty($pageScripts)): ?>
  <?php foreach ($pageScripts as $script): ?>
    <script src="<?= ADMIN_BASE_PATH ?>/admin/js/<?= $script ?>"></script>
  <?php endforeach; ?>
<?php endif; ?>