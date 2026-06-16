export type ToastVariant = 'info' | 'success' | 'warning' | 'danger';

export interface ToastItem {
  id: number;
  message: string;
  variant: ToastVariant;
}

export interface ToastContextValue {
  /** トーストを表示する（既定で 4 秒後に自動消去）。 */
  show: (message: string, variant?: ToastVariant) => void;
}
