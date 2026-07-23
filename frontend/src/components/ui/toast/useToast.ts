import { useContext } from 'react';
import { ToastContext } from './context';
import type { ToastContextValue } from './types';

/** トースト表示フック。`ToastProvider` 配下で使用する。 */
export function useToast(): ToastContextValue {
  const ctx = useContext(ToastContext);
  if (!ctx) {
    throw new Error('useToast は ToastProvider の内側で使用してください');
  }
  return ctx;
}
