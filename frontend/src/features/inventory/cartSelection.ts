const DEVICE_ID_PARAM = 'device_ids';

interface CartSelectableDevice {
  lending_now: string | null;
  sale_id: string | null;
  defective: boolean;
  not_for_sale: boolean;
}

export function isRentalCartSelectable(device: CartSelectableDevice): boolean {
  return !device.lending_now && !device.sale_id && !device.defective;
}

export function isSaleCartSelectable(device: CartSelectableDevice): boolean {
  return isRentalCartSelectable(device) && !device.not_for_sale;
}

/** カート式手続き画面へ渡された端末IDを URL から取り出す。 */
export function readCartDeviceIds(params: URLSearchParams): string[] {
  const values = [
    ...params.getAll(DEVICE_ID_PARAM),
    ...params.getAll('devices'),
  ];

  return Array.from(
    new Set(
      values
        .flatMap((value) => value.split(','))
        .map((value) => value.trim())
        .filter(Boolean),
    ),
  );
}

/** 選択中の端末IDをカート式手続き画面の URL クエリに変換する。 */
export function createCartDeviceQuery(deviceIds: string[]): string {
  const params = new URLSearchParams();
  params.set(DEVICE_ID_PARAM, deviceIds.join(','));
  return params.toString();
}
