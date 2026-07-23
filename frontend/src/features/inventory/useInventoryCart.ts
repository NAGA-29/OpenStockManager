import { useContext } from 'react';
import { InventoryCartContext } from './inventoryCartContext';
import type { InventoryCartContextValue } from './inventoryCartTypes';

export function useInventoryCart(): InventoryCartContextValue {
  const value = useContext(InventoryCartContext);
  if (!value) {
    throw new Error('useInventoryCart must be used within InventoryCartProvider');
  }
  return value;
}
