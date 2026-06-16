/**
 * HTMLエスケープ処理
 */
function escapeHtml(str: string | null | undefined): string {
  if (str == null) return '';
  return String(str)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');
}

type DeviceInfo = {
  id: string;
  type: string;
  name: string;
};

type Cart =
  | {
      [id: string]: DeviceInfo;
    }
  | null
  | undefined;

const NowCart: Cart = {};

/**
 * カートのバッチの数字を更新する
 * @param {number} num カートの商品数
 * @returns {void}
 */
const updateBatch = (num: Number) => {
  // idが`dm-cart`のバッチの数字を変更する
  const cartNum = document.getElementById("dm-cart");
  if (cartNum) {
    cartNum.textContent = num.toString();
  }
};

/**
 * カートに商品を追加する
 * @param {string} productId 商品ID
 * @returns {void}
 */
const addToCart = (deviceInfo: DeviceInfo) => {
  saveCartToSessionStorage(deviceInfo);
  NowCart[deviceInfo["id"]] = deviceInfo;
  updateBatch(Object.keys(NowCart).length);
};

/**
 * カートから商品を削除する
 * @param {string} deviceId 商品ID
 * @returns {void}
 */
const removeFromCart = (deviceInfo: DeviceInfo) => {
  if (NowCart[deviceInfo["id"]]) {
    delete NowCart[deviceInfo["id"]];
    deleteCartToSessionStorage(deviceInfo["id"]);
    updateBatch(Object.keys(NowCart).length);
  }
};

/**
 * カートをセッションストレージに保存する
 * @param deviceInfo DeviceInfo
 */
const saveCartToSessionStorage = (deviceInfo: DeviceInfo) => {
  const cart = sessionStorage.getItem("OpenStockManagerCart");
  if (cart) {
    const cartObj = JSON.parse(cart);
    // オブジェクトから特定の値を削除
    cartObj[deviceInfo["id"]] = deviceInfo;
    sessionStorage.setItem("OpenStockManagerCart", JSON.stringify(cartObj));
  } else {
    // セッションストレージにカートがない場合
    const cartObj = { [deviceInfo["id"]]: deviceInfo };
    sessionStorage.setItem("OpenStockManagerCart", JSON.stringify(cartObj));
  }
};

/**
 * セッションストレージからカートを削除する
 * @param deviceId string
 */
const deleteCartToSessionStorage = (deviceId: string) => {
  const cart = sessionStorage.getItem("OpenStockManagerCart");
  if (cart) {
    // JSON文字列をJavaScriptオブジェクトに変換
    const cartObj = JSON.parse(cart);
    // オブジェクトから特定の値を削除
    delete cartObj[deviceId];
    sessionStorage.setItem("OpenStockManagerCart", JSON.stringify(cartObj));
  }
};

/**
 * セッションストレージからカートをロードする
 * @return Array
 * @throws Error
 */
const loadCartFromSessionStorage = () => {
  try {
    const loadCart = sessionStorage.getItem("OpenStockManagerCart");
    if (loadCart) {
      return JSON.parse(loadCart);
    } else {
      sessionStorage.setItem("OpenStockManagerCart", JSON.stringify({}));
    }
  } catch (error) {
    console.error("Error loading cart from session storage:", error);
  }
  return [];
};

/**
 * カート用モーダルの初期化
 * @returns {void}
 */
