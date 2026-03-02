@extends('layouts.app')

@section('content')
@endsection

@section('main_contents')
    <div>
        <div class="col-md-12 shadow-sm p-0 mt-2 mb-2">
            <div class="device-bar d-flex align-items-center justify-content-between rounded bg-dark text-white p-3">
                <div class="device-name h3 m-0">
                    <i class="fas fa-sliders-h"></i> カスタムフィールド管理
                </div>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="text-muted mt-3 mb-3">
            <small>
                <i class="fas fa-info-circle"></i>
                フィールドを削除しても、登録済みデバイスのデータは保持されます（表示のみ非表示になります）。
            </small>
        </div>

        @foreach ($categories as $category)
            <div class="bg-white rounded shadow mb-4">
                {{-- カテゴリヘッダー --}}
                <div class="d-flex align-items-center justify-content-between p-3 border-bottom bg-light rounded-top">
                    <h5 class="m-0">
                        <i class="fa {{ $category->icon }}"></i>
                        {{ $category->name }}
                        <code class="ms-2 text-muted fs-6">{{ $category->code }}</code>
                    </h5>
                    <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal"
                        data-bs-target="#addFieldModal_{{ $category->code }}">
                        <i class="fas fa-plus"></i> フィールド追加
                    </button>
                </div>

                {{-- フィールド一覧 --}}
                @if ($category->fields->isEmpty())
                    <p class="text-muted p-3 mb-0"><i class="fas fa-info-circle"></i> このカテゴリにはまだカスタムフィールドがありません。</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-secondary">
                                <tr>
                                    <th style="width:40px;"><i class="fas fa-arrows-alt-v"></i></th>
                                    <th>ラベル</th>
                                    <th>種別</th>
                                    <th>必須</th>
                                    <th>選択肢</th>
                                    <th>操作</th>
                                </tr>
                            </thead>
                            <tbody class="sortable-fields" data-reorder-url="{{ route('device_fields.reorder') }}">
                                @foreach ($category->fields as $field)
                                    <tr data-id="{{ $field->id }}">
                                        <td class="drag-handle" style="cursor:grab;">
                                            <i class="fas fa-grip-vertical text-muted"></i>
                                        </td>
                                        <td>{{ $field->label }}</td>
                                        <td>
                                            @php
                                                $typeLabels = \App\Models\DeviceTypeField::FIELD_TYPES;
                                            @endphp
                                            <span
                                                class="badge bg-info text-dark">{{ $typeLabels[$field->field_type] ?? $field->field_type }}</span>
                                        </td>
                                        <td>
                                            @if ($field->is_required)
                                                <span class="badge bg-danger">必須</span>
                                            @else
                                                <span class="badge bg-secondary">任意</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($field->field_type === 'select' && $field->options)
                                                <small class="text-muted">
                                                    {{ collect($field->options)->pluck('label')->join(' / ') }}
                                                </small>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-outline-dark"
                                                data-bs-toggle="modal"
                                                data-bs-target="#editFieldModal_{{ $field->id }}">
                                                <i class="far fa-edit"></i>
                                            </button>
                                            <form action="{{ route('device_fields.destroy', $field->id) }}" method="POST"
                                                class="d-inline"
                                                onsubmit="return confirm('「{{ $field->label }}」を削除しますか？\n※ 登録済みデバイスのデータは消えませんが、表示されなくなります。');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    <i class="far fa-trash-alt"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>

                                    {{-- 編集モーダル --}}
                                    <div class="modal fade" id="editFieldModal_{{ $field->id }}" tabindex="-1">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <form action="{{ route('device_fields.update', $field->id) }}"
                                                    method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">フィールド編集</h5>
                                                        <button type="button" class="btn-close"
                                                            data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="mb-3">
                                                            <label class="form-label">ラベル <span
                                                                    class="text-danger">*</span></label>
                                                            <input type="text" class="form-control" name="label"
                                                                value="{{ $field->label }}" required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">種別 <span
                                                                    class="text-danger">*</span></label>
                                                            <select class="form-select field-type-select" name="field_type"
                                                                required>
                                                                @foreach (\App\Models\DeviceTypeField::FIELD_TYPES as $key => $label)
                                                                    <option value="{{ $key }}"
                                                                        {{ $field->field_type === $key ? 'selected' : '' }}>
                                                                        {{ $label }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="mb-3 form-check">
                                                            <input type="checkbox" class="form-check-input"
                                                                name="is_required"
                                                                id="is_required_edit_{{ $field->id }}" value="1"
                                                                {{ $field->is_required ? 'checked' : '' }}>
                                                            <label class="form-check-label"
                                                                for="is_required_edit_{{ $field->id }}">
                                                                必須項目にする
                                                            </label>
                                                        </div>
                                                        {{-- 選択肢管理（セレクト型のみ表示） --}}
                                                        <div class="options-container mb-3"
                                                            style="{{ $field->field_type === 'select' ? '' : 'display:none;' }}">
                                                            <label class="form-label">選択肢</label>
                                                            <div class="options-list">
                                                                @if ($field->options)
                                                                    @foreach ($field->options as $opt)
                                                                        <div class="input-group mb-2 option-row">
                                                                            <input type="text" class="form-control"
                                                                                name="options[][label]" placeholder="表示名"
                                                                                value="{{ $opt['label'] }}">
                                                                            <input type="text" class="form-control"
                                                                                name="options[][value]" placeholder="値"
                                                                                value="{{ $opt['value'] }}">
                                                                            <button type="button"
                                                                                class="btn btn-outline-danger remove-option">
                                                                                <i class="fas fa-times"></i>
                                                                            </button>
                                                                        </div>
                                                                    @endforeach
                                                                @endif
                                                            </div>
                                                            <button type="button"
                                                                class="btn btn-sm btn-outline-secondary add-option">
                                                                <i class="fas fa-plus"></i> 選択肢を追加
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary"
                                                            data-bs-dismiss="modal">キャンセル</button>
                                                        <button type="submit" class="btn btn-primary">更新</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            {{-- フィールド追加モーダル --}}
            <div class="modal fade" id="addFieldModal_{{ $category->code }}" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <form action="{{ route('device_fields.store') }}" method="POST">
                            @csrf
                            <input type="hidden" name="device_category_code" value="{{ $category->code }}">
                            <div class="modal-header">
                                <h5 class="modal-title">
                                    <i class="fa {{ $category->icon }}"></i>
                                    {{ $category->name }} — フィールド追加
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label">ラベル <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="label" placeholder="例: OSバージョン"
                                        required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">種別 <span class="text-danger">*</span></label>
                                    <select class="form-select field-type-select" name="field_type" required>
                                        @foreach (\App\Models\DeviceTypeField::FIELD_TYPES as $key => $label)
                                            <option value="{{ $key }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" name="is_required"
                                        id="is_required_add_{{ $category->code }}" value="1">
                                    <label class="form-check-label" for="is_required_add_{{ $category->code }}">
                                        必須項目にする
                                    </label>
                                </div>
                                {{-- 選択肢管理（セレクト型のみ表示） --}}
                                <div class="options-container mb-3" style="display:none;">
                                    <label class="form-label">選択肢</label>
                                    <div class="options-list"></div>
                                    <button type="button" class="btn btn-sm btn-outline-secondary add-option">
                                        <i class="fas fa-plus"></i> 選択肢を追加
                                    </button>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
                                <button type="submit" class="btn btn-success">追加</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endsection

@section('js')
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            // ---- 選択肢の追加・削除 ----
            function newOptionRow() {
                const row = document.createElement('div');
                row.className = 'input-group mb-2 option-row';
                row.innerHTML = `
            <input type="text" class="form-control" name="options[][label]" placeholder="表示名" required>
            <input type="text" class="form-control" name="options[][value]" placeholder="値" required>
            <button type="button" class="btn btn-outline-danger remove-option">
                <i class="fas fa-times"></i>
            </button>`;
                return row;
            }

            document.body.addEventListener('click', function(e) {
                // 選択肢追加ボタン
                if (e.target.closest('.add-option')) {
                    const container = e.target.closest('.options-container');
                    container.querySelector('.options-list').appendChild(newOptionRow());
                }
                // 選択肢削除ボタン
                if (e.target.closest('.remove-option')) {
                    e.target.closest('.option-row').remove();
                }
            });

            // ---- 種別変更で選択肢エリアを表示/非表示 ----
            document.body.addEventListener('change', function(e) {
                if (e.target.classList.contains('field-type-select')) {
                    const modal = e.target.closest('.modal-body');
                    const optionsContainer = modal.querySelector('.options-container');
                    if (e.target.value === 'select') {
                        optionsContainer.style.display = '';
                    } else {
                        optionsContainer.style.display = 'none';
                    }
                }
            });

            // ---- ドラッグ＆ドロップ並び替え ----
            document.querySelectorAll('.sortable-fields').forEach(function(tbody) {
                const reorderUrl = tbody.dataset.reorderUrl;
                let dragSrc = null;

                tbody.querySelectorAll('tr[data-id]').forEach(function(row) {
                    row.setAttribute('draggable', 'true');

                    row.addEventListener('dragstart', function(e) {
                        dragSrc = row;
                        e.dataTransfer.effectAllowed = 'move';
                        row.classList.add('opacity-50');
                    });

                    row.addEventListener('dragend', function() {
                        row.classList.remove('opacity-50');
                        tbody.querySelectorAll('tr').forEach(r => r.classList.remove(
                            'drag-over'));
                    });

                    row.addEventListener('dragover', function(e) {
                        e.preventDefault();
                        e.dataTransfer.dropEffect = 'move';
                        tbody.querySelectorAll('tr').forEach(r => r.classList.remove(
                            'drag-over'));
                        row.classList.add('drag-over');
                    });

                    row.addEventListener('drop', function(e) {
                        e.preventDefault();
                        if (dragSrc !== row) {
                            const rows = [...tbody.querySelectorAll('tr[data-id]')];
                            const srcIdx = rows.indexOf(dragSrc);
                            const tgtIdx = rows.indexOf(row);
                            if (srcIdx < tgtIdx) {
                                row.after(dragSrc);
                            } else {
                                row.before(dragSrc);
                            }
                            const ids = [...tbody.querySelectorAll('tr[data-id]')].map(r =>
                                r.dataset.id);
                            fetch(reorderUrl, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector(
                                        'meta[name="csrf-token"]').content
                                },
                                body: JSON.stringify({
                                    ids
                                })
                            });
                        }
                    });
                });
            });
        });
    </script>
    <style>
        .drag-handle {
            cursor: grab;
        }

        .drag-over {
            border-top: 2px solid #0d6efd;
        }
    </style>
@endsection
