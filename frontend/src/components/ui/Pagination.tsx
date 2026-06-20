import './ui.css';

interface PaginationProps {
  /** 現在のページ（1 始まり）。 */
  page: number;
  /** 最終ページ。 */
  lastPage: number;
  /** ページ変更ハンドラ。 */
  onChange: (page: number) => void;
}

/**
 * 一覧画面で共通利用するページネーション（前へ / 現在 / 次へ）。
 * 1 ページ以下のときは何も描画しない。各ページで重複していたマークアップを集約。
 */
function Pagination({ page, lastPage, onChange }: PaginationProps) {
  if (lastPage <= 1) {
    return null;
  }

  return (
    <div className="search-pagination">
      <button
        type="button"
        onClick={() => onChange(Math.max(1, page - 1))}
        disabled={page <= 1}
        className="osm-btn osm-btn--small"
      >
        &lt; 前へ
      </button>
      <span className="search-pagination__info">
        {page} / {lastPage}
      </span>
      <button
        type="button"
        onClick={() => onChange(Math.min(lastPage, page + 1))}
        disabled={page >= lastPage}
        className="osm-btn osm-btn--small"
      >
        次へ &gt;
      </button>
    </div>
  );
}

export default Pagination;
