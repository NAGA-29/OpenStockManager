import { Link } from 'react-router-dom';

/** 404 プレースホルダ（エラーページ群は 3-11 で整備）。 */
function NotFoundPage() {
  return (
    <main className="app-shell">
      <h1>404</h1>
      <p>ページが見つかりませんでした。</p>
      <Link to="/dashboard">ダッシュボードへ戻る</Link>
    </main>
  );
}

export default NotFoundPage;
