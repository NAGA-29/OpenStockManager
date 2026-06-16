<dialog id="EditUserModal" class="edit-user-dialog">
    <div class="edit-user-dialog__header">
        <h5>{{ __('ユーザー情報編集') }}</h5>
        <button type="button" class="edit-user-dialog__close" id="editUserCloseBtn" aria-label="Close">&times;</button>
    </div>
    <form action="{{ route('user.update') }}" method="POST">
        @csrf
        <div class="edit-user-dialog__body">
            <input type="hidden" id="edit_user_id" name="id" value="">

            {{-- 名前 --}}
            <div class="edit-user-dialog__field">
                <label for="edit_user_name" class="edit-user-dialog__label">{{ __('名前') }}</label>
                <div class="edit-user-dialog__input-wrap">
                    <input id="edit_user_name" type="text" class="edit-user-dialog__input" name="name" value=""
                        required>
                    @error('name')
                        <span class="edit-user-dialog__error">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            {{-- メールアドレス --}}
            <div class="edit-user-dialog__field">
                <label for="edit_user_email" class="edit-user-dialog__label">{{ __('メールアドレス') }}</label>
                <div class="edit-user-dialog__input-wrap">
                    <input id="edit_user_email" type="email" class="edit-user-dialog__input" name="email" value=""
                        required>
                    @error('email')
                        <span class="edit-user-dialog__error">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            {{-- 権限 --}}
            <div class="edit-user-dialog__field">
                <label for="edit_user_role" class="edit-user-dialog__label">{{ __('権限') }}</label>
                <div class="edit-user-dialog__input-wrap">
                    <select id="edit_user_role" class="edit-user-dialog__input" name="role" required>
                        <option value="admin">{{ __('管理者') }}</option>
                        <option value="user">{{ __('一般ユーザー') }}</option>
                    </select>
                    @error('role')
                        <span class="edit-user-dialog__error">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            {{-- パスワード --}}
            <div class="edit-user-dialog__field">
                <label for="edit_user_password" class="edit-user-dialog__label">{{ __('パスワード') }}</label>
                <div class="edit-user-dialog__input-wrap">
                    <input id="edit_user_password" type="password" class="edit-user-dialog__input" name="password">
                    <small class="edit-user-dialog__hint">{{ __('変更する場合のみ入力') }}</small>
                    @error('password')
                        <span class="edit-user-dialog__error">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            {{-- パスワード確認 --}}
            <div class="edit-user-dialog__field">
                <label for="edit_user_password_confirmation"
                    class="edit-user-dialog__label">{{ __('パスワード確認') }}</label>
                <div class="edit-user-dialog__input-wrap">
                    <input id="edit_user_password_confirmation" type="password" class="edit-user-dialog__input"
                        name="password_confirmation">
                </div>
            </div>
        </div>
        <div class="edit-user-dialog__footer">
            <button type="submit" class="edit-user-dialog__btn edit-user-dialog__btn--save">{{ __('保存') }}</button>
            <button type="button" class="edit-user-dialog__btn edit-user-dialog__btn--cancel"
                id="editUserCancelBtn">{{ __('閉じる') }}</button>
        </div>
    </form>
</dialog>
