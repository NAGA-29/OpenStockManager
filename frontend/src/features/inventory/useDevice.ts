import { useQuery } from '@tanstack/react-query';
import api from '@/lib/api';

/** 解決済みカスタムフィールド（ラベル・型・select 表示名）。 */
export interface DeviceCustomField {
  key: string;
  label: string;
  type: string;
  value: string | number | boolean | null;
  /** 表示用の値（select はラベル解決済み）。 */
  display: string | number | boolean | null;
}

/** 端末に紐づく画像。 */
export interface DeviceImage {
  path: string;
  filename: string | null;
}

/** 貸出履歴行。 */
export interface DeviceRentalHist {
  lend_id: number | string;
  company: string | null;
  checkout_at: string | null;
}

/** 販売履歴行。 */
export interface DeviceSaleHist {
  sale_id: number | string;
  company: string | null;
  sale_date_at: string | null;
}

/** 端末詳細（`GET /api/devices/:id` の `data`）。 */
export interface DeviceDetail {
  device_id: string;
  device_type: string;
  device_name: string | null;
  device_serial: string | null;
  lending_now: string | null;
  sale_id: string | null;
  defective: boolean;
  not_for_sale: boolean;
  note: string | null;
  condition: string | null;
  option: string | null;
  using_user_id: string | null;
  first_work_date_at: string | null;
  purchase_date_at: string | null;
  modified_at: string | null;
  custom_fields: DeviceCustomField[];
  images: DeviceImage[];
  rental_hists: DeviceRentalHist[];
  sale_hists: DeviceSaleHist[];
}

/** 端末個別詳細を取得するクエリ。 */
export function useDevice(deviceId: string) {
  return useQuery({
    queryKey: ['devices', 'detail', deviceId],
    queryFn: async (): Promise<DeviceDetail> => {
      const res = await api.get<{ data: DeviceDetail }>(
        `/devices/${encodeURIComponent(deviceId)}`,
      );
      return res.data.data;
    },
  });
}
