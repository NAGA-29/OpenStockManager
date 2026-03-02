// 複数のボタンを取得する
const buttons = document.querySelectorAll(".switch-button");
console.log(buttons);
// リスナーを設定する
buttons.forEach((button) => {
  button.addEventListener("click", function () {
    saveButtonStatus(button);
  });
});

// fetch APIとasync/await構文を使用する
async function saveButtonStatus(button) {
  // fetch APIを使用してPOSTリクエストを送信する
  const group_id = button.getAttribute("group_id");
  const className = button.getAttribute("class");
  const csrfToken = document.querySelector('input[name="_token"]').getAttribute('value');

  // aタグの子要素を取得
  const child = button.querySelector('i');
  button.removeChild(child);
  // span要素を作成
  const span = document.createElement('span');
  span.className = 'spinner-border spinner-border-sm';
  button.appendChild(span);
  // i要素を準備
  const i_tag = document.createElement('i');
  i_tag.className = "fas fa-bell";
  try {
    // // fetch APIを使用してPOSTリクエストを送信する
    // const group_id = button.getAttribute("group_id");
    // const className = button.getAttribute("class");
    // const csrfToken = document.querySelector('input[name="_token"]').getAttribute('value');

    // // aタグの子要素を取得
    // const child = button.querySelector('i');
    // button.removeChild(child);
    // // span要素を作成
    // const span = document.createElement('span');
    // span.className = 'spinner-border spinner-border-sm';
    // button.appendChild(span);
    // // i要素を準備
    // const i_tag = document.createElement('i');
    // i_tag.className = "fas fa-bell";

    const response = await fetch(`/terminal/group/update/${group_id}/status`, {
      method: "POST",
      headers: {
        "X-CSRF-TOKEN": csrfToken,
        "Content-Type": "application/json",
      },
      body: JSON.stringify({
        'group_id': group_id,
        'on_off' : button.getAttribute("on_off"),
      }),
    });
    // 通信成功時の処理
    // レスポンスデータを取得する
    const responseData = await response.text();
    const obj = JSON.parse(responseData);
    // ボタンの文字列を切り替える ON->OFF or OFF->ON
    if (obj.status === 'success') {
      button.removeChild(span);
      // iタグを追加
      button.appendChild(i_tag);
      if (obj.on_off == '1') {
        button.setAttribute("on_off", "1");
        button.className = "btn btn-outline-success switch-button";
      } else if (obj.on_off == '0') {
        button.setAttribute("on_off", "0");
        button.className = "btn btn-outline-secondary switch-button";
      }
    }else if(obj.status === "error"){
      // button.removeChild(child);
      // button.className = className;
      // // iタグを追加
      // button.appendChild(i_tag);
      throw new Error("内容に不備があります。サーバー管理者に連絡してください。");
    }
  } catch (err) {
    button.removeChild(child);
    button.className = className;
    // iタグを追加
    button.appendChild(i_tag);
    alert("通信に失敗しました。しばらくしてから再度お試しください。");
  }
}
