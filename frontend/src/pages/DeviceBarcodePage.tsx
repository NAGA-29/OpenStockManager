import { useEffect, useRef } from 'react';
import { NavLink, useParams } from 'react-router-dom';
import JsBarcode from 'jsbarcode';
import Alert from '@/components/ui/Alert';
import Loading from '@/components/ui/Loading';
import { useDevice } from '@/features/inventory/useDevice';
import './barcode.css';

/**
 * バーコード印刷画面（旧 `devices/barcode_print.blade.php` を移植）。
 * 端末詳細 API（`GET /api/devices/:id`）から端末情報を取得し、
 * jsbarcode で device_id を CODE128 バーコードとして SVG 描画する。
 * 印刷ボタンで `window.print()`、印刷時は操作ボタンを非表示にする。
 */
function DeviceBarcodePage() {
  const { id = '' } = useParams<{ id: string }>();
  const { data, isLoading, isError, refetch } = useDevice(id);
  const svgRef = useRef<SVGSVGElement>(null);

  useEffect(() => {
    if (data && svgRef.current) {
      JsBarcode(svgRef.current, data.device_id, {
        format: 'CODE128',
        width: 2,
        height: 60,
        displayValue: true,
        fontSize: 14,
        margin: 10,
      });
    }
  }, [data]);

  if (isLoading) {
    return <Loading />;
  }

  if (isError || !data) {
    return (
      <Alert variant="danger">
        端末情報の取得に失敗しました。{' '}
        <button type="button" onClick={() => void refetch()}>
          再読み込み
        </button>
      </Alert>
    );
  }

  return (
    <div className="barcode-page">
      <div className="barcode-actions">
        <button
          type="button"
          className="osm-btn"
          onClick={() => window.print()}
        >
          <i className="fas fa-print" aria-hidden="true" /> 印刷
        </button>
        <NavLink
          to={`/devices/${encodeURIComponent(data.device_id)}`}
          className="osm-btn osm-btn--secondary"
        >
          端末詳細に戻る
        </NavLink>
      </div>

      <div className="barcode-label">
        <div className="barcode-device-info">
          <div className="barcode-device-name">{data.device_name}</div>
          <div className="barcode-device-detail">
            {data.device_type} | S/N: {data.device_serial}
          </div>
        </div>
        <svg ref={svgRef} />
      </div>
    </div>
  );
}

export default DeviceBarcodePage;
