<?php

declare(strict_types=1);

/**
 * ページネーション表示パーツ（Bootstrap版）
 *
 * 設計方針：
 * - Controller から $page・$totalPages を受け取って Bootstrap の pagination を出力する
 * - 現在ページは active、前後2ページを表示し、範囲外は … で省略する
 * - 1ページのみの場合は出力しない
 *
 * 前提：
 * - $page       : int 現在のページ番号
 * - $totalPages : int 総ページ数
 * （呼び出し元で定義済みであること）
 */

// 1ページのみの場合は出力しない
if ($totalPages <= 1) {
    return;
}

$range    = 2;
$start    = max(1, $page - $range);
$end      = min($totalPages, $page + $range);
$midStart = max(2, $start);
$midEnd   = min($totalPages - 1, $end);
?>

<!-- =====================================================
  ページネーション
===================================================== -->
<nav aria-label="ページネーション">
  <ul class="pagination justify-content-center">

    <!-- 前へ -->
    <?php if ($page > 1): ?>
      <li class="page-item">
        <a class="page-link" href="?page=<?= $page - 1 ?>">前へ</a>
      </li>
    <?php else: ?>
      <li class="page-item disabled">
        <span class="page-link">前へ</span>
      </li>
    <?php endif; ?>

    <!-- 先頭ページ -->
    <li class="page-item <?= $page === 1 ? 'active' : '' ?>">
      <a class="page-link" href="?page=1">1</a>
    </li>

    <!-- 先頭側の省略 -->
    <?php if ($midStart > 2): ?>
      <li class="page-item disabled">
        <span class="page-link">…</span>
      </li>
    <?php endif; ?>

    <!-- 中央レンジ -->
    <?php for ($p = $midStart; $p <= $midEnd; $p++): ?>
      <li class="page-item <?= $p === $page ? 'active' : '' ?>">
        <a class="page-link" href="?page=<?= $p ?>"><?= $p ?></a>
      </li>
    <?php endfor; ?>

    <!-- 末尾側の省略 -->
    <?php if ($midEnd < $totalPages - 1): ?>
      <li class="page-item disabled">
        <span class="page-link">…</span>
      </li>
    <?php endif; ?>

    <!-- 末尾ページ（総ページ数が1より大きい場合のみ） -->
    <?php if ($totalPages > 1): ?>
      <li class="page-item <?= $page === $totalPages ? 'active' : '' ?>">
        <a class="page-link" href="?page=<?= $totalPages ?>"><?= $totalPages ?></a>
      </li>
    <?php endif; ?>

    <!-- 次へ -->
    <?php if ($page < $totalPages): ?>
      <li class="page-item">
        <a class="page-link" href="?page=<?= $page + 1 ?>">次へ</a>
      </li>
    <?php else: ?>
      <li class="page-item disabled">
        <span class="page-link">次へ</span>
      </li>
    <?php endif; ?>

  </ul>
</nav>
