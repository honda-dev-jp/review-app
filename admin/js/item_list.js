/**
 * 作品詳細モーダル表示処理
 *
 * Bootstrap の `show.bs.modal` イベントを利用し、
 * クリックされた作品ボタンの data 属性から
 * 作品情報を取得してモーダルに表示する。
 *
 * 処理概要
 * - 詳細ボタンを押す
 * - Bootstrap が `show.bs.modal` を発火
 * - event.relatedTarget からクリックされたボタン取得
 * - data属性から作品情報を取得
 * - モーダル内のDOM要素へ値をセット
 *
 * 使用 data 属性
 * @attribute data-item-title        作品タイトル
 * @attribute data-item-image        作品画像URL
 * @attribute data-item-rating       評価値
 * @attribute data-item-description  作品説明
 *
 * 更新するDOM
 * @element detailItemTitle        タイトル表示
 * @element detailItemImage        画像表示
 * @element detailItemRating       評価表示
 * @element detailItemDescription  説明表示
 *
 * @param {Event} event Bootstrap モーダル表示イベント
 */
const detailModal = document.getElementById('detailModal');

detailModal.addEventListener('show.bs.modal', function(event){

  const button = event.relatedTarget;

  document.getElementById('detailItemTitle').textContent =
    button.getAttribute('data-item-title');

  document.getElementById('detailItemImage').src =
    button.getAttribute('data-item-image');

  document.getElementById('detailItemRating').textContent =
    button.getAttribute('data-item-rating');

  document.getElementById('detailItemDescription').textContent =
    button.getAttribute('data-item-description');

});