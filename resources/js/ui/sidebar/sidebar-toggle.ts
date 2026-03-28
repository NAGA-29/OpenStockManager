const STORAGE_KEY = 'sidebar_collapsed';
const COLLAPSED_CLASS = 'collapsed';

const sidebarEl = document.getElementById('side_navbar') as HTMLElement | null;
const toggleBtn = document.getElementById('sidebar-toggle-btn') as HTMLButtonElement | null;

if (sidebarEl && toggleBtn) {
  const applyState = (collapsed: boolean, animate: boolean): void => {
    if (!animate) {
      sidebarEl.style.transition = 'none';
      requestAnimationFrame(() => { sidebarEl.style.transition = ''; });
    }
    sidebarEl.classList.toggle(COLLAPSED_CLASS, collapsed);
  };

  const closeOpenSubmenus = (): void => {
    sidebarEl.querySelectorAll<HTMLElement>('.collapse.show').forEach((panel) => {
      const bsCollapse = (window as any).bootstrap?.Collapse?.getInstance(panel);
      if (bsCollapse) {
        bsCollapse.hide();
      } else {
        panel.classList.remove('show');
      }
    });
  };

  const toggleSidebar = (): void => {
    const isCollapsed = sidebarEl.classList.contains(COLLAPSED_CLASS);
    if (!isCollapsed) closeOpenSubmenus();
    const next = !isCollapsed;
    applyState(next, true);
    try { localStorage.setItem(STORAGE_KEY, next ? '1' : '0'); } catch { /* ignore */ }
  };

  // ページ読み込み時に状態を復元（アニメーションなし）
  const stored = (() => {
    try { return localStorage.getItem(STORAGE_KEY) === '1'; } catch { return false; }
  })();
  applyState(stored, false);

  toggleBtn.addEventListener('click', toggleSidebar);
}
