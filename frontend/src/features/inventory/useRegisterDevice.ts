import { useMutation } from '@tanstack/react-query';
import api from '@/lib/api';

/** 端末登録のリクエストペイロード。 */
export interface RegisterDevicePayload {
  device_type: string;
  device_name: string;
  device_serial: string;
  custom_fields?: Record<string, string | boolean>;
  first_work_date_at?: string | null;
  purchase_date_at?: string | null;
  client?: string | null;
  condition: number;
  defective?: boolean;
  not_for_sale?: boolean;
  note?: string | null;
}

interface RegisterDeviceResult {
  device_id: string;
}

/** 端末を単体登録する mutation（`POST /api/devices`）。 */
export function useRegisterDevice() {
  return useMutation({
    mutationFn: async (payload: RegisterDevicePayload): Promise<RegisterDeviceResult> => {
      const res = await api.post<{ data: RegisterDeviceResult }>('/devices', payload);
      return res.data.data;
    },
  });
}
