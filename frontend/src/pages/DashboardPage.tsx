import { useAuth } from '@/auth/useAuth';

/**
 * ダッシュボードのプレースホルダ（保護ルート、共通レイアウト配下）。
 * 集計表示の本体は 3-2 で `GET /api/dashboard` を用いて実装する。
 */
function DashboardPage() {
  const { user } = useAuth();

  return (
    <>
      <h1>ダッシュボード</h1>
      <p>ようこそ、{user?.name} さん。</p>
    </>
  );
}

export default DashboardPage;
