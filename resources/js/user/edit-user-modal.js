document.addEventListener('DOMContentLoaded', function () {
    const dialog = document.getElementById('EditUserModal');
    const closeBtn = document.getElementById('editUserCloseBtn');
    const cancelBtn = document.getElementById('editUserCancelBtn');

    // 行クリックでモーダルを開く
    const rows = document.querySelectorAll('.edit-user-row');
    rows.forEach(function (row) {
        row.addEventListener('click', function () {
            document.getElementById('edit_user_id').value = this.dataset.id;
            document.getElementById('edit_user_name').value = this.dataset.name;
            document.getElementById('edit_user_email').value = this.dataset.email;
            document.getElementById('edit_user_role').value = this.dataset.role;
            document.getElementById('edit_user_password').value = '';
            document.getElementById('edit_user_password_confirmation').value = '';

            dialog.showModal();
        });
    });

    // 閉じるボタン
    closeBtn.addEventListener('click', function () {
        dialog.close();
    });

    cancelBtn.addEventListener('click', function () {
        dialog.close();
    });

    // backdrop クリックで閉じる
    dialog.addEventListener('click', function (e) {
        if (e.target === dialog) {
            dialog.close();
        }
    });
});
