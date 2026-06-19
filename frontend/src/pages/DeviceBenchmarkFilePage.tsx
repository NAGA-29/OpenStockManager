import { useRef, useState } from 'react';
import Alert from '@/components/ui/Alert';
import Loading from '@/components/ui/Loading';
import { useToast } from '@/components/ui/toast/useToast';
import {
  useDeviceBenchmarkFile,
  useUploadDeviceBenchmarkFile,
} from '@/features/devices/useDeviceBenchmarkFile';
import { AxiosError } from 'axios';
import './device-files.css';

interface ValidationErrorResponse {
  message?: string;
  errors?: Record<string, string[]>;
}

function DeviceBenchmarkFilePage() {
  const { data: fileInfo, isLoading } = useDeviceBenchmarkFile();
  const uploadMutation = useUploadDeviceBenchmarkFile();
  const { show } = useToast();
  const fileInputRef = useRef<HTMLInputElement>(null);
  const [error, setError] = useState<string | null>(null);

  const handleFileSelect = async (event: React.ChangeEvent<HTMLInputElement>) => {
    const file = event.target.files?.[0];
    if (!file) return;

    setError(null);

    try {
      await uploadMutation.mutateAsync(file);
      show('ベンチマークファイルをアップロードしました。', 'success');
      if (fileInputRef.current) {
        fileInputRef.current.value = '';
      }
    } catch (err) {
      const axiosErr = err as AxiosError<ValidationErrorResponse>;
      const responseData = axiosErr.response?.data;

      if (axiosErr.response?.status === 422) {
        const fieldErrors = responseData?.errors?.benchmark_file;
        if (fieldErrors) {
          setError(fieldErrors[0]);
        } else {
          setError(responseData?.message ?? 'ファイルのアップロードに失敗しました。');
        }
      } else {
        setError(responseData?.message ?? 'サーバーエラーが発生しました。');
      }
      show('ベンチマークファイルのアップロードに失敗しました。', 'danger');
    }
  };

  if (isLoading) {
    return <Loading />;
  }

  return (
    <>
      <div className="page-bar">
        <i className="fas fa-chart-bar" aria-hidden="true" />
        ベンチマーク
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
            ベンチマーク結果Excelファイルをアップロードしてください。
          </p>
          <p className="file-upload__warning">
            ※以前のファイルを上書きしてしまいます。戻すことはできません。
          </p>

          <div className="file-upload__input-group">
            <label htmlFor="benchmark_file" className="file-upload__label">
              ファイルを選択
            </label>
            <input
              ref={fileInputRef}
              id="benchmark_file"
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

export default DeviceBenchmarkFilePage;
