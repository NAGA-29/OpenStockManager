<div class="col-md-12 shadow-sm p-0 mt-2 mb-2">
    <div class="device-bar d-flex align-items-center justify-content-between rounded bg-dark text-white p-3">
        <div class="device-name h3 m-0">
            {{ __('新規デバイス登録 (単数)') }}
        </div>
    </div>
</div>

@if (Session::has('client_register'))
    <div class="alert alert-success" role="alert">
        {{ session('client_register') }}
    </div>
@endif

@if (Session::has('registered_device_id'))
    <div class="alert alert-info d-flex align-items-center justify-content-between" role="alert">
        <div>
            <i class="fas fa-check-circle"></i>
            端末 <strong>{{ session('registered_device_id') }}</strong> を登録しました。
        </div>
        <a href="{{ route('device.barcode', ['device_id' => session('registered_device_id')]) }}"
            class="btn btn-primary btn-sm" target="_blank">
            <i class="fas fa-barcode"></i> バーコードを印刷
        </a>
    </div>
@endif

<div class="bg-white rounded shadow table-responsive text-nowrap">
    <div class="card">
        <div class="card-body m-5">
            <p>[ <span class="text-danger">*</span> ] は入力必須</p>
            <form action="{{ route('device.save') }}" method="POST" class="h6 fw-bold" enctype="multipart/form-data">
                @csrf

                <div class="mb-3">
                    <label for="device_type" class="form-label">端末区分 <span class="text-danger">*</span></label>
                    <select class="form-select" name="device_type" id="device_type" required>
                        @foreach ($deviceCategories as $cat)
                            <option value="{{ $cat->code }}" {{ old('device_type') == $cat->code ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label for="device_name" class="form-label">端末名 <span class="text-danger">*</span></label>
                    <input class="form-control" type="text" name="device_name" value="{{ old('device_name') }}"
                        id="device_name" required>
                </div>

                <div class="mb-3">
                    <label for="device_serial" class="form-label">端末シリアル <span class="text-danger">*</span></label>
                    <input class="form-control" type="text" name="device_serial" value="{{ old('device_serial') }}"
                        id="device_serial" required>
                </div>

                {{-- カスタムフィールド（デバイス種別に応じて動的表示） --}}
                <div id="register-custom-fields-container"></div>

                <div class="mb-3">
                    <label for="first_work_date_at" class="form-label">初稼働日</label>
                    <input class="form-control" type="date" name="first_work_date_at"
                        value="{{ old('first_work_date_at') }}" id="first_work_date_at">
                </div>

                <div class="mb-3">
                    <label for="purchase_date_at" class="form-label">購入日</label>
                    <input class="form-control" type="date" name="purchase_date_at"
                        value="{{ old('purchase_date_at') }}" id="purchase_date_at">
                </div>

                <div class="mb-3">
                    <label for="client" class="form-label">販売先</label>
                    <input class="form-control" type="text" name="client" id="client">
                </div>

                <div class="mb-3">
                    <label for="sale_date_at" class="form-label">販売日</label>
                    <input class="form-control" type="date" name="sale_date_at" id="sale_date_at">
                </div>

                {{-- コンディション --}}
                <div class="mb-3">
                    <label for="condition" class="form-check-label">{{ __('コンディション') }}</label>
                    <select id="condition" class="form-select" name="condition">
                        <option value='1' {{ old('condition') == 1 ? 'selected' : '' }}>新品</option>
                        <option value='2' {{ old('condition') == 2 ? 'selected' : '' }}>新古品</option>
                        <option value='3' {{ old('condition') == 3 ? 'selected' : '' }}>中古</option>
                        <option value='4' {{ old('condition') == 4 ? 'selected' : '' }}>ジャンク品</option>
                    </select>
                </div>

                <div class="form-check mb-3">
                    <input type="checkbox" class="form-check-input" name="defective" id="defective" value="1">
                    <label for="defective" class="form-check-label">不具合</label>
                </div>

                <div class="form-check mb-3">
                    <input type="checkbox" class="form-check-input" name="not_for_sale" id="not_for_sale"
                        value="1">
                    <label for="not_for_sale" class="form-check-label">販売不可</label>
                </div>

                <div class="mb-3">
                    <label for="note" class="form-label">ノート</label>
                    <textarea class="form-control" name="note" id="note" rows="5">{{ old('note') }}</textarea>
                </div>

                {{-- 画像 --}}
                <div class="mb-3">
                    <label for="device_image" class="form-label">端末写真 [ jpg, png ]</label>
                    <input id="device_image" type="file" class="form-control" name="device_image"
                        onchange="previewImage(event)" accept="image/png, image/jpeg">
                    {{-- アップロードされた画像を表示 --}}
                    <img id="image_preview" src="#" alt="Image Preview"
                        style="display: none; max-width: 100%; margin-top: 10px;">
                </div>

                <div class="text-center">
                    <button type="submit" class="btn btn-outline-dark mr-3">登録</button>
                    <button type="reset" class="btn btn-outline-danger mr-3">リセット</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function previewImage(event) {
        const reader = new FileReader();
        reader.onload = function() {
            const output = document.getElementById('image_preview');
            output.src = reader.result;
            output.style.display = 'block';
        };
        reader.readAsDataURL(event.target.files[0]);
    }

    const oldCustomFields = @json(old('custom_fields', []));

    function renderCustomFields(fields) {
        const container = document.getElementById('register-custom-fields-container');
        if (!fields || fields.length === 0) {
            container.innerHTML = '';
            return;
        }
        let html = '<hr><p class="text-muted small">カスタムフィールド</p>';
        fields.forEach(function(field) {
            const val = oldCustomFields[field.field_key] ?? '';
            const required = field.is_required ? 'required' : '';
            const reqBadge = field.is_required ? '<span class="text-danger">*</span>' : '';
            html += `<div class="mb-3">`;
            html += `<label class="form-label">${field.label} ${reqBadge}</label>`;
            if (field.field_type === 'text') {
                html += `<input class="form-control" type="text" name="custom_fields[${field.field_key}]" value="${val}" ${required}>`;
            } else if (field.field_type === 'number') {
                html += `<input class="form-control" type="number" name="custom_fields[${field.field_key}]" value="${val}" ${required}>`;
            } else if (field.field_type === 'boolean') {
                const checked = val == '1' ? 'checked' : '';
                html += `<div class="form-check">
                    <input class="form-check-input" type="checkbox" name="custom_fields[${field.field_key}]" value="1" ${checked}>
                </div>`;
            } else if (field.field_type === 'select') {
                html += `<select class="form-select" name="custom_fields[${field.field_key}]" ${required}>`;
                if (!field.is_required) html += `<option value="">-- 選択 --</option>`;
                (field.options || []).forEach(function(opt) {
                    const sel = val === opt.value ? 'selected' : '';
                    html += `<option value="${opt.value}" ${sel}>${opt.label}</option>`;
                });
                html += `</select>`;
            }
            html += `</div>`;
        });
        html += '<hr>';
        container.innerHTML = html;
    }

    function loadCustomFields(code) {
        fetch(`/device/fields/${code}`)
            .then(r => r.json())
            .then(fields => renderCustomFields(fields))
            .catch(() => { document.getElementById('register-custom-fields-container').innerHTML = ''; });
    }

    document.addEventListener('DOMContentLoaded', function () {
        const select = document.getElementById('device_type');
        if (select && select.value) {
            loadCustomFields(select.value);
        }
        if (select) {
            select.addEventListener('change', function () {
                loadCustomFields(this.value);
            });
        }
    });
</script>
