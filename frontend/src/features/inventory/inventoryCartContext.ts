import { createContext } from 'react';
import type { InventoryCartContextValue } from './inventoryCartTypes';

/** 在庫一覧でチェックした端末をヘッダーのカートモーダルと共有する。 */
export const InventoryCartContext =
  createContext<InventoryCartContextValue | null>(null);
