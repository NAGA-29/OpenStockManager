import type { ReactNode } from 'react';
import './ui.css';

/** テーブルの列定義。`render` 省略時は `row[key]` を表示。 */
export interface Column<T> {
  key: string;
  header: ReactNode;
  render?: (row: T) => ReactNode;
  className?: string;
}

interface DataTableProps<T> {
  columns: Column<T>[];
  rows: T[];
  rowKey: (row: T) => string | number;
  /** 行が空のときの表示。 */
  empty?: ReactNode;
}

/** 汎用テーブル（旧 table.css の見た目を踏襲）。 */
function DataTable<T>({ columns, rows, rowKey, empty }: DataTableProps<T>) {
  if (rows.length === 0) {
    return <p className="ui-table__empty">{empty ?? 'データがありません。'}</p>;
  }

  return (
    <div className="ui-table__wrap">
      <table className="ui-table">
        <thead>
          <tr>
            {columns.map((col) => (
              <th key={col.key} className={col.className}>
                {col.header}
              </th>
            ))}
          </tr>
        </thead>
        <tbody>
          {rows.map((row) => (
            <tr key={rowKey(row)}>
              {columns.map((col) => (
                <td key={col.key} className={col.className}>
                  {col.render
                    ? col.render(row)
                    : ((row as Record<string, ReactNode>)[col.key] ?? null)}
                </td>
              ))}
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
}

export default DataTable;
