<form class="profile-group" action="{{ Route('device.update') }}" method="POST" enctype="multipart/form-data"
    novalidate='novalidate' id="edit-device-form">
    @csrf

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- 端末ID --}}
    <div class="mb-3 row">
        <label for="device_id" class="col-md-4 col-form-label text-md-end">{{ __('端末ID') }}</label>
        <div class="col-md-6">
            <p>{{ $device_info_collection->device_id }}</p>
            <input id="device_id" type="hidden" class="form-control" name="device_id"
                value="{{ $device_info_collection->device_id }}" required>
        </div>
    </div>

    {{-- 端末区分 --}}
    <div class="mb-3 row">
        <label for="device_type" class="col-md-4 col-form-label text-md-end">{{ __('端末区分') }}</label>
        <div class="col-md-6">
            <select class="form-select" name="device_type" id="edit_device_type">
                @foreach ($deviceCategories as $cat)
                    <option value="{{ $cat->code }}" {{ $device_info_collection->device_type == $cat->code ? 'selected' : '' }}>
                        {{ $cat->name }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- 端末名 --}}
    <div class="mb-3 row">
        <label for="device_name" class="col-md-4 col-form-label text-md-end">{{ __('端末名') }}</label>
        <div class="col-md-6">
            <input id="device_name" type="text" class="form-control" name="device_name"
                value="{{ $device_info_collection->device_name }}" required>
        </div>
    </div>

    {{-- 端末シリアル --}}
    <div class="mb-3 row">
        <label for="device_serial" class="col-md-4 col-form-label text-md-end">{{ __('端末シリアル') }}</label>
        <div class="col-md-6">
            <input id="device_serial" type="text" class="form-control" name="device_serial"
                value="{{ $device_info_collection->device_serial }}" required>
        </div>
    </div>

    {{-- カスタムフィールド（デバイス種別に応じて動的表示） --}}
    <div id="edit-custom-fields-container"></div>

    {{-- 初稼働日 --}}
    <div class="mb-3 row">
        <label for="first_work_date_at" class="col-md-4 col-form-label text-md-end">{{ __('初稼働日') }}</label>
        <div class="col-md-6">
            <input id="first_work_date_at" type="date" class="form-control" name="first_work_date_at"
                value="{{ $date_list['first_work_date_at'] }}" required>
        </div>
    </div>

    {{-- 購入日 --}}
    <div class="mb-3 row">
        <label for="purchase_date_at" class="col-md-4 col-form-label text-md-end">{{ __('購入日') }}</label>
        <div class="col-md-6">
            <input id="purchase_date_at" type="date" class="form-control" name="purchase_date_at"
                value="{{ $date_list['purchase_date_at'] }}" required>
        </div>
    </div>

    {{-- オプション --}}
    <div class="mb-3 row">
        <label for="option" class="col-md-4 col-form-label text-md-end">{{ __('オプション') }}</label>
        <div class="col-md-6">
            <input id="option" type="text" class="form-control" name="option"
                value="{{ $device_info_collection->option }}" required>
        </div>
    </div>

    {{-- コンディション --}}
    <div class="mb-3 row">
        <label for="condition" class="col-md-4 col-form-label text-md-end">{{ __('コンディション') }}</label>
        <div class="col-md-6">
            <select id="condition" class="form-select" name="condition">
              <option value='1' {{ $device_info_collection->condition_id === 1 ? 'selected' : '' }}>新品</option>
              <option value='2' {{ $device_info_collection->condition_id === 2 ? 'selected' : '' }}>新古品</option>
              <option value='3' {{ $device_info_collection->condition_id === 3 ? 'selected' : '' }}>中古</option>
              <option value='4' {{ $device_info_collection->condition_id === 4 ? 'selected' : '' }}>ジャンク品</option>
            </select>
        </div>
    </div>

    {{-- 不具合 --}}
    <div class="mb-3 row">
        <label for="defective" class="col-md-4 col-form-label text-md-end">{{ __('不具合') }}</label>
        <div class="col-md-6">
            <input id="defective" type="checkbox" class="form-check-input" name="defective" value=1
                {{ $device_info_collection->defective == 1 ? 'checked' : '' }}>
        </div>
    </div>

    {{-- 販売不可 --}}
    <div class="mb-3 row">
        <label for="not_for_sale" class="col-md-4 col-form-label text-md-end">{{ __('販売不可') }}</label>
        <div class="col-md-6">
            <input id="not_for_sale" type="checkbox" class="form-check-input" name="not_for_sale" value=1
                {{ $device_info_collection->not_for_sale == 1 ? 'checked' : '' }}>
        </div>
    </div>

    {{-- ノート --}}
    <div class="mb-3 row">
        <label for="note" class="col-md-4 col-form-label text-md-end">{{ __('ノート') }}</label>
        <div class="col-md-6">
            <textarea id="note" rows=8 class="form-control" name="note">{{ $device_info_collection->note }}</textarea>
        </div>
    </div>

    {{-- 画像 --}}
    <div class="form-group row">
        @foreach ($device_info_collection->contents as $device_image)
            <div class="col-md-4 image-container">
                <input type="hidden" name="imageList[]" value="{{ $device_image->id }}">
                <img src="{{ asset($device_image->path) }}" class="img-fluid pt-2 pb-2 image-gallery"
                    data-id="{{ $device_image->id }}">
                <span class="delete-mark" onclick="deleteImage('{{ $device_image->id }}')"></span>
            </div>
        @endforeach
    </div>

    <div class="row justify-content-center">
        <div class="p-2">
            <input id="device_image" type="file" class="form-control @error('device_image') is-invalid @enderror"
                name="device_image" onchange="previewImage(event)" accept="image/png, image/jpeg">
            {{-- preview --}}
            <img id="image_preview" src="#" alt="Image Preview">
        </div>
    </div>

    {{-- ボタン類 --}}
    <div class="form-group text-center">
        <button type="submit" class="btn btn-outline-dark m-2">{{ __('保存') }}</button>
        <button type="button" class="btn btn-outline-secondary m-2"
            data-bs-dismiss="modal">{{ __('閉じる') }}</button>
    </div>
