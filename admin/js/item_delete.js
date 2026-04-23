/**
 * 削除モーダル表示処理
 *
 * Bootstrap の `show.bs.modal` イベントを利用して、
 * 削除ボタンに設定された data 属性から
 * 作品IDと作品タイトルを取得し、モーダルにセットする。
 *
 * 処理概要
 * - 削除ボタンを押す
 * - Bootstrap が `show.bs.modal` を発火
 * - event.relatedTarget からクリックされたボタンを取得
 * - data属性から作品情報を取得
 * - モーダルの hidden input と表示タイトルに値を設定
 *
 * 使用 data 属性
 * @attribute data-item-id      削除対象の作品ID
 * @attribute data-item-title   削除対象の作品タイトル
 *
 * 更新するDOM
 * @element modalItemId      hidden input（削除処理用）
 * @element modalItemTitle   モーダル内の作品タイトル表示
 *
 * @param {Event} event Bootstrap モーダル表示イベント
 */
const deleteModal = document.getElementById('deleteModal');

deleteModal.addEventListener('show.bs.modal', function(event){

  const button = event.relatedTarget;

  const itemId = button.getAttribute('data-item-id');
  const itemTitle = button.getAttribute('data-item-title');

  document.getElementById('modalItemId').value = itemId;
  document.getElementById('modalItemTitle').textContent = itemTitle;

});