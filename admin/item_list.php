<!-- =====================================================
  ヘッダー
===================================================== -->
<?php require_once __DIR__ . '/../layout/head.php'; ?>

<body>

  <!-- =====================================================
    ヘッダーセクション
  ===================================================== -->
  <?php require_once __DIR__ . '/../layout/header.php'; ?>


<!-- =====================================================
  メインレイアウト
===================================================== -->
<main class="container-fluid">

  <div class="row">

    <!-- =====================================================
      サイドバー
    ===================================================== -->
    <?php require_once __DIR__ . '/../layout/sidebar.php'; ?>

    <!-- =====================================================
      コンテンツエリア
    ===================================================== -->
    <div class="col-md-10 py-3">

      <h2 class="h4 mb-0">
        作品一覧
      </h2>

      <!-- =====================================================
        作品一覧テーブル
      ===================================================== -->
      <div class="table-responsive">

        <table class="table table-striped table-hover">

          <!-- テーブルヘッダー -->
          <thead>

            <tr>

              <th class="col-id">
                作品ID
              </th>

              <th class="col-thumb">
                サムネイル
              </th>

              <th class="col-title">
                タイトル
              </th>

              <th class="col-rating">
                評価平均点
              </th>

              <th class="col-action text-end">
                操作
              </th>

            </tr>

          </thead>



          <!-- テーブルデータ -->
          <tbody>


            <!-- ===== 作品1 ===== -->
            <tr>

              <td>
                101
              </td>

              <!-- サムネイル -->
              <td>
                <img src="../../images/thumbnail/item01.png"
                     alt="鋼鉄の境界線"
                     class="img-thumbnail item-thumb"
                >
              </td>


              <!-- タイトル（クリックで詳細モーダル） -->
              <td class="item-title-cell">

                <button
                  type="button"
                  class="btn btn-link p-0 text-start item-title-link"

                  data-bs-toggle="modal"
                  data-bs-target="#detailModal"

                  data-item-title="鋼鉄の境界線"
                  data-item-image="images/item01.jpg"
                  data-item-rating="4.2"
                  data-item-description="近未来都市を舞台に巨大企業と戦う主人公の物語。">

                  鋼鉄の境界線

                </button>

              </td>


              <!-- 評価 -->
              <td class="col-rating">

                <div class="rating-inline">

                  <span class="rating-score">
                    ★4.2
                  </span>

                </div>

              </td>


              <!-- 操作 -->
              <td class="text-end">

                <!-- 編集 -->
                <a href="item_edit.php?id=101"
                   class="btn btn-outline-primary btn-sm">
                  編集
                </a>

                <!-- 削除 -->
                <button
                  type="button"
                  class="btn btn-outline-danger btn-sm"

                  data-bs-toggle="modal"
                  data-bs-target="#deleteModal"

                  data-item-id="101"
                  data-item-title="鋼鉄の境界線">

                  削除

                </button>

              </td>

            </tr>


          </tbody>

        </table>

      </div>


      <!-- =====================================================
        ページネーション
      ===================================================== -->
      <?php require_once __DIR__ . '/../parts/pagination.php'; ?>

    </div><!-- /.col-md-10 -->

  </div><!-- /.row -->

</main>

<!-- =====================================================
  フッター
===================================================== -->
<?php require_once __DIR__ . '/../layout/footer.php'; ?>

<!-- =====================================================
  削除確認モーダル
===================================================== -->
<?php require_once __DIR__ . '/../parts/modal_item_delete.php'; ?>

<!-- =====================================================
  作品詳細モーダル
===================================================== -->
<?php require_once __DIR__ . '/../parts/modal_item_detail.php'; ?>

<!-- =====================================================
  Bootstrap JS
===================================================== -->
<?php require_once __DIR__ . '/../layout/scripts.php'; ?>

<script src="../../js/item_delete.js"></script>
<script src="../../js/item_list.js"></script>

</body>
</html>
