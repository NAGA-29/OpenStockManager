import { type ReactNode } from 'react';
import '@/pages/login.css';

interface AuthLayoutProps {
  /** フォームパネルに差し込む内容（見出し・フォーム等）。 */
  children: ReactNode;
}

/**
 * 認証画面用の共通レイアウト（旧 `layouts/auth.blade.php` 相当）。
 * 左のブランディングパネル＋右のフォームパネルの 2 カラムカードを提供し、
 * フォーム側は `children` で差し込む。ログイン以外の認証画面でも再利用する。
 */
function AuthLayout({ children }: AuthLayoutProps) {
  return (
    <div className="login-screen">
      <div className="login-card">
        <aside className="login-card__aside">
          <span className="login-card__badge">Device Manager</span>
          <h2>Welcome</h2>
          <p>管理者から発行されたアカウント情報でログインしてください。</p>
          <ul className="login-card__features">
            <li>
              <i className="fas fa-tablet-alt" aria-hidden="true" />
              <span>デバイス管理・ステータス確認を一元化</span>
            </li>
            <li>
              <i className="fas fa-history" aria-hidden="true" />
              <span>履歴情報を安全にトラッキング</span>
            </li>
          </ul>
        </aside>

        <div className="login-card__form">{children}</div>
      </div>
    </div>
  );
}

export default AuthLayout;
