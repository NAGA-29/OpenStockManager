<div class="sidebar-container bg-dark" id="side_navbar" style='min-height:100vh'>
    <div class="sidebar-logo">
    </div>
    <ul class="sidebar-navigation flex-column">
        <li class="header" style="cursor: pointer;">
            <a href="{{ route('dashboard') }}" class="text-decoration-none" style="padding: 0;">
                <i class="fa fa-home"> ダッシュボード</i>
            </a>
        </li>

        <li class="header" style="cursor: pointer;">
            <a href="{{ route('device.list') }}" class="text-decoration-none" style="padding: 0;">
                <i class="fa fa-tablet-alt"> 在庫一覧</i>
            </a>
        </li>

        <li class="header" data-bs-target="#procedure" data-bs-toggle="collapse" style="cursor: pointer;">
            <i class="fa fa-file-signature"> 手続き</i>
            <i class="fas fa-sort-down"></i>
        </li>
        <div id='procedure' class="collapse list-unstyled pl-3">
            <li>
                <a href="{{ route('device.rental') }}" class="text-decoration-none">
                    レンタル
                </a>
            </li>
            <li>
                <a href={{ route('device.sale') }} class="text-decoration-none">
                    販売
                </a>
            </li>
        </div>

        <li class="header" data-bs-target="#history" data-bs-toggle="collapse" style="cursor: pointer;">
            <i class="fa fa-history"> 履歴</i>
            <i class="fas fa-sort-down"></i>
        </li>
        <div id='history' class="collapse list-unstyled pl-3">
            <li>
                <a href="{{ route('rental.history') }}" class="text-decoration-none">
                    レンタル
                </a>
            </li>
            <li>
                <a href="{{ route('sales.history') }}" class="text-decoration-none">
                    販売
                </a>
            </li>
        </div>
        <li class="header" data-bs-target="#data" data-bs-toggle="collapse" style="cursor: pointer;">
            <i class="fas fa-chart-pie"> データ一覧</i>
            <i class="fas fa-sort-down"></i>
        </li>
        <div id='data' class="collapse list-unstyled pl-3">
            <li>
                <a href='{{ route('device.file.benchmark') }}' class="text-decoration-none">
                    商品データ
                </a>
            </li>
            <li>
                <a href={{ route('client.list') }} class="text-decoration-none">
                    クライアント
                </a>
            </li>
            <li>
                <a href='{{ route('device.file.benchmark') }}' class="text-decoration-none">
                    ベンチマーク
                </a>
            </li>
        </div>

        <li class="header" data-bs-target="#register" data-bs-toggle="collapse" style="cursor: pointer;">
            <i class="fas fa-address-book"> 登録</i>
            <i class="fas fa-sort-down"></i>
        </li>
        <div id='register' class="collapse list-unstyled pl-3">
            <li>
                <a href={{ route('device.register') }} class="text-decoration-none">
                    機材
                </a>
            </li>
        </div>

        <li class="header" data-bs-target="#system" data-bs-toggle="collapse" style="cursor: pointer;">
            <i class="fas fa-cogs"> 設定</i>
            <i class="fas fa-sort-down"></i>
        </li>
        <div id='system' class="collapse list-unstyled pl-3">
            <li>
                <a href="{{ route('user.list') }}" class="text-decoration-none">
                    管理者管理
                </a>
            </li>
            <li>
                <a href="{{ route('device_categories.index') }}" class="text-decoration-none">
                    機材カテゴリ管理
                </a>
            </li>
            <li>
                <a href="{{ route('device_fields.index') }}" class="text-decoration-none">
                    カスタムフィールド管理
                </a>
            </li>
        </div>
    </ul>
</div>
