<!-- =====================================================
  作品詳細モーダル
===================================================== -->
<div class="modal fade" id="detailModal" tabindex="-1">

  <div class="modal-dialog modal-lg">

    <div class="modal-content">

      <div class="modal-header">

        <h5 class="modal-title">
          作品詳細
        </h5>

        <button type="button"
                class="btn-close"
                data-bs-dismiss="modal">
        </button>

      </div>


      <div class="modal-body">

        <div class="row g-3">

          <!-- 画像 -->
          <div class="col-md-4">

            <img id="detailItemImage"
                 class="img-fluid img-thumbnail">

          </div>

          <!-- 詳細 -->
          <div class="col-md-8">

            <h3 id="detailItemTitle" class="h5"></h3>

            <p>
              評価平均点：
              <span id="detailItemRating"></span>
            </p>

            <p id="detailItemDescription"></p>

          </div>

        </div>

      </div>

    </div>

  </div>

</div>