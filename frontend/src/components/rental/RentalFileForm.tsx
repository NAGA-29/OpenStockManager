import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import Alert from '@/components/ui/Alert';
import DataTable, { type Column } from '@/components/ui/DataTable';
import {
  useUploadRentalMulti,
  useStoreRentalMulti,
  type RentalPreview,
} from '@/features/rental/useRental';
import { useContacts, type Contact } from '@/features/contacts/useContacts';
import type { Client } from '@/features/clients/useClients';

type FormState = 'upload' | 'confirm' | 'completed';

interface RentalFormData {
  client_id: string;
  contact_id: number | '';
  checkout_at: string;
  schedule_return_at: string;
  note: string;
}

interface RentalFileFormProps {
  clients: Client[];
}

function RentalFileForm({ clients }: RentalFileFormProps) {
  const navigate = useNavigate();
  const [state, setState] = useState<FormState>('upload');
  const [file, setFile] = useState<File | null>(null);
  const [previews, setPreviews] = useState<RentalPreview[]>([]);
  const [errors, setErrors] = useState<Record<string, string[]>>({});
  const [message, setMessage] = useState('');

  const [form, setForm] = useState<RentalFormData>({
    client_id: '',
    contact_id: '',
    checkout_at: new Date().toISOString().split('T')[0],
    schedule_return_at: new Date(Date.now() + 7 * 24 * 60 * 60 * 1000)
      .toISOString()
      .split('T')[0],
    note: '',
  });

  const { data: allContacts } = useContacts('');
  const contactsData = form.client_id
    ? allContacts?.filter((c) => c.client_id === form.client_id) ?? []
    : [];
  const { mutateAsync: uploadFile, isPending: isUploading } = useUploadRentalMulti();
  const { mutateAsync: storeRentals, isPending: isStoring } = useStoreRentalMulti();

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
      setMessage(`${data.count}件のレンタル対象を読み込みました。`);
    } catch (err) {
      if ((err as any).response?.status === 422) {
        setErrors((err as any).response.data.errors || {});
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
      const data = await storeRentals({
        client_id: form.client_id,
        contact_id,
        checkout_at: form.checkout_at,
        schedule_return_at: form.schedule_return_at,
        rentals: previews.map((p) => ({ device_id: p.device_id })),
        note: form.note || undefined,
      });
      setState('completed');
      setMessage(data.message);
      setTimeout(() => navigate('/rental/history'), 3000);
    } catch (err) {
      if ((err as any).response?.status === 422) {
        setErrors((err as any).response.data.errors || {});
      } else {
        setMessage((err as any).response?.data?.message || 'エラーが発生しました。');
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

  const columns: Column<RentalPreview>[] = [
    { key: 'device_id', header: '端末ID' },
    { key: 'device_type', header: '端末区分' },
    { key: 'device_name', header: '端末名' },
    { key: 'device_serial', header: 'シリアル' },
    { key: 'condition', header: 'コンディション' },
  ];

  return (
    <div className="rental-file-form">
      {message && (
        <Alert variant={state === 'completed' ? 'success' : 'info'}>
          {message}
        </Alert>
      )}

      {state === 'upload' && (
        <div className="rental-file-upload">
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

          {errors.rental_file && (
            <Alert variant="danger">{errors.rental_file.join(', ')}</Alert>
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
        <div className="rental-file-confirm">
          <div className="rental-file-preview">
            <h3>解析結果プレビュー</h3>
            <DataTable
              columns={columns}
              rows={previews}
              rowKey={(row) => row.device_id}
              empty="レンタル対象がありません。"
            />
          </div>

          <div className="rental-file-form-section">
            <h3>レンタル情報</h3>

            <div className="rental-form__field">
              <label>貸出先企業 *</label>
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

            <div className="rental-form__field">
              <label>貸出先担当者 *</label>
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

            <div className="rental-form__field">
              <label>貸出日 *</label>
              <input
                type="date"
                value={form.checkout_at}
                onChange={(e) =>
                  setForm((prev) => ({
                    ...prev,
                    checkout_at: e.target.value,
                  }))
                }
                className="form-control"
                required
              />
            </div>

            <div className="rental-form__field">
              <label>返却予定日 *</label>
              <input
                type="date"
                value={form.schedule_return_at}
                onChange={(e) =>
                  setForm((prev) => ({
                    ...prev,
                    schedule_return_at: e.target.value,
                  }))
                }
                className="form-control"
                required
              />
            </div>

            <div className="rental-form__field">
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

          <div className="rental-file-actions">
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
        <div className="rental-file-completed">
          <div className="completion-message">
            <i className="fas fa-check-circle" />
            <p>レンタル登録が完了しました。</p>
          </div>
        </div>
      )}
    </div>
  );
}

export default RentalFileForm;
