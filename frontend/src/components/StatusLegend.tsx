import './statusLegend.css';

/**
 * 端末一覧のステータスアイコン凡例（旧 `devices/components/status_legend.blade.php`）。
 * `<details>` による開閉式。
 */
function StatusLegend() {
  return (
    <details className="status-legend">
      <summary className="status-legend__toggle">
        <i className="fas fa-info-circle" aria-hidden="true" /> ステータス説明
      </summary>
      <div className="status-legend__body">
        <div className="status-legend__row">
          <span className="status-legend__item">
            <i className="fas fa-dove fa-lg" aria-hidden="true" /> レンタル中
          </span>
          <span className="status-legend__item">
            <i className="fas fa-yen-sign" aria-hidden="true" /> 販売済み
          </span>
          <span className="status-legend__item">
            <i className="fas fa-images" aria-hidden="true" /> 写真アリ
          </span>
        </div>
        <div className="status-legend__row">
          <span className="status-legend__item">
            <span className="status-legend__dot status-legend__dot--success" /> 貸出可能
          </span>
          <span className="status-legend__item">
            <span className="status-legend__dot status-legend__dot--primary" /> 貸出中
          </span>
          <span className="status-legend__item">
            <span className="status-legend__dot status-legend__dot--danger" /> 不具合 -
            動作に問題あり
          </span>
        </div>
      </div>
    </details>
  );
}

export default StatusLegend;
