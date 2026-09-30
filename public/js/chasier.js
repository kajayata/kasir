// Data produk diambil dari server (KasirController@search), bukan hardcode.
let products = [];

/* ============================================
   1. SEARCH PRODUCT
============================================ */
const searchInput = document.getElementById("searchProduct");

searchInput.addEventListener("input", searchProduct);

let searchSeq = 0;

async function searchProduct() {
    const keyword = searchInput.value.toLowerCase().trim();
    const resultBox = document.getElementById("productResults");

    if (!keyword) {
        resultBox.style.display = "none";
        return;
    }

    const seq = ++searchSeq;
    const response = await fetch(`${window.KASIR.searchUrl}?q=${encodeURIComponent(keyword)}`, {
        headers: { Accept: "application/json" },
    });

    if (seq !== searchSeq) return; // ada ketikan lebih baru, abaikan respons ini

    const results = await response.json();
    products = results;

    if (results.length === 0) {
        resultBox.innerHTML = `
            <div class="product-item">
                Barang tidak ditemukan
            </div>
        `;
    } else {
        resultBox.innerHTML = results
            .map(
                (product) => `
                <div class="product-item" onclick="addToCart(${product.id})">
                    <div>
                        <div class="product-name">${product.name}</div>
                        <div class="product-code">${product.code} - ${product.barcode}</div>
                    </div>
                    <div class="product-price">${formatRupiah(product.price)}</div>
                </div>
            `,
            ).join("");
    }

    resultBox.style.display = "block";
}

/* ============================================
   2. FORMAT RUPIAH
============================================ */
function formatRupiah(value) {
    return new Intl.NumberFormat(
        "id-ID",
        {
            style: "currency",
            currency: "IDR",
            maximumFractionDigits: 0
        }
    ).format(value);
}

/* ============================================
   CART
============================================ */
let cart = [];

/* ============================================
   ADD CART
============================================ */
function addToCart(productId) {
    const product = products.find((p) => p.id === productId);
    const existing = cart.find((item) => item.id === productId);

    if (existing) {
        existing.qty++;
    } else {
        cart.push({
            ...product,
            qty: 1,
        });
    }

    searchInput.value = "";
    document.getElementById("productResults").style.display = "none";
    renderCart();
}

/* ============================================
   RENDER CART
============================================ */
function renderCart() {
    const body = document.getElementById("cartBody");

    if (cart.length === 0) {
        body.innerHTML = `
            <tr>
                <td colspan="6" class="empty-cart">
                    Keranjang masih kosong.
                    <br>
                    Silakan cari atau scan barang.
                </td>
            </tr>
        `;
        calculateTotal();
        return;
    }

    body.innerHTML = cart.map(
        (item, index) => `
        <tr>
            <td>${index + 1}</td>
            <td>
                <strong>${item.name}</strong>
                <div style="font-size:11px; color:#6c757d">
                    ${item.code}
                </div>
            </td>
            <td>${formatRupiah(item.price)}</td>
            <td>
                <div class="qty-control">
                    <button onclick="changeQty(${item.id}, -1)">−</button>
                    <input type="number" value="${item.qty}" min="1" onchange="updateQty(${item.id}, this.value)">
                    <button onclick="changeQty(${item.id}, 1)">+</button>
                </div>
            </td>
            <td class="text-right">
                <strong>${formatRupiah(item.price * item.qty)}</strong>
            </td>
            <td class="text-center">
                <button class="remove-btn" onclick="removeItem(${item.id})">×</button>
            </td>
        </tr>
    `,
    ).join("");

    calculateTotal();
}

/* ============================================
   CHANGE QTY
============================================ */
function changeQty(id, amount) {
    const item = cart.find((item) => item.id === id);
    if (!item) return;

    item.qty += amount;

    if (item.qty <= 0) {
        removeItem(id);
        return;
    }

    renderCart();
}

/* ============================================
   UPDATE QTY
============================================ */
function updateQty(id, qty) {
    const item = cart.find((item) => item.id === id);
    if (!item) return;

    item.qty = Math.max(1, parseInt(qty) || 1);
    renderCart();
}

/* ============================================
   REMOVE ITEM
============================================ */
function removeItem(id) {
    cart = cart.filter((item) => item.id !== id);
    renderCart();
}

/* ============================================
   CALCULATE TOTAL
============================================ */
function calculateTotal() {
    let totalQty = 0;
    let subtotal = 0;

    cart.forEach((item) => {
        totalQty += item.qty;
        subtotal += item.price * item.qty;
    });

    const discountPercent = parseFloat(document.getElementById("discountPercent").value) || 0;
    const manualDiscount = parseFloat(document.getElementById("discountAmount").value) || 0;
    const tax = parseFloat(document.getElementById("tax").value) || 0;
    const otherFee = parseFloat(document.getElementById("otherFee").value) || 0;

    const percentDiscount = subtotal * (discountPercent / 100);
    const totalDiscount = percentDiscount + manualDiscount;
    const grandTotal = Math.max(0, subtotal - totalDiscount + tax + otherFee);

    document.getElementById("totalQty").innerText = totalQty;
    document.getElementById("itemCount").innerText = totalQty + " Item";
    document.getElementById("subtotal").innerText = formatRupiah(subtotal);
    document.getElementById("grandTotal").innerText = formatRupiah(grandTotal);

    calculateChange();
}

