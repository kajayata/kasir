/* ============================================
   TAMBAHAN (di luar kode PDF):
   1. Enter = scan barcode / ambil hasil pertama
   2. Nominal cepat (Saran no. 5)
   3. Muat kembali transaksi yang ditahan
============================================ */

function setPayment(value) {
    document.getElementById("payment").value = value;
    calculateChange();
}

function renderQuickCash() {
    const box = document.getElementById("quickCash");
    if (!box) return;

    const total = getGrandTotal();
    const suggestions = [];

    if (total > 0) {
        const rounded = Math.ceil(total / 10000) * 10000;
        suggestions.push(rounded, 50000, 100000, 0);
    }

    box.innerHTML = suggestions
        .filter((value, i, arr) => arr.indexOf(value) === i)
        .map(
            (value) => `
            <button onclick="setPayment(${value === 0 ? "getGrandTotal()" : value});">
                ${value === 0 ? "Uang Pas" : formatRupiah(value)}
            </button>
        `,
        )
        .join("");
}

/* Enter = barcode scanner (menembak Enter setelah mengetik kode) */
searchInput.addEventListener("keydown", function (event) {
    if (event.key === "Enter") {
        event.preventDefault();

        if (products.length === 0) return;

        addToCart(products[0].id);
        searchInput.value = "";
        document.getElementById("productResults").style.display = "none";
    }
});

/* Lanjutkan transaksi yang ditahan */
if (window.KASIR.resumeId) {
    fetch(`${window.KASIR.transactionUrl}/${window.KASIR.resumeId}`, {
        headers: { Accept: "application/json" },
    })
        .then((response) => (response.ok ? response.json() : null))
        .then((data) => {
            if (!data) return;

            cart = data.items;
            renderCart();
            alert("Transaksi #" + data.id + " dimuat kembali. Silakan lanjutkan pembayaran.");
        });
}

renderQuickCash();
