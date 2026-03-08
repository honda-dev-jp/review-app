<!-- =====================================================
  削除確認モーダル
===================================================== -->
<div class="modal fade" id="deleteModal" tabindex="-1">

  <div class="modal-dialog">

    <div class="modal-content">


      <div class="modal-header">

        <h5 class="modal-title">
          削除確認
        </h5>

        <button type="button"
                class="btn-close"
                data-bs-dismiss="modal">
        </button>

      </div>


      <div class="modal-body">

        <p>
          「<span id="modalItemTitle"></span>」を削除します。
        </p>

      </div>


      <div class="modal-footer">

        <button class="btn btn-secondary"
                data-bs-dismiss="modal">
          キャンセル
        </button>

        <form action="item_delete.php" method="post">

          <input type="hidden"
                 name="id"
                 id="modalItemId">

          <button class="btn btn-danger">
            削除する
          </button>

        </form>

      </div>

    </div>

  </div>

</div>