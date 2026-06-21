import { type FormEvent, type ReactNode } from 'react';
import Modal from './Modal';

interface FormModalProps {
  open: boolean;
  title: ReactNode;
  onClose: () => void;
  /** フォーム送信ハンドラ（preventDefault は内部で実施）。 */
  onSubmit: () => void;
  /** 送信中フラグ（送信ボタンを無効化）。 */
  submitting?: boolean;
  /** 送信ボタンのラベル。既定は「更新」。 */
  submitLabel?: string;
  /** 送信中のラベル。既定は「更新中…」。 */
  submittingLabel?: string;
  /** キャンセルボタンのラベル。既定は「キャンセル」。 */
  cancelLabel?: string;
  /** フォーム本体（入力フィールド群）。 */
  children: ReactNode;
}

/**
 * フォーム用モーダル。`Modal` をベースに、フォーム＋キャンセル/送信フッターを共通化する。
 * 入力フィールドは `children` で受け取り、各画面固有のフォーム本体を差し込む。
 */
function FormModal({
  open,
  title,
  onClose,
  onSubmit,
  submitting = false,
  submitLabel = '更新',
  submittingLabel = '更新中…',
  cancelLabel = 'キャンセル',
  children,
}: FormModalProps) {
  const handleSubmit = (e: FormEvent) => {
    e.preventDefault();
    onSubmit();
  };

  return (
    <Modal open={open} title={title} onClose={onClose}>
      <form onSubmit={handleSubmit} noValidate>
        {children}
        <div className="register-actions">
          <button type="button" className="osm-btn" onClick={onClose}>
            {cancelLabel}
          </button>
          <button
            type="submit"
            className="osm-btn osm-btn--primary"
            disabled={submitting}
          >
            {submitting ? submittingLabel : submitLabel}
          </button>
        </div>
      </form>
    </Modal>
  );
}

export default FormModal;
