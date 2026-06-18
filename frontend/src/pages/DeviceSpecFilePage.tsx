import { useRef, useState } from 'react';
import Alert from '@/components/ui/Alert';
import Loading from '@/components/ui/Loading';
import { useToast } from '@/components/ui/toast/useToast';
import {
  useDeviceSpecFile,
  useUploadDeviceSpecFile,
} from '@/features/devices/useDeviceSpecFile';
import { AxiosError } from 'axios';
import './device-files.css';

interface ValidationErrorResponse {
  message?: string;
  errors?: Record<string, string[]>;
}

function DeviceSpecFilePage() {
  const { data: fileInfo, isLoading } = useDeviceSpecFile();
  const uploadMutation = useUploadDeviceSpecFile();
  const { show } = useToast();
  const fileInputRef = useRef<HTMLInputElement>(null);
  const [error, setError] = useState<string | null>(null);

  const handleFileSelect = async (event: React.ChangeEvent<HTMLInputElement>) => {
    const file = event.target.files?.[0];
    if (!file) return;

    setError(null);

    try {
      await uploadMutation.mutateAsync(file);
      show('スペックファイルをアップロードしました。', 'success');
      if (fileInputRef.current) {
        fileInputRef.current.value = '';
      }
    } catch (err) {
      const axiosErr = err as AxiosError<ValidationErrorResponse>;
      const responseData = axiosErr.response?.data;

      if (axiosErr.response?.status === 422) {
        const fieldErrors = responseData?.errors?.spec_file;
        if (fieldErrors) {
          setError(fieldErrors[0]);
        } else {
          setError(responseData?.message ?? 'ファイルのアップロードに失敗しました。');
        }
      } else {
        setError(responseData?.message ?? 'サーバーエラーが発生しました。');
      }
      show('スペックファイルのアップロードに失敗しました。', 'danger');
    }
  };

  if (isLoading) {
    return <Loading />;
  }

  return (
    <>
      <div className="page-bar">
        <i className="fas fa-file-excel" aria-hidden="true" />
        端末スペック
      </div>

      <div className="file-upload-container">
        <div className="file-upload-header">
          {fileInfo && (
            <div className="file-info">
              <p className="file-info__label">現在のファイル:</p>
              <p className="file-info__name">{fileInfo.filename}</p>
              <p className="file-info__meta">
                サイズ: {(fileInfo.size / 1024).toFixed(2)} KB | 更新: {fileInfo.updated_at}
              </p>
            </div>
          )}
          {!fileInfo && (
            <p className="file-info__empty">ファイルがアップロードされていません</p>
          )}
        </div>

        <div className="file-upload-card">
          {error && <Alert variant="danger">{error}</Alert>}

          <p className="file-upload__note">
            スペックを記載したエクセルファイルをアップロードしてください。
          </p>
          <p className="file-upload__warning">
            ※以前のファイルを上書きしてしまいます。戻すことはできません。注意してください。
          </p>

          <div className="file-upload__input-group">
            <label htmlFor="spec_file" className="file-upload__label">
              ファイルを選択
            </label>
            <input
              ref={fileInputRef}
              id="spec_file"
              type="file"
              accept=".xlsx,.xls,.csv,.pdf"
              onChange={handleFileSelect}
              disabled={uploadMutation.isPending}
              className="file-upload__input"
            />
            {uploadMutation.isPending && (
              <p className="file-upload__status">アップロード中...</p>
            )}
          </div>
        </div>
      </div>
    </>
  );
}

export default DeviceSpecFilePage;
