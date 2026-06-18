import { useState, useRef } from 'react';
import { AxiosError } from 'axios';
import Alert from '@/components/ui/Alert';
import Loading from '@/components/ui/Loading';
import DataTable, { type Column } from '@/components/ui/DataTable';
import { useToast } from '@/components/ui/toast/useToast';
import {
  useUploadDeviceMulti,
  useStoreDeviceMulti,
  type DevicePreview,
} from '@/features/devices/useDeviceMulti';
import './register.css';

interface ValidationErrorResponse {
  message?: string;
  errors?: Record<string, string[]>;
}

type PageState = 'upload' | 'confirm' | 'completed';

/**
 * 複数端末 CSV 一括登録画面。
 * 旧 `register_device/register_device_multi.blade.php` の CSV アップロードフローを React 化。
 */
function DeviceRegisterMultiPage() {
  const uploadMutation = useUploadDeviceMulti();
  const storeMutation = useStoreDeviceMulti();
  const { show } = useToast();
  const fileInputRef = useRef<HTMLInputElement>(null);

  const [pageState, setPageState] = useState<PageState>('upload');
  const [devices, setDevices] = useState<DevicePreview[]>([]);
  const [uploadError, setUploadError] = useState<string | null>(null);
  const [completedCount, setCompletedCount] = useState(0);

  const handleFileChange = async (event: React.ChangeEvent<HTMLInputElement>) => {
    const file = event.target.files?.[0];
    if (!file) return;

    setUploadError(null);

    try {
      const result = await uploadMutation.mutateAsync(file);
      setDevices(result);
      setPageState('confirm');
    } catch (err) {
      const axiosErr = err as AxiosError<ValidationErrorResponse>;
      const responseData = axiosErr.response?.data;

      if (axiosErr.response?.status === 422) {
        setUploadError(responseData?.message ?? 'ファイル形式が正しくありません。');
      } else {
        setUploadError(responseData?.message ?? 'CSV 解析に失敗しました。');
      }
      show('CSV のアップロードに失敗しました。', 'danger');
    }
  };

  const handleConfirmSubmit = async () => {
    try {
      const result = await storeMutation.mutateAsync(devices);
      setCompletedCount(result.count);
      setPageState('completed');
      show(result.message, 'success');
    } catch (err) {
      const axiosErr = err as AxiosError<ValidationErrorResponse>;
      const responseData = axiosErr.response?.data;
      show(responseData?.message ?? '登録に失敗しました。', 'danger');
    }
  };

  const handleBackToUpload = () => {
    setPageState('upload');
    setDevices([]);
    setUploadError(null);
    if (fileInputRef.current) {
      fileInputRef.current.value = '';
    }
  };

  if (pageState === 'upload') {
    return (
      <>
        <div className="page-bar">
          <i className="fas fa-plus" aria-hidden="true" />
          複数端末登録
        </div>

        <div className="register-card">
          {uploadError && <Alert variant="danger">{uploadError}</Alert>}

          <p className="register-note">
            登録希望の端末を記載したファイルをアップロードしてください。
          </p>
          <p className="register-note" style={{ fontSize: '0.9rem', color: '#6b7280' }}>
            ファイルの解析・登録には時間が掛かります。アップロード後はしばらくお待ちください。
          </p>

          <div className="register-field">
            <label htmlFor="device_register_file">添付ファイル (CSV)</label>
            <input
              ref={fileInputRef}
              id="device_register_file"
              type="file"
              accept=".csv"
              onChange={handleFileChange}
              disabled={uploadMutation.isPending}
            />
            {uploadMutation.isPending && <p style={{ color: '#3b82f6' }}>解析中...</p>}
          </div>

          <div className="register-actions">
            <button
              type="button"
              className="osm-btn"
              disabled={uploadMutation.isPending}
              onClick={() => fileInputRef.current?.click()}
            >
              ファイルを選択
            </button>
          </div>
        </div>
      </>
    );
  }

  if (pageState === 'confirm') {
    const columns: Column<DevicePreview>[] = [
      { key: 'device_id', header: '端末ID' },
      { key: 'device_type', header: '区分' },
      { key: 'device_name', header: '端末名' },
      { key: 'device_serial', header: 'シリアル' },
      { key: 'first_work_date_at', header: '稼働日' },
      { key: 'purchase_date_at', header: '購入日' },
      { key: 'option', header: 'オプション' },
      {
        key: 'defective',
        header: '不具合',
        render: (row) =>
          row.defective ? (
            <i className="fas fa-check-circle" style={{ color: '#ef4444' }} />
          ) : null,
      },
      {
        key: 'not_for_sale',
        header: '販売不可',
        render: (row) =>
          row.not_for_sale ? (
            <i className="fas fa-check-circle" style={{ color: '#ef4444' }} />
          ) : null,
      },
      { key: 'note', header: 'ノート' },
    ];

    return (
      <>
        <div className="page-bar">
          <i className="fas fa-check" aria-hidden="true" />
          複数端末登録 ＜ 確認 ＞
        </div>

        <div className="register-card">
          <p className="register-note">以下の登録内容で登録します。よろしいですか？</p>
          <p className="register-note" style={{ fontSize: '0.9rem', color: '#6b7280' }}>
            ※処理に時間がかかる場合があります。
          </p>

          <div style={{ overflowX: 'auto', marginBottom: '1.5rem' }}>
            <DataTable
              columns={columns}
              rows={devices}
              rowKey={(row) => row.device_id}
              empty="登録するデバイスがありません。"
            />
          </div>

          <div className="register-actions">
            <button
              type="button"
              className="osm-btn"
              disabled={storeMutation.isPending}
              onClick={handleConfirmSubmit}
            >
              {storeMutation.isPending ? '登録中…' : '登録'}
            </button>
            <button
              type="button"
              className="osm-btn"
              style={{ backgroundColor: '#dc2626', marginLeft: '0.5rem' }}
              disabled={storeMutation.isPending}
              onClick={handleBackToUpload}
            >
              キャンセル
            </button>
          </div>
        </div>
      </>
    );
  }

  if (pageState === 'completed') {
    return (
      <>
        <div className="page-bar">
          <i className="fas fa-check-circle" aria-hidden="true" />
          登録完了
        </div>

        <div className="register-card">
          <Alert variant="success">
            {completedCount}台の端末を登録しました。
          </Alert>

          <div className="register-actions">
            <button type="button" className="osm-btn" onClick={handleBackToUpload}>
              別のファイルを登録
            </button>
          </div>
        </div>
      </>
    );
  }

  return <Loading />;
}

export default DeviceRegisterMultiPage;
