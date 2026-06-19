import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import api from '@/lib/api';

export interface FileInfo {
  filename: string;
  size: number;
  updated_at: string;
}

export function useDeviceSpecFile() {
  return useQuery({
    queryKey: ['device-spec-file'],
    queryFn: async (): Promise<FileInfo | null> => {
      const res = await api.get<{ data: FileInfo | null }>('/devices/file/spec');
      return res.data.data;
    },
  });
}

export function useUploadDeviceSpecFile() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: async (file: File) => {
      const formData = new FormData();
      formData.append('spec_file', file);

      const response = await api.post<{ data: FileInfo }>(
        '/devices/file/spec',
        formData,
        {
          headers: {
            'Content-Type': 'multipart/form-data',
          },
        }
      );
      return response.data.data;
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['device-spec-file'] });
    },
  });
}
