<div class="summary-cards mb-3 mt-3">
    <div class="summary-card-title">{{ $title }}</div>
    <div class="summary-card-group">
        <div class="summary-card summary-card--success">
            <span class="summary-card__label">貸出可能</span>
            <span class="summary-card__value">{{ $count['all_count'] - ($count['defective_count'] + $count['lending_count']) }}</span>
        </div>
        <div class="summary-card summary-card--primary">
            <span class="summary-card__label">貸出中</span>
            <span class="summary-card__value">{{ $count['lending_count'] }}</span>
        </div>
        <div class="summary-card summary-card--danger">
            <span class="summary-card__label">不具合</span>
            <span class="summary-card__value">{{ $count['defective_count'] }}</span>
        </div>
    </div>
</div>
