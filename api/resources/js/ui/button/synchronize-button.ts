// 複数のボタンを取得する
const btn = document.getElementById("synchronize-button") as HTMLButtonElement;
// リスナーを設定する
btn.addEventListener("click", function () {
    synchronizeClients(btn);
  });

/** CRMデータを同期する
 * @access public
 * @param button 
 */
const synchronizeClients = async (button: HTMLButtonElement) => {
  const child = button.querySelector('i') as HTMLElement;
  button.removeChild(child);

  const span = document.createElement('span');
  span.className = 'spinner-border spinner-border-sm'; // スピナー
  button.appendChild(span);

  const i_tag = document.createElement('i');
  i_tag.className = 'fas fa-redo'; // リフレッシュアイコン
  try {
    const url = "/sync/crm";
    const response = await fetch(url, {
      method: "GET",
      headers: {
        "Content-Type": "application/json",
      },
    });

    // レスポンスデータを取得
    const responseData = await response.text();
    const obj = JSON.parse(responseData);
    if (obj.status === 'success') {
      alert("同期が完了しました");
    }else if(obj.status === "error"){
      throw new Error("内容に不備があります。サーバー管理者に連絡してください");
    }
  } catch (err) {
    alert("通信に失敗しました。しばらくしてから再度お試しください");
  }
  button.removeChild(span);
  button.appendChild(i_tag);
}

(window as any).synchronizeClients = synchronizeClients;