</form>

<script>
    // TODO: 外部ファイル化
    function previewImage(event) {
        const reader = new FileReader();
        reader.onload = function() {
            const output = document.getElementById('image_preview');
            output.src = reader.result;
            output.style.display = 'block';
        };
        reader.readAsDataURL(event.target.files[0]);
    }

    const existingCustomFields = @json($device_info_collection->custom_fields ?? []);

    function renderEditCustomFields(fields) {
        const container = document.getElementById('edit-custom-fields-container');
        if (!fields || fields.length === 0) {
            container.innerHTML = '';
            return;
        }
        let html = '<hr><div class="text-muted small mb-2">カスタムフィールド</div>';
        fields.forEach(function(field) {
            const currentValues = existingCustomFields || {};
            const val = currentValues[field.field_key] !== undefined ? currentValues[field.field_key] : '';
            const required = field.is_required ? 'required' : '';
            const reqBadge = field.is_required ? '<span class="text-danger">*</span>' : '';
            html += `<div class="mb-3 row">`;
            html += `<label class="col-md-4 col-form-label text-md-end">${field.label} ${reqBadge}</label>`;
            html += `<div class="col-md-6">`;
            if (field.field_type === 'text') {
                html += `<input class="form-control" type="text" name="custom_fields[${field.field_key}]" value="${val}" ${required}>`;
            } else if (field.field_type === 'number') {
                html += `<input class="form-control" type="number" name="custom_fields[${field.field_key}]" value="${val}" ${required}>`;
            } else if (field.field_type === 'boolean') {
                const checked = val == '1' || val === true ? 'checked' : '';
                html += `<input class="form-check-input" type="checkbox" name="custom_fields[${field.field_key}]" value="1" ${checked}>`;
            } else if (field.field_type === 'select') {
                html += `<select class="form-select" name="custom_fields[${field.field_key}]" ${required}>`;
                if (!field.is_required) html += `<option value="">-- 選択 --</option>`;
                (field.options || []).forEach(function(opt) {
                    const sel = val === opt.value ? 'selected' : '';
                    html += `<option value="${opt.value}" ${sel}>${opt.label}</option>`;
                });
                html += `</select>`;
            }
            html += `</div></div>`;
        });
        html += '<hr>';
        container.innerHTML = html;
    }

    function loadEditCustomFields(code) {
        fetch(`/device/fields/${code}`)
            .then(r => r.json())
            .then(fields => renderEditCustomFields(fields))
            .catch(() => { document.getElementById('edit-custom-fields-container').innerHTML = ''; });
    }

    document.addEventListener('DOMContentLoaded', function () {
        const editModal = document.getElementById('EditModal');
        if (editModal) {
            editModal.addEventListener('shown.bs.modal', function () {
                const sel = document.getElementById('edit_device_type');
                if (sel && sel.value) {
                    loadEditCustomFields(sel.value);
                }
            });
        }

        const select = document.getElementById('edit_device_type');
        if (select) {
            select.addEventListener('change', function () {
                loadEditCustomFields(this.value);
            });
        }
    });
</script>
