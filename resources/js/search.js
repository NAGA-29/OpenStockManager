import "bootstrap";

/**
 * HTMLエスケープ処理
 * @param {string} str
 * @returns {string}
 */
function escapeHtml(str) {
  if (str == null) return '';
  return String(str)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');
}

document.addEventListener("DOMContentLoaded", function () {
  // 検索ボタン押下
  document
    .getElementById("search_client")
    .addEventListener("click", function () {
      document.getElementById("result").textContent = "検索中...";
      document.getElementById("search_table").innerHTML = "";
      const search_word = document.getElementById("word").value;

      fetch("/client/search", {
        method: "POST",
        headers: {
          "X-CSRF-TOKEN": document
            .querySelector('meta[name="csrf-token"]')
            .getAttribute("content"),
          "Content-Type": "application/json",
        },
        body: JSON.stringify({ search_word: search_word }),
      })
        .then((response) => response.json())
        .then((json) => {
          if (json.data.length === 0) {
            document.getElementById("result").textContent =
              "該当する企業はありませんでした。";
          } else {
            let count = 0;
            json.data.forEach((data) => {
              count += 1;
              let input_selector = `<input type='radio' name='client_id' value='${escapeHtml(data.client_id)}@${escapeHtml(data.company)}' id='${escapeHtml(data.client_id)}'/>`;
              let row = document.createElement("tr");
              row.innerHTML = `
              <td>${input_selector}</td>
              <td><label for='${escapeHtml(data.id)}'>${escapeHtml(data.company)}</label></td>
              <td><label for='${escapeHtml(data.id)}'>${escapeHtml(data.url)}</label></td>
              <td><label for='${escapeHtml(data.id)}'>${escapeHtml(data.tel)}</label></td>
              <td><label for='${escapeHtml(data.id)}'>${escapeHtml(data.street_address)}</label></td>
            `;
              document.getElementById("search_table").appendChild(row);
            });
            document.getElementById(
              "result"
            ).textContent = `${count}件の企業が見つかりました。`;
          }
        })
        .catch((error) => {
          document.getElementById("result").textContent =
            "エラーが発生しました: " + error.message;
        });
    });

  document
    .getElementById("client_select_btn")
    .addEventListener("click", function () {
      // 選択ボタン押下
      let id_company = document.querySelector(
        'input[name="client_id"]:checked'
      ).value;
      let split_list = id_company.split("@");
      // すべてのclass="search_result"を取得し、そのすべてのvalueにsplit_list[1]を代入
      document.querySelectorAll(".search_result").forEach((element) => {
        element.textContent  = split_list[1];
      });

      document.querySelectorAll(".client").forEach((element) => {
        element.value = split_list[0];
      });

      fetch("/search/personnel", {
        method: "POST",
        headers: {
          "X-CSRF-TOKEN": document
            .querySelector('meta[name="csrf-token"]')
            .getAttribute("content"),
          "Content-Type": "application/json",
        },
        body: JSON.stringify({ personnel_id: split_list[0] }),
      })
        .then((response) => response.json())
        .then((datas) => {
          if (datas.success == 0) {
            document.querySelectorAll(".select_personnel").forEach((element) => {
              element.textContent = "登録されている担当者がいません、CRMに登録してください";
            });
            // すべてのclass="personnel_selector"を取得し、そのすべてを削除
              document.querySelectorAll(".personnel_selector").forEach((element) => {
                element.remove();
              });
          } else {
            let count = 0;
            document.querySelectorAll(".personnel_selector").forEach((element) => {
              element.remove();
            });
            let select = document.createElement("select");
            // select.id = "personnel_selector";
            select.name = "personnel";
            select.className = "personnel_selector form-control";
            // document.querySelector(".select_personnel").after(select);
            datas.data.forEach((data) => {
              count += 1;
              let option = document.createElement("option");
              option.value = data.personnel_id;
              option.textContent = `${data.name} : ${data.email}`;
              select.appendChild(option);
            });

            document.querySelectorAll(".select_personnel").forEach((element) => {
              element.after(select.cloneNode(true));
            });

            document.querySelectorAll(".select_personnel").forEach((element) => {
              element.textContent = `登録されている担当者が${count}名います`;
            }); 
          }
        })
        .catch((error) => {
          alert(error.message);
        });
      // モーダルを閉じる
      const modalElement = document.getElementById("ClientSearchModal");
      const modal =
        bootstrap.Modal.getInstance(modalElement) ||
        new bootstrap.Modal(modalElement);
      modal.hide();

      // オーバーレイ要素を削除
      document.querySelectorAll(".modal-backdrop").forEach((el) => el.remove());
    });
});
