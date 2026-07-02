import { useCallback, useMemo, useState, type ReactNode } from 'react';
import { InventoryCartContext } from './inventoryCartContext';
import type { InventoryCartContextValue } from './inventoryCartTypes';
import type { CategoryDevice } from './useDeviceCategory';

const STORAGE_KEY = 'osm.inventoryCart.devices';

function readStoredDevices(): CategoryDevice[] {
  try {
    const raw = window.localStorage.getItem(STORAGE_KEY);
    if (!raw) {
      return [];
    }
    const devices = JSON.parse(raw);
    return Array.isArray(devices) ? devices : [];
  } catch {
    return [];
  }
}

function writeStoredDevices(devices: CategoryDevice[]) {
  window.localStorage.setItem(STORAGE_KEY, JSON.stringify(devices));
}

export function InventoryCartProvider({ children }: { children: ReactNode }) {
  const [selectedDevices, setSelectedDevices] = useState<CategoryDevice[]>(() =>
    readStoredDevices(),
  );

  const addDevice = useCallback((device: CategoryDevice) => {
    setSelectedDevices((prev) => {
      const next = prev.some((item) => item.device_id === device.device_id)
        ? prev
        : [...prev, device];
      writeStoredDevices(next);
      return next;
    });
  }, []);

  const removeDevice = useCallback((deviceId: string) => {
    setSelectedDevices((prev) => {
      const next = prev.filter((device) => device.device_id !== deviceId);
      writeStoredDevices(next);
      return next;
    });
  }, []);

  const removeDevices = useCallback((deviceIds: string[]) => {
    setSelectedDevices((prev) => {
      const deviceIdSet = new Set(deviceIds);
      const next = prev.filter((device) => !deviceIdSet.has(device.device_id));
      writeStoredDevices(next);
      return next;
    });
  }, []);

  const toggleDevice = useCallback((device: CategoryDevice) => {
    setSelectedDevices((prev) => {
      const next = prev.some((item) => item.device_id === device.device_id)
        ? prev.filter((item) => item.device_id !== device.device_id)
        : [...prev, device];
      writeStoredDevices(next);
      return next;
    });
  }, []);

  const setDevices = useCallback((devices: CategoryDevice[]) => {
    setSelectedDevices((prev) => {
      const next = new Map(prev.map((device) => [device.device_id, device]));
      for (const device of devices) {
        next.set(device.device_id, device);
      }
      const nextDevices = Array.from(next.values());
      writeStoredDevices(nextDevices);
      return nextDevices;
    });
  }, []);

  const clearCart = useCallback(() => {
    setSelectedDevices([]);
    window.localStorage.removeItem(STORAGE_KEY);
  }, []);

  const value = useMemo<InventoryCartContextValue>(() => {
    return {
      selectedDevices,
      selectedDeviceIds: selectedDevices.map((device) => device.device_id),
      addDevice,
      removeDevice,
      removeDevices,
      toggleDevice,
      setDevices,
      clearCart,
    };
  }, [addDevice, clearCart, removeDevice, removeDevices, selectedDevices, setDevices, toggleDevice]);

  return (
    <InventoryCartContext.Provider value={value}>
      {children}
    </InventoryCartContext.Provider>
  );
}
