<p>新しいメールアドレスを入力してください<br>
    入力したメールアドレスに認証メールが送信されます</p>
  <p>認証リンクの有効時間は発行から30分です</p>

<p id="error_message" class="text-danger"></p>

<form id="emailChangeForm" method='POST' action="{{ route('profile.email.change') }}">
    @csrf
    <div class="form-group">
        <input type="email" class="form-control" id="email" name="email" placeholder="new_email@sample.co.jp"
            required>
    </div>

    <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">キャンセル</button>
        <button type="submit" class="btn btn-outline-dark" id="reboot-save-btn">送信</button>
    </div>
</form>