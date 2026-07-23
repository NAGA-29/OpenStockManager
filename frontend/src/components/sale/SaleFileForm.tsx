import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { AxiosError } from 'axios';
import Alert from '@/components/ui/Alert';
import DataTable, { type Column } from '@/components/ui/DataTable';
import {
  useUploadSaleMulti,
  useStoreSaleMulti,
  type SalePreview,
} from '@/features/sale/useSale';
import { useContacts, type Contact } from '@/features/contacts/useContacts';
import type { Client } from '@/features/clients/useClients';

interface ValidationErrorResponse {
  message?: string;
  errors?: Record<string, string[]>;
}

type FormState = 'upload' | 'confirm' | 'completed';

interface SaleFormData {
  client_id: string;
  contact_id: number | '';
  sale_date_at: string;
  note: string;
}

interface SaleFileFormProps {
  clients: Client[];
}

function SaleFileForm({ clients }: SaleFileFormProps) {
  const navigate = useNavigate();
  const [state, setState] = useState<FormState>('upload');
  const [file, setFile] = useState<File | null>(null);
  const [previews, setPreviews] = useState<SalePreview[]>([]);
  const [errors, setErrors] = useState<Record<string, string[]>>({});
  const [message, setMessage] = useState('');

  const [form, setForm] = useState<SaleFormData>({
    client_id: '',
    contact_id: '',
    sale_date_at: new Date().toISOString().split('T')[0],
    note: '',
  });

  const { data: allContacts } = useContacts('');
  const contactsData = form.client_id
    ? allContacts?.filter((c) => c.client_id === form.client_id) ?? []
    : [];
  const { mutateAsync: uploadFile, isPending: isUploading } = useUploadSaleMulti();
  const { mutateAsync: storeSales, isPending: isStoring } = useStoreSaleMulti();

  const handleFileChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    if (e.target.files?.[0]) {
      setFile(e.target.files[0]);
      setErrors({});
      setMessage('');
    }
  };

  const handleUpload = async () => {
    if (!file) {
      setErrors({ file: ['CSVファイルを選択してください。'] });
      return;
    }

    try {
      const data = await uploadFile(file);
      setPreviews(data.data);
      setState('confirm');
      setMessage(`${data.count}件の販売対象を読み込みました。`);
    } catch (err) {
      const axiosErr = err as AxiosError<ValidationErrorResponse>;
      if (axiosErr.response?.status === 422) {
        setErrors(axiosErr.response.data?.errors || {});
      }
    }
  };

  const handleStore = async () => {
    setErrors({});

    const contact_id = form.contact_id;
    if (!contact_id || typeof contact_id === 'string') {
      alert('担当者を選択してください。');
      return;
    }

    try {
      const data = await storeSales({
        client_id: form.client_id,
        contact_id,
        sale_date_at: form.sale_date_at,
        sales: previews.map((p) => ({ device_id: p.device_id })),
        note: form.note || undefined,
      });
      setState('completed');
      setMessage(data.message);
      setTimeout(() => navigate('/sale/history'), 3000);
    } catch (err) {
      const axiosErr = err as AxiosError<ValidationErrorResponse>;
      if (axiosErr.response?.status === 422) {
        setErrors(axiosErr.response.data?.errors || {});
      } else {
        setMessage(axiosErr.response?.data?.message || 'エラーが発生しました。');
      }
    }
  };

  const handleBack = () => {
    setState('upload');
    setPreviews([]);
    setFile(null);
    setErrors({});
    setMessage('');
  };

  const columns: Column<SalePreview>[] = [
    { key: 'device_id', header: '端末ID' },
    { key: 'device_type', header: '端末区分' },
    { key: 'device_name', header: '端末名' },
    { key: 'device_serial', header: 'シリアル' },
    { key: 'condition', header: 'コンディション' },
  ];

  return (
    <div className="sale-file-form">
      {message && (
        <Alert variant={state === 'completed' ? 'success' : 'info'}>
          {message}
        </Alert>
      )}

      {state === 'upload' && (
        <div className="sale-file-upload">
          <div className="file-input-wrapper">
            <label htmlFor="csv-file" className="file-label">
              <i className="fas fa-cloud-upload-alt" />
              <span>CSVファイルを選択</span>
              <span className="file-hint">またはドラッグ＆ドロップ</span>
            </label>
            <input
              id="csv-file"
              type="file"
              accept=".csv,.txt"
              onChange={handleFileChange}
              className="file-input"
            />
          </div>

          {file && (
            <div className="file-info">
              <p>
                <i className="fas fa-check-circle text-success" />
                {file.name}
              </p>
            </div>
          )}

          {errors.sale_file && (
            <Alert variant="danger">{errors.sale_file.join(', ')}</Alert>
          )}

          <button
            type="button"
            onClick={handleUpload}
            disabled={!file || isUploading}
            className="osm-btn osm-btn--primary"
          >
            {isUploading ? 'ファイルを解析中...' : 'ファイルを解析'}
          </button>
        </div>
      )}

      {state === 'confirm' && (
        <div className="sale-file-confirm">
          <div className="sale-file-preview">
            <h3>解析結果プレビュー</h3>
            <DataTable
              columns={columns}
              rows={previews}
              rowKey={(row) => row.device_id}
              empty="販売対象がありません。"
            />
          </div>

          <div className="sale-file-form-section">
            <h3>販売情報</h3>

            <div className="sale-form__field">
              <label>販売先企業 *</label>
              <select
                value={form.client_id}
                onChange={(e) =>
                  setForm((prev) => ({
                    ...prev,
                    client_id: e.target.value,
                    contact_id: '',
                  }))
                }
                className="form-control"
                required
              >
                <option value="">選択してください</option>
                {clients.map((client) => (
                  <option key={client.client_id} value={client.client_id}>
                    {client.company}
                  </option>
                ))}
              </select>
              {errors.client_id && (
                <div className="form-error">{errors.client_id.join(', ')}</div>
              )}
            </div>

            <div className="sale-form__field">
              <label>販売先担当者 *</label>
              <select
                value={form.contact_id}
                onChange={(e) =>
                  setForm((prev) => ({
                    ...prev,
                    contact_id: e.target.value ? Number(e.target.value) : '',
                  }))
                }
                className="form-control"
                required
                disabled={!form.client_id}
              >
                <option value="">
                  {form.client_id ? '選択してください' : '企業を先に選択'}
                </option>
                {contactsData?.map((contact: Contact) => (
                  <option key={contact.id} value={contact.id}>
                    {contact.name}
                  </option>
                ))}
              </select>
              {errors.contact_id && (
                <div className="form-error">{errors.contact_id.join(', ')}</div>
              )}
            </div>

            <div className="sale-form__field">
              <label>販売日 *</label>
              <input
                type="date"
                value={form.sale_date_at}
                onChange={(e) =>
                  setForm((prev) => ({
                    ...prev,
                    sale_date_at: e.target.value,
                  }))
                }
                className="form-control"
                required
              />
            </div>

            <div className="sale-form__field">
              <label>ノート</label>
              <textarea
                value={form.note}
                onChange={(e) =>
                  setForm((prev) => ({
                    ...prev,
                    note: e.target.value,
                  }))
                }
                className="form-control"
                rows={4}
              />
            </div>
          </div>

          <div className="sale-file-actions">
            <button
              type="button"
              onClick={handleStore}
              disabled={isStoring || !form.client_id || !form.contact_id}
              className="osm-btn osm-btn--primary"
            >
              {isStoring ? '登録中...' : '登録'}
            </button>
            <button
              type="button"
              onClick={handleBack}
              className="osm-btn"
              disabled={isStoring}
            >
              戻る
            </button>
          </div>
        </div>
      )}

      {state === 'completed' && (
        <div className="sale-file-completed">
          <div className="completion-message">
            <i className="fas fa-check-circle" />
            <p>販売登録が完了しました。</p>
          </div>
        </div>
      )}
    </div>
  );
}

export default SaleFileForm;
