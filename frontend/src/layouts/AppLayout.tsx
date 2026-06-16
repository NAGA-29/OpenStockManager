import { useState } from 'react';
import { Link, Outlet, useNavigate } from 'react-router-dom';
import { useAuth } from '@/auth/useAuth';
import Sidebar from './Sidebar';
import Footer from './Footer';
import './layout.css';

/**
 * 認証済み画面の共通レイアウト（旧 `layouts/app.blade.php` 相当）。
 * 上部ナビ（ブランド＋ユーザーメニュー）＋サイドバー＋本文（`<Outlet>`）＋フッター。
 * `ProtectedRoute` 配下のレイアウトルートとして利用する。
 */
function AppLayout() {
  const { user, logout } = useAuth();
  const navigate = useNavigate();
  const [menuOpen, setMenuOpen] = useState(false);

  const handleLogout = async () => {
    setMenuOpen(false);
    await logout();
    navigate('/login', { replace: true });
  };

  return (
    <div className="layout-root">
      <header className="osm-navbar">
        <Link to="/dashboard" className="osm-navbar__brand">
          <span className="brand-open">Open</span>
          <span className="brand-rest">StockManager</span>
        </Link>

        <div className="osm-navbar__menu">
          <button
            type="button"
            className="osm-navbar__user"
            aria-haspopup="true"
            aria-expanded={menuOpen}
            onClick={() => setMenuOpen((prev) => !prev)}
          >
            {user?.name}
            <i className="fas fa-caret-down" aria-hidden="true" />
          </button>
          {menuOpen && (
            <div className="osm-navbar__dropdown">
              <Link to="/profile" onClick={() => setMenuOpen(false)}>
                マイページ
              </Link>
              <button type="button" onClick={() => void handleLogout()}>
                ログアウト
              </button>
            </div>
          )}
        </div>
      </header>

      <div className="osm-body">
        <Sidebar />
        <main className="osm-main">
          <div className="osm-main__content">
            <Outlet />
          </div>
          <Footer />
        </main>
      </div>
    </div>
  );
}

export default AppLayout;
