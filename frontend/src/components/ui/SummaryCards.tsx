import { type ReactNode } from 'react';
import './ui.css';

export type SummaryCardVariant = 'primary' | 'danger' | 'success' | 'warning';

export interface SummaryCardItem {
  label: ReactNode;
  value: ReactNode;
  /** 配色。既定は primary。 */
  variant?: SummaryCardVariant;
}

interface SummaryCardsProps {
  cards: SummaryCardItem[];
}

/**
 * サマリーカード群（ラベル＋数値）。旧 `component/summary_cards` 相当。
 * ダッシュボード等の集計表示で共通利用する。
 */
function SummaryCards({ cards }: SummaryCardsProps) {
  return (
    <div className="summary-cards">
      {cards.map((card, i) => (
        <div
          key={i}
          className={`summary-card summary-card--${card.variant ?? 'primary'}`}
        >
          <span className="summary-card__label">{card.label}</span>
          <span className="summary-card__value">{card.value}</span>
        </div>
      ))}
    </div>
  );
}

export default SummaryCards;