const initializeCartModal = () => {
  // NOTE: カートボタンがクリックされたとき
  document
    .getElementById("dm-cart-btn")
    ?.addEventListener("click", function () {
      const load = sessionStorage.getItem("OpenStockManagerCart");
      const loadCart = JSON.parse(load || "{}");
      if (Object.keys(loadCart).length > 0) {
        const table = document.getElementById("in-cart-devices") as HTMLElement;
        table.innerHTML = ""; // テーブルの中身を空にする
        Object.entries(loadCart).forEach(([id, object]) => {
          const item = object as DeviceInfo;
          const table_tr = document.createElement("tr");
          table_tr.innerHTML = `
                    <td><input type="checkbox" class="product-checkbox-cart" device-name="${escapeHtml(item.name)}" device-type="${escapeHtml(item.type)}" value="${escapeHtml(item.id)}" checked></td>
                    <td><a href="/devices/${encodeURIComponent(item.id)}">${escapeHtml(item.id)}</a></td>
                    <td>${escapeHtml(item.name)}</td>
                    <td>${escapeHtml(item.type)}</td>
                `;
          table.appendChild(table_tr);
        });
      }
    });

  // NOTE: チェックボタンを外すを押されたとき
  document
    .getElementById("all-remove-btn")
    ?.addEventListener("click", function () {
      const checkboxes = document.querySelectorAll(
        ".product-checkbox-cart"
      ) as NodeListOf<HTMLInputElement>;
      checkboxes.forEach((checkbox) => {
        checkbox.checked = false;
      });
    });

  // NOTE: チェックボタンをつけるを押されたとき
  document
    .getElementById("all-check-btn")
    ?.addEventListener("click", function () {
      const checkboxes = document.querySelectorAll(
        ".product-checkbox-cart"
      ) as NodeListOf<HTMLInputElement>;
      checkboxes.forEach((checkbox) => {
        checkbox.checked = true;
      });
    });

  // NOTE: モーダルの閉じるボタンが押されたとき
  document
    .getElementById("update-cart-btn")
    ?.addEventListener("click", function () {
      const checkboxes = document.querySelectorAll(
        ".product-checkbox-cart"
      ) as NodeListOf<HTMLInputElement>;
      checkboxes.forEach((checkbox) => {
        if (!checkbox.checked) {
          const deviceId = checkbox.value;
          const deviceName = checkbox.getAttribute("device-name");
          const deviceType = checkbox.getAttribute("device-type");
          if (deviceId && deviceType && deviceName) {
            removeFromCart({
              id: deviceId,
              type: deviceType,
              name: deviceName,
            });
          }
        }
      });
      // page reload
      location.reload();
    });
};

/**
 * 初回initialize
 * 全てのDOMが読み込まれた後に実行
 */
document.addEventListener("DOMContentLoaded", function () {
  // セッションストレージからカートをロード
  const load = sessionStorage.getItem("OpenStockManagerCart");
  const loadCart = JSON.parse(load || "{}");

  if (loadCart && typeof loadCart === "object") {
    // loadCartがオブジェクトであることを確認し、キーと値を反復処理
    Object.entries(loadCart).forEach(([id, object]) => {
      NowCart[id] = object as DeviceInfo;
    });
  } else {
    console.error("loadCart is not an object:", loadCart);
  }

  // idが`dm-cart`のバッチの数字を変更
  if (Object.keys(loadCart).length > 0) {
    updateBatch(Object.keys(loadCart).length);
  }

  // checkboxの初期化
  const checkboxes = document.querySelectorAll(
    ".product-checkbox"
  ) as NodeListOf<HTMLInputElement>;
  checkboxes.forEach((checkbox) => {
    const deviceId = checkbox.value;
    if (deviceId) {
      if (NowCart[deviceId]) {
        checkbox.checked = true;
      }
    }
  });

  // チェックボックスが変更されたときのリスナー
  checkboxes.forEach((checkbox) => {
    checkbox.addEventListener("change", function () {
      if (this.checked) {
        const deviceId = this.value;
        const deviceType = this.getAttribute("device-type");
        const deviceName = this.getAttribute("device-name");
        if (deviceId && deviceType && deviceName) {
          addToCart({ id: deviceId, type: deviceType, name: deviceName });
        }else {
          alert("Undefined Device id, Device Type, Device Name");
        }
      } else {
        const deviceId = this.value;
        const deviceType = this.getAttribute("device-type");
        const deviceName = this.getAttribute("device-name");
        if (deviceId && deviceType && deviceName) {
          removeFromCart({ id: deviceId, type: deviceType, name: deviceName });
        }
      }
    });
  });

  initializeCartModal();
});

// deviceIdにカートに入っているデバイスのIDを代入
const setForm = (deviceId: string) => {
  const sample = sessionStorage.getItem("OpenStockManagerCart");
  const List = JSON.parse(sample || "{}");
  const deviceInput = document.getElementById(
    "device-list"
  ) as HTMLInputElement;
  Object.entries(List).forEach(([id, item]) => {
    const device = item as DeviceInfo;
    deviceInput.innerHTML += `<input type="hidden" name="deviceIds[]" value="${escapeHtml(device.id)}">`;
  });
};
