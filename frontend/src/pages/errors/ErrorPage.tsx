import { type ReactNode } from 'react';
import { Link } from 'react-router-dom';
import './error.css';

interface ErrorPageProps {
  /** HTTP ステータスなどの大きく表示するコード。 */
  code: string;
  /** 見出し。 */
  title: string;
  /** 本文（複数行可）。 */
  message: ReactNode;
  /** 補足情報（500 のタイムスタンプ等）。 */
  detail?: ReactNode;
  /** 「戻る」リンク先。既定はトップ（`/`）。 */
  homeTo?: string;
}

/**
 * 共通エラーページ（旧 `errors/*.blade.php` + `layouts/error` を移植）。
 * 認証レイアウトの外側で単体表示できるよう、自前のシェルを持つ。
 */
function ErrorPage({ code, title, message, detail, homeTo = '/' }: ErrorPageProps) {
  return (
    <main className="error-page">
      <div className="error-page__card">
        <div className="error-page__code">{code}</div>
        <h1 className="error-page__title">{title}</h1>
        <div className="error-page__message">{message}</div>
        {detail && <div className="error-page__detail">{detail}</div>}
        <Link to={homeTo} className="osm-btn osm-btn--primary error-page__home">
          <i className="fas fa-home" aria-hidden="true" /> トップページへ
        </Link>
      </div>
    </main>
  );
}

export default ErrorPage;
