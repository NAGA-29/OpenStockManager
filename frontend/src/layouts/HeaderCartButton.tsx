import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import DataTable, { type Column } from '@/components/ui/DataTable';
import Modal from '@/components/ui/Modal';
import {
  createCartDeviceQuery,
  isRentalCartSelectable,
  isSaleCartSelectable,
} from '@/features/inventory/cartSelection';
import { useInventoryCart } from '@/features/inventory/useInventoryCart';
import type { CategoryDevice } from '@/features/inventory/useDeviceCategory';

function HeaderCartButton() {
  const navigate = useNavigate();
  const [open, setOpen] = useState(false);
  const { selectedDevices, removeDevice, clearCart } = useInventoryCart();
  const selectedDeviceIds = selectedDevices.map((device) => device.device_id);
  const saleDeviceIds = selectedDevices
    .filter(isSaleCartSelectable)
    .map((device) => device.device_id);

  const goToCart = (path: '/rental' | '/sale', deviceIds: string[]) => {
    setOpen(false);
    navigate(`${path}?${createCartDeviceQuery(deviceIds)}`);
  };

  const columns: Column<CategoryDevice>[] = [
    { key: 'device_id', header: '端末ID' },
    { key: 'device_name', header: '端末名', render: (row) => row.device_name ?? '-' },
    {
      key: 'status',
      header: '状態',
      render: (row) => (
        <span className="header-cart-status">
          {row.lending_now ? <span className="badge badge--danger">貸出中</span> : null}
          {row.sale_id ? <span className="badge badge--danger">販売済</span> : null}
          {row.defective ? <span className="badge badge--danger">不具合</span> : null}
          {row.not_for_sale ? <span className="badge badge--danger">販売不可</span> : null}
          {isRentalCartSelectable(row) ? (
            <span className="badge badge--success">手続き可</span>
          ) : null}
        </span>
      ),
    },
    {
      key: 'action',
      header: '',
      render: (row) => (
        <button
          type="button"
          className="osm-btn osm-btn--small osm-btn--danger"
          onClick={() => removeDevice(row.device_id)}
        >
          削除
        </button>
      ),
    },
  ];

  return (
    <>
      <button
        type="button"
        className="osm-navbar__cart"
        aria-label={`カートを開く。選択中 ${selectedDevices.length}件`}
        onClick={() => setOpen(true)}
      >
        <i className="fas fa-shopping-cart" aria-hidden="true" />
        <span className="osm-navbar__cart-count">{selectedDevices.length}</span>
      </button>

      <Modal
        open={open}
        title={`選択中の商品 (${selectedDevices.length}件)`}
        onClose={() => setOpen(false)}
        footer={
          <>
            <button
              type="button"
              className="osm-btn"
              disabled={selectedDevices.length === 0}
              onClick={clearCart}
            >
              選択解除
            </button>
            <button
              type="button"
              className="osm-btn osm-btn--primary"
              disabled={selectedDeviceIds.length === 0}
              onClick={() => goToCart('/rental', selectedDeviceIds)}
            >
              レンタル手続きへ
            </button>
            <button
              type="button"
              className="osm-btn"
              disabled={saleDeviceIds.length === 0}
              onClick={() => goToCart('/sale', saleDeviceIds)}
            >
              販売手続きへ
            </button>
          </>
        }
      >
        <DataTable
          columns={columns}
          rows={selectedDevices}
          rowKey={(row) => row.device_id}
          empty="商品が選択されていません。"
        />
      </Modal>
    </>
  );
}

export default HeaderCartButton;
