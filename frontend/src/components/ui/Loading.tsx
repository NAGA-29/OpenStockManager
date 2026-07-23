import './ui.css';

/** 汎用ローディング表示（スピナー＋ラベル）。 */
function Loading({ label = '読み込み中…' }: { label?: string }) {
  return (
    <div className="ui-loading" role="status" aria-live="polite">
      <span className="ui-loading__spinner" aria-hidden="true" />
      <span>{label}</span>
    </div>
  );
}

export default Loading;
