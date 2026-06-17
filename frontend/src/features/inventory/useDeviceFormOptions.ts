import { useQuery } from '@tanstack/react-query';
import api from '@/lib/api';

/** カスタムフィールドの選択肢（select 型）。 */
export interface CustomFieldOption {
  value: string;
  label: string;
}

/** カテゴリに紐づくカスタムフィールド定義。 */
export interface CustomFieldDef {
  field_key: string;
  label: string;
  field_type: 'text' | 'number' | 'boolean' | 'select' | string;
  is_required: boolean;
  options: CustomFieldOption[] | null;
}

/** 登録フォーム用のカテゴリ（カスタムフィールド定義込み）。 */
export interface FormCategory {
  code: string;
  name: string;
  fields: CustomFieldDef[];
}

/** コンディション選択肢。 */
export interface ConditionOption {
  id: number;
  label: string;
}

interface DeviceFormOptions {
  categories: FormCategory[];
  conditions: ConditionOption[];
}

/** 端末登録フォームの選択肢（カテゴリ＋カスタムフィールド＋コンディション）を取得する。 */
export function useDeviceFormOptions() {
  return useQuery({
    queryKey: ['devices', 'form-options'],
    queryFn: async (): Promise<DeviceFormOptions> => {
      const res = await api.get<DeviceFormOptions>('/devices/form-options');
      return res.data;
    },
    staleTime: 5 * 60 * 1000,
  });
}
