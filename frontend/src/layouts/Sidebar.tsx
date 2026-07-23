import { useState } from 'react';
import { NavLink, useLocation } from 'react-router-dom';
import { useAuth } from '@/auth/useAuth';

interface NavLeaf {
  label: string;
  to: string;
  /** 旧 web.php で `admin` ミドルウェア保護のルートは管理者のみ表示。 */
  adminOnly?: boolean;
}

interface NavSection {
  id: string;
  label: string;
  icon: string;
  children: NavLeaf[];
}

/**
 * サイドバーのメニュー定義（旧 `layouts/sidebar.blade.php` を踏襲）。
 * 遷移先は React 側のルート（Phase 3 で順次実装）。
 */
const NAV_SECTIONS: NavSection[] = [
  {
    id: 'inventory',
    label: '在庫一覧',
    icon: 'fa fa-tablet-alt',
    children: [
      { label: '個別管理', to: '/inventory/units/STB' },
      { label: '数量管理', to: '/inventory/stocks' },
      { label: '端末検索', to: '/devices/search' },
    ],
  },
  {
    id: 'procedure',
    label: '手続き',
    icon: 'fa fa-file-signature',
    children: [
      { label: 'レンタル', to: '/rental' },
      { label: '販売', to: '/sale' },
    ],
  },
  {
    id: 'history',
    label: '履歴',
    icon: 'fa fa-history',
    children: [
      { label: '全体', to: '/history' },
      { label: 'レンタル', to: '/rental/history' },
      { label: '販売', to: '/sale/history' },
    ],
  },
  {
    id: 'data',
    label: 'データ一覧',
    icon: 'fas fa-chart-pie',
    children: [
      { label: 'スペックデータ', to: '/device/file/spec' },
      { label: 'ベンチマーク', to: '/device/file/benchmark' },
      { label: 'クライアント', to: '/clients' },
      { label: '担当者', to: '/contacts' },
    ],
  },
  {
    id: 'register',
    label: '登録',
    icon: 'fas fa-address-book',
    children: [
      { label: '機材', to: '/device/register' },
      { label: '機材（CSV一括）', to: '/device/register/multi' },
      { label: 'クライアント', to: '/clients/register' },
      { label: '担当者', to: '/contacts/register' },
    ],
  },
  {
    id: 'system',
    label: '設定',
    icon: 'fas fa-cogs',
    children: [
      { label: 'ユーザー', to: '/users', adminOnly: true },
      { label: '機材カテゴリ', to: '/settings/categories', adminOnly: true },
      { label: 'カスタムフィールド', to: '/settings/fields', adminOnly: true },
      { label: '外部連携', to: '/settings/mail' },
    ],
  },
];

function linkClass({ isActive }: { isActive: boolean }): string {
  return isActive ? 'osm-sidebar__link active' : 'osm-sidebar__link';
}

function Sidebar() {
  const { user } = useAuth();
  const { pathname } = useLocation();
  const isAdmin = user?.is_admin ?? false;

  const [collapsed, setCollapsed] = useState(false);
  // 現在のパスを含むセクションは初期表示で開いておく。
  const [openSections, setOpenSections] = useState<Record<string, boolean>>(() => {
    const initial: Record<string, boolean> = {};
    for (const section of NAV_SECTIONS) {
      initial[section.id] = section.children.some((child) =>
        pathname.startsWith(child.to),
      );
    }
    return initial;
  });

  const toggleSection = (id: string) => {
    setOpenSections((prev) => ({ ...prev, [id]: !prev[id] }));
  };

  return (
    <nav className={collapsed ? 'osm-sidebar collapsed' : 'osm-sidebar'}>
      <ul className="osm-sidebar__nav">
        <li>
          <NavLink to="/dashboard" className={linkClass}>
            <i className="fa fa-home" aria-hidden="true" />
            <span className="osm-sidebar__label">ダッシュボード</span>
          </NavLink>
        </li>

        {NAV_SECTIONS.map((section) => {
          const items = section.children.filter(
            (child) => !child.adminOnly || isAdmin,
          );
          if (items.length === 0) {
            return null;
          }
          const isOpen = openSections[section.id] ?? false;
          return (
            <li key={section.id}>
              <button
                type="button"
                className="osm-sidebar__section-toggle"
                aria-expanded={isOpen}
                onClick={() => toggleSection(section.id)}
              >
                <i className={section.icon} aria-hidden="true" />
                <span className="osm-sidebar__label">{section.label}</span>
                <i
                  className="fas fa-sort-down osm-sidebar__caret"
                  aria-hidden="true"
                />
              </button>
              {isOpen && (
                <ul className="osm-sidebar__sublist">
                  {items.map((child) => (
                    <li key={child.to}>
                      <NavLink to={child.to} className={linkClass}>
                        <span className="osm-sidebar__label">{child.label}</span>
                      </NavLink>
                    </li>
                  ))}
                </ul>
              )}
            </li>
          );
        })}
      </ul>

      <button
        type="button"
        className="osm-sidebar__toggle"
        aria-label="サイドバーを折りたたむ"
        onClick={() => setCollapsed((prev) => !prev)}
      >
        <i className="fas fa-chevron-left" aria-hidden="true" />
      </button>
    </nav>
  );
}

export default Sidebar;
