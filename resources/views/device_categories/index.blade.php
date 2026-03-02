@extends('layouts.app')

@section('content')
@endsection

@section('main_contents')
    <div>
        <div class="col-md-12 shadow-sm p-0 mt-2 mb-2">
            <div class="device-bar d-flex align-items-center justify-content-between rounded bg-dark text-white p-3">
                <div class="device-name h3 m-0">
                    {{ __('機材カテゴリ管理') }}
                </div>
            </div>
        </div>

        {{-- 新規カテゴリ追加フォーム --}}
        <div class="bg-white rounded shadow p-4 mb-4">
            <h5 class="mb-3">新規カテゴリ追加</h5>
            <form action="{{ route('device_categories.store') }}" method="POST" class="row g-3 align-items-end">
                @csrf
                <div class="col-md-2">
                    <label for="new_code" class="form-label">コード <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="new_code" name="code"
                           placeholder="例: DRONE" value="{{ old('code') }}" required
                           pattern="[A-Z0-9_]+" title="半角英大文字・数字・アンダースコアのみ">
                </div>
                <div class="col-md-3">
                    <label for="new_name" class="form-label">表示名 <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="new_name" name="name"
                           placeholder="例: ドローン" value="{{ old('name') }}" required>
                </div>
                <div class="col-md-3">
                    <label for="new_icon" class="form-label">アイコン (FontAwesome)</label>
                    <input type="text" class="form-control" id="new_icon" name="icon"
                           placeholder="例: fa-helicopter" value="{{ old('icon') }}">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-success w-100">
                        <i class="fas fa-plus"></i> 追加
                    </button>
                </div>
            </form>
        </div>

        {{-- カテゴリ一覧 --}}
        <div class="bg-white rounded shadow table-responsive">
            <table class="table table-hover mb-0" id="categoryTable">
                <thead class="table-dark">
                    <tr>
                        <th scope="col" style="width: 50px;"><i class="fas fa-arrows-alt-v"></i></th>
                        <th scope="col">コード</th>
                        <th scope="col">表示名</th>
                        <th scope="col">アイコン</th>
                        <th scope="col">登録台数</th>
                        <th scope="col">有効</th>
                        <th scope="col">操作</th>
                    </tr>
                </thead>
                <tbody id="sortableCategories" data-reorder-url="{{ route('device_categories.reorder') }}">
                    @foreach ($categories as $category)
                        <tr data-id="{{ $category->id }}">
                            <td class="drag-handle" style="cursor: grab;">
                                <i class="fas fa-grip-vertical text-muted"></i>
                            </td>
                            <td>
                                <code>{{ $category->code }}</code>
                            </td>
                            <td>
                                <i class="fa {{ $category->icon }}"></i>
                                {{ $category->name }}
                            </td>
                            <td>
                                <small class="text-muted">{{ $category->icon }}</small>
                            </td>
                            <td>
                                <span class="badge bg-{{ ($deviceCounts[$category->code] ?? 0) > 0 ? 'primary' : 'secondary' }}">
                                    {{ $deviceCounts[$category->code] ?? 0 }} 台
                                </span>
                            </td>
                            <td>
                                @if ($category->is_active)
                                    <span class="badge bg-success">有効</span>
                                @else
                                    <span class="badge bg-secondary">無効</span>
                                @endif
                            </td>
                            <td>
                                <button type="button" class="btn btn-sm btn-outline-dark" data-bs-toggle="modal"
                                        data-bs-target="#editModal{{ $category->id }}">
                                    <i class="far fa-edit"></i>
                                </button>
                                @if (($deviceCounts[$category->code] ?? 0) === 0)
                                    <form action="{{ route('device_categories.destroy', $category->id) }}" method="POST"
                                          class="d-inline" onsubmit="return confirm('「{{ $category->name }}」を削除しますか？');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="far fa-trash-alt"></i>
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>

                        {{-- 編集モーダル --}}
                        <div class="modal fade" id="editModal{{ $category->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form action="{{ route('device_categories.update', $category->id) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <div class="modal-header">
                                            <h5 class="modal-title">カテゴリ編集: {{ $category->name }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label class="form-label">コード <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" name="code"
                                                       value="{{ $category->code }}" required
                                                       pattern="[A-Z0-9_]+" title="半角英大文字・数字・アンダースコアのみ">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">表示名 <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" name="name"
                                                       value="{{ $category->name }}" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">アイコン (FontAwesome)</label>
                                                <input type="text" class="form-control" name="icon"
                                                       value="{{ $category->icon }}">
                                                <small class="text-muted">
                                                    プレビュー: <i class="fa {{ $category->icon }}"></i>
                                                </small>
                                            </div>
                                            <div class="form-check mb-3">
                                                <input type="checkbox" class="form-check-input" name="is_active"
                                                       id="is_active_{{ $category->id }}" value="1"
                                                       {{ $category->is_active ? 'checked' : '' }}>
                                                <label class="form-check-label" for="is_active_{{ $category->id }}">有効</label>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
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

        <div class="mt-3 text-muted">
            <small>
                <i class="fas fa-info-circle"></i>
                行をドラッグ＆ドロップして表示順を変更できます。並び替えは自動保存されます。
            </small>
        </div>
    </div>
@endsection

@section('js')
    @vite(['resources/js/components/sortable-categories.ts'])
    <style>
        .drag-handle:hover { cursor: grab; }
        .drag-over { border-top: 2px solid #0d6efd; }
        tr[draggable="true"] { cursor: grabbing; }
    </style>
@endsection
