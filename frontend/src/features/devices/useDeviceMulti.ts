import { useMutation } from '@tanstack/react-query';
import api from '@/lib/api';

export interface DevicePreview {
  device_id: string;
  device_type: string;
  device_name: string;
  device_serial: string;
  first_work_date_at?: string;
  purchase_date_at?: string;
  option?: string;
  condition?: number;
  defective?: number;
  not_for_sale?: number;
  note?: string;
}

export function useUploadDeviceMulti() {
  return useMutation({
    mutationFn: async (file: File): Promise<DevicePreview[]> => {
      const formData = new FormData();
      formData.append('device_register_file', file);

      const response = await api.post<{ data: DevicePreview[] }>(
        '/devices/multi/upload',
        formData,
        {
          headers: {
            'Content-Type': 'multipart/form-data',
          },
        }
      );
      return response.data.data;
    },
  });
}

export function useStoreDeviceMulti() {
  return useMutation({
    mutationFn: async (devices: DevicePreview[]) => {
      const response = await api.post<{
        data: { count: number; message: string };
      }>('/devices/multi/store', {
        devices,
      });
      return response.data.data;
    },
  });
}
