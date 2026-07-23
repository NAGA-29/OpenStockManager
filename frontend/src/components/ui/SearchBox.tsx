import { type FormEvent } from 'react';
import './ui.css';

interface SearchBoxProps {
  /** 入力値（制御コンポーネント）。 */
  value: string;
  /** 入力変更ハンドラ。 */
  onChange: (value: string) => void;
  /** 検索実行（form submit）ハンドラ。 */
  onSubmit: () => void;
  placeholder?: string;
  /** 検索ボタンのラベル。既定は「検索」。 */
  buttonLabel?: string;
}

/**
 * 一覧画面で共通利用する検索ボックス（入力＋検索ボタン）。
 * 旧 `component/search_form` 相当。各ページで重複していたマークアップを集約。
 */
function SearchBox({
  value,
  onChange,
  onSubmit,
  placeholder = '検索キーワード',
  buttonLabel = '検索',
}: SearchBoxProps) {
  const handleSubmit = (e: FormEvent) => {
    e.preventDefault();
    onSubmit();
  };

  return (
    <div className="search-section">
      <form onSubmit={handleSubmit} className="search-form">
        <input
          type="text"
          placeholder={placeholder}
          value={value}
          onChange={(e) => onChange(e.target.value)}
          className="form-control"
        />
        <button type="submit" className="osm-btn osm-btn--primary">
          <i className="fas fa-search" aria-hidden="true" /> {buttonLabel}
        </button>
      </form>
    </div>
  );
}

export default SearchBox;
