<p>現在カートに入っているデバイスは以下です</p>
<ol id="CartList"></ol>
<script>
    // カートに入っているデバイスを表示
    const html = document.getElementById('CartList');
    const db = sessionStorage.getItem("DeviceManagerCart");
    const CartList = JSON.parse(db || "{}");
    if (Object.keys(CartList).length > 0) {
        Object.entries(CartList).forEach(([id, item]) => {
            const url = "/devices/" + item.id;
            html.innerHTML +=
                `<li>${item.id} / ${item.type} / ${item.name} / <a class="far fa-arrow-alt-circle-right" href="${url}"></a></li>`;
        });
    } else {
        html.innerHTML = "<p>No device in cart...</p>";
    }
</script>