/* ============================================
   CALCULATE CHANGE
============================================ */
function calculateChange() {
    const grandTotal = getGrandTotal();
    const payment = parseFloat(document.getElementById("payment").value) || 0;

    const change = payment - grandTotal;

    const changeBox = document.getElementById("changeBox");
    const changeElement = document.getElementById("change");

    if (change >= 0) {
        changeBox.classList.remove("short-payment");
        changeElement.innerText = formatRupiah(change);
        document.querySelector(".change-label").innerText = "KEMBALIAN";
    } else {
        changeBox.classList.add("short-payment");
        changeElement.innerText = formatRupiah(Math.abs(change));
        document.querySelector(".change-label").innerText = "UANG KURANG";
    }
}

/* ============================================
   GET GRAND TOTAL
============================================ */
function getGrandTotal() {
    let subtotal = 0;

    cart.forEach((item) => {
        subtotal += item.price * item.qty;
    });

    const discountPercent = parseFloat(document.getElementById("discountPercent").value) || 0;
    const discountAmount = parseFloat(document.getElementById("discountAmount").value) || 0;
    const tax = parseFloat(document.getElementById("tax").value) || 0;
    const otherFee = parseFloat(document.getElementById("otherFee").value) || 0;

    const discount = (subtotal * discountPercent) / 100 + discountAmount;

    return Math.max(0, subtotal - discount + tax + otherFee);
}

/* ============================================
   PAYMENT METHOD
============================================ */
function selectPayment(button) {
    document.querySelectorAll(".payment-method button").forEach((btn) => {
        btn.classList.remove("active");
    });

    button.classList.add("active");
}

/* ============================================
   TRANSAKSI API
============================================ */
let heldId = window.KASIR.resumeId;

function transaksiPayload() {
    const num = (id) => parseInt(document.getElementById(id).value, 10) || 0;

    return {
        items: cart.map((item) => ({ id: item.id, qty: item.qty })),
        discount_percent: num("discountPercent"),
        discount_amount: num("discountAmount"),
        tax: num("tax"),
        other_fee: num("otherFee"),
        paid_amount: num("payment"),
        payment_method: document.querySelector(".payment-method button.active").textContent.trim(),
        customer_id: document.getElementById("customer").value || null,
    };
}

async function postTransaksi(url, body) {
    const response = await fetch(url, {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "Accept": "application/json",
            "X-CSRF-TOKEN": window.KASIR.csrf,
        },
        body: JSON.stringify(body),
    });

    if (response.ok) return response.json();

    const payload = await response.json().catch(() => ({}));
    const first = Object.values(payload.errors || {}).flat()[0];
    throw new Error(first || "Transaksi gagal disimpan.");
}

function resetForm() {
    cart = [];
    heldId = null;

    ["payment", "discountPercent", "discountAmount", "tax", "otherFee"].forEach((id) => {
        document.getElementById(id).value = id === "payment" ? "" : 0;
    });
    document.getElementById("customer").selectedIndex = 0;
    document.getElementById("customerPhone").value = "";

    const methods = document.querySelectorAll(".payment-method button");
    methods.forEach((btn, i) => btn.classList.toggle("active", i === 0));

    renderCart();
}

/* ============================================
   HOLD TRANSACTION
============================================ */
async function holdTransaction() {
    if (cart.length === 0) {
        alert("Tidak ada transaksi untuk ditahan.");
        return;
    }

    try {
        const data = await postTransaksi(window.KASIR.holdUrl, transaksiPayload());
        heldId = data.id;
        resetForm();
        alert("Transaksi berhasil ditahan.\nID: " + heldId);
    } catch (error) {
        alert(error.message);
    }
}

/* ============================================
   PAYMENT
============================================ */
async function processPayment() {
    if (cart.length === 0) {
        alert("Keranjang masih kosong.");
        return;
    }

    const total = getGrandTotal();
    const payment = parseFloat(document.getElementById("payment").value) || 0;

    if (payment < total) {
        alert("Uang pembayaran masih kurang.");
        return;
    }

    const url = heldId
        ? `${window.KASIR.transactionUrl}/${heldId}/resume`
        : window.KASIR.storeUrl;

    try {
        const data = await postTransaksi(url, transaksiPayload());
        resetForm();
        window.location.href = data.struk_url;
    } catch (error) {
        alert(error.message);
    }
}

/* ============================================
   CANCEL TRANSACTION
============================================ */
async function cancelTransaction() {
    if (!confirm("Batalkan transaksi ini?")) {
        return;
    }

    if (heldId) {
        try {
            await postTransaksi(`${window.KASIR.transactionUrl}/${heldId}/cancel`, {});
        } catch (error) {
            alert(error.message);
            return;
        }
    }

    resetForm();
}

/* ============================================
   DATE
============================================ */
document.getElementById("currentDate").innerText = new Date().toLocaleString(
    "id-ID",
);

/* ============================================
   KEYBOARD SHORTCUT
============================================ */
document.addEventListener("keydown", function (event) {

    // F2 = fokus pencarian
    if (event.key === "F2") {
        event.preventDefault();
        searchInput.focus();
    }

    // F4 = fokus pembayaran
    if (event.key === "F4") {
        event.preventDefault();
        document.getElementById("payment").focus();
    }

    // ESC = batal
    if (event.key === "Escape" && (cart.length > 0 || heldId)) {
        cancelTransaction();
    }
});

/* ============================================
   INITIAL
============================================ */
renderCart();
