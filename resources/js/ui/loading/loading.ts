const loading = document.querySelector(".loader");

if (loading) {
  window.addEventListener('pageshow', (event) => {
    // ページがキャッシュから読み込まれた時も含めて、常にローディングを非表示にする
    loading.classList.add('hide');
}, false);

  window.onbeforeunload = function() {
      loading.classList.remove('hide');
  };
}
