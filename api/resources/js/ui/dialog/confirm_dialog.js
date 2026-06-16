document.getElementById('confirm-dialog').addEventListener('click', confirm_dialog);

function confirm_dialog(event){
    if(window.confirm('削除すると戻すことはできません。削除してよろしいですか? ')){ // 確認ダイアログを表示
        return true; // 「OK」時は送信を実行
    }else{
        // 「キャンセル」時の処理
        event.stopPropagation();
        // イベントキャンセル
        event.preventDefault();
    }
}
