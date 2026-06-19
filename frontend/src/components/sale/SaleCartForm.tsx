import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import Alert from '@/components/ui/Alert';
import DataTable, { type Column } from '@/components/ui/DataTable';
import { useStoreSale, useSales, type SaleHist } from '@/features/sale/useSale';
import { useContacts, type Contact } from '@/features/contacts/useContacts';
import type { Client } from '@/features/clients/useClients';
import type { CategoryDevice } from '@/features/inventory/useDeviceCategory';

interface SaleFormState {
  device_ids: string[];
  client_id: string;
  contact_id: number | '';
  sale_date_at: string;
  note: string;
}

interface SaleCartFormProps {
  clients: Client[];
  sales: SaleHist[];
}

function SaleCartForm({ clients }: SaleCartFormProps) {
  const navigate = useNavigate();
  const [form, setForm] = useState<SaleFormState>({
    device_ids: [],
    client_id: '',
    contact_id: '',
    sale_date_at: new Date().toISOString().split('T')[0],
    note: '',
  });

  const [searchTerm, setSearchTerm] = useState('');
  const [searchResults, setSearchResults] = useState<CategoryDevice[]>([]);
  const [errors, setErrors] = useState<Record<string, string[]>>({});
  const [successMessage, setSuccessMessage] = useState('');

  const { data: allContacts } = useContacts('');
  const contactsData = form.client_id
    ? allContacts?.filter((c) => c.client_id === form.client_id) ?? []
    : [];
  const { mutateAsync: storeSale, isPending } = useStoreSale();
  const { refetch: refetchSales } = useSales();

  const handleAddDevice = (device: CategoryDevice) => {
    if (!form.device_ids.includes(device.device_id)) {
      setForm((prev) => ({
        ...prev,
        device_ids: [...prev.device_ids, device.device_id],
      }));
    }
    setSearchResults([]);
    setSearchTerm('');
  };

  const handleRemoveDevice = (deviceId: string) => {
    setForm((prev) => ({
      ...prev,
      device_ids: prev.device_ids.filter((id) => id !== deviceId),
    }));
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setErrors({});

    const contact_id = form.contact_id;
    if (!contact_id || typeof contact_id === 'string') {
      alert('担当者を選択してください。');
      return;
    }

    try {
      await storeSale({
        device_ids: form.device_ids,
        client_id: form.client_id,
        contact_id,
        sale_date_at: form.sale_date_at,
        note: form.note || undefined,
      });
      setSuccessMessage('販売登録が完了しました。');
      refetchSales();
      setTimeout(() => navigate('/sale/history'), 2000);
    } catch (err) {
      if ((err as any).response?.status === 422) {
        setErrors((err as any).response.data.errors || {});
      }
    }
  };

  const columns: Column<CategoryDevice>[] = [
    { key: 'device_id', header: '端末ID' },
    { key: 'device_type', header: '端末区分' },
    { key: 'device_name', header: '端末名' },
    { key: 'device_serial', header: 'シリアル' },
    {
      key: 'action',
      header: '',
      render: (row) => (
        <button
          type="button"
          className="osm-btn osm-btn--small osm-btn--danger"
          onClick={() => handleRemoveDevice(row.device_id)}
        >
          削除
        </button>
      ),
    },
  ];

  const selectedDevices: CategoryDevice[] = form.device_ids.map((id) => ({
    device_id: id,
    device_type: '',
    device_name: null,
    device_serial: null,
    condition: null,
    lending_now: null,
    sale_id: null,
    defective: false,
    not_for_sale: false,
    note: null,
    has_images: false,
  }));

  return (
    <div className="sale-form">
      {successMessage && (
        <Alert variant="success">{successMessage}</Alert>
      )}

      <form onSubmit={handleSubmit}>
        <div className="sale-form__section">
          <h3>端末検索・追加</h3>
          <div className="sale-form__field">
            <label>端末を検索</label>
            <input
              type="text"
              placeholder="端末IDやシリアルで検索"
              value={searchTerm}
              onChange={(e) => setSearchTerm(e.target.value)}
              className="form-control"
            />
          </div>

          {searchResults.length > 0 && (
            <div className="sale-search-results">
              {searchResults.map((device) => (
                <div key={device.device_id} className="sale-search-result">
                  <span>
                    {device.device_id} - {device.device_name}
                  </span>
                  <button
                    type="button"
                    onClick={() => handleAddDevice(device)}
                    className="osm-btn osm-btn--small"
                  >
                    追加
                  </button>
                </div>
              ))}
            </div>
          )}
        </div>

        <div className="sale-form__section">
          <h3>選択された端末</h3>
          {errors.device_ids && (
            <Alert variant="danger">{errors.device_ids.join(', ')}</Alert>
          )}
          <DataTable
            columns={columns}
            rows={selectedDevices}
            rowKey={(row) => row.device_id}
            empty="端末が選択されていません。"
          />
        </div>

        <div className="sale-form__section">
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
            {errors.sale_date_at && (
              <div className="form-error">{errors.sale_date_at.join(', ')}</div>
            )}
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

        <div className="sale-form__actions">
          <button
            type="submit"
            className="osm-btn osm-btn--primary"
            disabled={isPending || form.device_ids.length === 0}
          >
            {isPending ? '登録中...' : '登録'}
          </button>
          <button
            type="button"
            className="osm-btn"
            onClick={() =>
              setForm({
                device_ids: [],
                client_id: '',
                contact_id: '',
                sale_date_at: new Date().toISOString().split('T')[0],
                note: '',
              })
            }
          >
            リセット
          </button>
        </div>
      </form>
    </div>
  );
}

export default SaleCartForm;
