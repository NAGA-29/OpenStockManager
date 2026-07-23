import type { CategoryDevice } from './useDeviceCategory';

export interface InventoryCartContextValue {
  selectedDevices: CategoryDevice[];
  selectedDeviceIds: string[];
  addDevice: (device: CategoryDevice) => void;
  removeDevice: (deviceId: string) => void;
  removeDevices: (deviceIds: string[]) => void;
  toggleDevice: (device: CategoryDevice) => void;
  setDevices: (devices: CategoryDevice[]) => void;
  clearCart: () => void;
}
