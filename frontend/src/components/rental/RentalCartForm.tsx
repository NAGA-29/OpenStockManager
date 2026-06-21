import { useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import Alert from '@/components/ui/Alert';
import DataTable, { type Column } from '@/components/ui/DataTable';
import { useStoreRental, useRentals, type RentalHist } from '@/features/rental/useRental';
import { useContacts, type Contact } from '@/features/contacts/useContacts';
import { useDeviceSearch } from '@/features/inventory/useDeviceSearch';
import type { Client } from '@/features/clients/useClients';
import type { CategoryDevice } from '@/features/inventory/useDeviceCategory';

interface RentalFormState {
  device_ids: string[];
  client_id: string;
  contact_id: number | '';
  checkout_at: string;
  schedule_return_at: string;
  note: string;
}

interface RentalCartFormProps {
  clients: Client[];
  rentals: RentalHist[];
}

function RentalCartForm({ clients }: RentalCartFormProps) {
  const navigate = useNavigate();
  const [form, setForm] = useState<RentalFormState>({
    device_ids: [],
    client_id: '',
    contact_id: '',
    checkout_at: new Date().toISOString().split('T')[0],
    schedule_return_at: new Date(Date.now() + 7 * 24 * 60 * 60 * 1000)
      .toISOString()
      .split('T')[0],
    note: '',
  });

  const [searchTerm, setSearchTerm] = useState('');
  const [debouncedTerm, setDebouncedTerm] = useState('');
  const [errors, setErrors] = useState<Record<string, string[]>>({});
  const [successMessage, setSuccessMessage] = useState('');

  // 入力をデバウンスして端末検索 API の呼び出し回数を抑える。
  useEffect(() => {
    const timer = setTimeout(() => setDebouncedTerm(searchTerm.trim()), 300);
    return () => clearTimeout(timer);
  }, [searchTerm]);

  const { data: searchData } = useDeviceSearch(debouncedTerm, '', 1);
  // 検索結果から既に選択済みの端末を除外して候補に出す。
  const searchResults = (searchData?.data ?? []).filter(
    (device) => !form.device_ids.includes(device.device_id),
  );

  const { data: allContacts } = useContacts('');
  const contactsData = form.client_id
    ? allContacts?.filter((c) => c.client_id === form.client_id) ?? []
    : [];
  const { mutateAsync: storeRental, isPending } = useStoreRental();
  const { refetch: refetchRentals } = useRentals();

  const handleAddDevice = (device: CategoryDevice) => {
    if (!form.device_ids.includes(device.device_id)) {
      setForm((prev) => ({
        ...prev,
        device_ids: [...prev.device_ids, device.device_id],
      }));
    }
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
      await storeRental({
        device_ids: form.device_ids,
        client_id: form.client_id,
        contact_id,
        checkout_at: form.checkout_at,
        schedule_return_at: form.schedule_return_at,
        note: form.note || undefined,
      });
      setSuccessMessage('レンタル登録が完了しました。');
      refetchRentals();
      setTimeout(() => navigate('/rental/history'), 2000);
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
    <div className="rental-form">
      {successMessage && (
        <Alert variant="success">{successMessage}</Alert>
      )}

      <form onSubmit={handleSubmit}>
        <div className="rental-form__section">
          <h3>端末検索・追加</h3>
          <div className="rental-form__field">
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
            <div className="rental-search-results">
              {searchResults.map((device) => (
                <div key={device.device_id} className="rental-search-result">
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

        <div className="rental-form__section">
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

        <div className="rental-form__section">
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
            {errors.checkout_at && (
              <div className="form-error">{errors.checkout_at.join(', ')}</div>
            )}
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
            {errors.schedule_return_at && (
              <div className="form-error">{errors.schedule_return_at.join(', ')}</div>
            )}
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

        <div className="rental-form__actions">
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
                checkout_at: new Date().toISOString().split('T')[0],
                schedule_return_at: new Date(Date.now() + 7 * 24 * 60 * 60 * 1000)
                  .toISOString()
                  .split('T')[0],
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

export default RentalCartForm;
