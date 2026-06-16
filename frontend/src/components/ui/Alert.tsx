import type { ReactNode } from 'react';
import './ui.css';

export type AlertVariant = 'info' | 'success' | 'warning' | 'danger';

/** 汎用アラート（旧 Bootstrap alert 相当）。 */
function Alert({
  variant = 'info',
  children,
}: {
  variant?: AlertVariant;
  children: ReactNode;
}) {
  return (
    <div className={`ui-alert ui-alert--${variant}`} role="alert">
      {children}
    </div>
  );
}

export default Alert;
