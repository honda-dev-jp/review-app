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

detailModal.addEventListener('show.bs.modal', function (event) {

    const button = event.relatedTarget;

    document.getElementById('detailItemTitle').textContent =
        button.getAttribute('data-item-title');

    document.getElementById('detailItemImage').src =
        button.getAttribute('data-item-image');

    // 評価：★CSS幅方式でレンダリング（innerHTML不使用・DOM操作で構築）
    const rating = button.getAttribute('data-item-rating');
    const ratingEl = document.getElementById('detailItemRating');

    // 既存の子要素をクリア
    while (ratingEl.firstChild) {
        ratingEl.removeChild(ratingEl.firstChild);
    }

    if (rating === '未評価') {
        ratingEl.textContent = '未評価';
    } else {
        const val = parseFloat(rating);
        const percent = (val / 5) * 100;

        // 外側ラッパー
        const wrapper = document.createElement('span');
        wrapper.style.display = 'inline-flex';
        wrapper.style.alignItems = 'center';
        wrapper.style.gap = '4px';

        // 星コンテナ
        const starContainer = document.createElement('span');
        starContainer.style.position = 'relative';
        starContainer.style.display = 'inline-block';
        starContainer.style.fontSize = '16px';
        starContainer.style.lineHeight = '1';

        // 背景（グレー★）
        const starBg = document.createElement('span');
        starBg.style.color = '#ccc';
        starBg.textContent = '★★★★★';

        // 前景（黄色★・幅で切り取る）
        const starFg = document.createElement('span');
        starFg.style.color = '#f0a500';
        starFg.style.position = 'absolute';
        starFg.style.top = '0';
        starFg.style.left = '0';
        starFg.style.overflow = 'hidden';
        starFg.style.whiteSpace = 'nowrap';
        starFg.style.width = percent + '%';
        starFg.textContent = '★★★★★';

        starContainer.appendChild(starBg);
        starContainer.appendChild(starFg);

        // 数値（丸めなし）
        const valEl = document.createElement('small');
        valEl.style.color = '#555';
        valEl.textContent = '(' + val + ')';

        wrapper.appendChild(starContainer);
        wrapper.appendChild(valEl);
        ratingEl.appendChild(wrapper);
    }

    document.getElementById('detailItemDescription').textContent =
        button.getAttribute('data-item-description');

});
