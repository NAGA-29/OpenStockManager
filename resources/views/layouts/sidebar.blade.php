<div class="sidebar-container bg-dark" id="side_navbar" style='min-height:100vh'>

    <div class="sidebar-logo"></div>
    <ul class="sidebar-navigation flex-column">

        <li class="header" style="cursor: pointer;">
            <a href="{{ route('dashboard') }}" class="text-decoration-none" style="padding: 0;">
                <i class="fa fa-home"></i><span class="sidebar-label">ダッシュボード</span>
            </a>
        </li>

        <li class="header" data-bs-target="#inventory" data-bs-toggle="collapse" style="cursor: pointer;">
            <i class="fa fa-tablet-alt"></i><span class="sidebar-label">在庫一覧</span>
            <i class="fas fa-sort-down sidebar-sort-icon"></i>
        </li>
        <div id='inventory' class="collapse list-unstyled pl-3">
            <li>
                <a href="{{ route('inventory.units.category', ['code' => 'STB']) }}" class="text-decoration-none">
                    <span class="sidebar-label">個別管理</span>
                </a>
            </li>
            <li>
                <a href="{{ route('inventory.stocks.index') }}" class="text-decoration-none">
                    <span class="sidebar-label">数量管理</span>
                </a>
            </li>
        </div>

        <li class="header" data-bs-target="#procedure" data-bs-toggle="collapse" style="cursor: pointer;">
            <i class="fa fa-file-signature"></i><span class="sidebar-label">手続き</span>
            <i class="fas fa-sort-down sidebar-sort-icon"></i>
        </li>
        <div id='procedure' class="collapse list-unstyled pl-3">
            <li>
                <a href="{{ route('device.rental') }}" class="text-decoration-none">
                    <span class="sidebar-label">レンタル</span>
                </a>
            </li>
            <li>
                <a href={{ route('device.sale') }} class="text-decoration-none">
                    <span class="sidebar-label">販売</span>
                </a>
            </li>
        </div>

        <li class="header" data-bs-target="#history" data-bs-toggle="collapse" style="cursor: pointer;">
            <i class="fa fa-history"></i><span class="sidebar-label">履歴</span>
            <i class="fas fa-sort-down sidebar-sort-icon"></i>
        </li>
        <div id='history' class="collapse list-unstyled pl-3">
            <li>
                <a href="{{ route('rental.history') }}" class="text-decoration-none">
                    <span class="sidebar-label">レンタル</span>
                </a>
            </li>
            <li>
                <a href="{{ route('sales.history') }}" class="text-decoration-none">
                    <span class="sidebar-label">販売</span>
                </a>
            </li>
        </div>

        <li class="header" data-bs-target="#data" data-bs-toggle="collapse" style="cursor: pointer;">
            <i class="fas fa-chart-pie"></i><span class="sidebar-label">データ一覧</span>
            <i class="fas fa-sort-down sidebar-sort-icon"></i>
        </li>
        <div id='data' class="collapse list-unstyled pl-3">
            <li>
                <a href='{{ route('device.file.benchmark') }}' class="text-decoration-none">
                    <span class="sidebar-label">商品データ</span>
                </a>
            </li>
            <li>
                <a href={{ route('client.list') }} class="text-decoration-none">
                    <span class="sidebar-label">クライアント</span>
                </a>
            </li>
            <li>
                <a href='{{ route('device.file.benchmark') }}' class="text-decoration-none">
                    <span class="sidebar-label">ベンチマーク</span>
                </a>
            </li>
        </div>

        <li class="header" data-bs-target="#register" data-bs-toggle="collapse" style="cursor: pointer;">
            <i class="fas fa-address-book"></i><span class="sidebar-label">登録</span>
            <i class="fas fa-sort-down sidebar-sort-icon"></i>
        </li>
        <div id='register' class="collapse list-unstyled pl-3">
            <li>
                <a href={{ route('device.register') }} class="text-decoration-none">
                    <span class="sidebar-label">機材</span>
                </a>
            </li>
        </div>

        <li class="header" data-bs-target="#system" data-bs-toggle="collapse" style="cursor: pointer;">
            <i class="fas fa-cogs"></i><span class="sidebar-label">設定</span>
            <i class="fas fa-sort-down sidebar-sort-icon"></i>
        </li>
        <div id='system' class="collapse list-unstyled pl-3">
            <li>
                <a href="{{ route('user.list') }}" class="text-decoration-none">
                    <span class="sidebar-label">ユーザー</span>
                </a>
            </li>
            <li>
                <a href="{{ route('device_categories.index') }}" class="text-decoration-none">
                    <span class="sidebar-label">機材カテゴリ</span>
                </a>
            </li>
            <li>
                <a href="{{ route('device_fields.index') }}" class="text-decoration-none">
                    <span class="sidebar-label">カスタムフィールド</span>
                </a>
            </li>
            <li>
                <a href="#" class="text-decoration-none">
                    <span class="sidebar-label">外部連携</span>
                </a>
            </li>
        </div>

    </ul>
</div>
