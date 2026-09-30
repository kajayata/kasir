# PROSEDUR KERJA PRAKTIKUM KE-3 - MEMBUAT HALAMAN TRANSAKSI PEMBAYARAN KASIR

**Program Studi :**
1. Teknik Informatika
2. Teknologi Rekayasa Perangkat Lunak

**Topik Praktikum :** Membedah fitur-fitur yang ada pada halaman kasir

---

## 0. Instruksi untuk AI Coding Assistant (Vibe Coding)

Dokumen ini adalah spesifikasi lengkap halaman kasir. Ikuti aturan berikut:

1. **Salin kode di dokumen ini persis apa adanya.** Jangan mengubah nama fungsi, id, class, atau urutan struktur.
2. **CSS sudah disediakan lengkap** di bagian **"CSS — `chasier.css`"** dokumen ini (disalin dari `kasir.html`). Simpan apa adanya sebagai `public/css/chasier.css`. Jangan menulis ulang, menyingkat, atau mengubah CSS, lalu pasang `<link>` ke file tersebut.
3. **Jangan memperbaiki bug** yang tercantum di bagian [Catatan Bug & Keterbatasan](#catatan-bug--keterbatasan-kode-pdf) kecuali diminta secara eksplisit. Bagian itu hanya catatan.
4. Nama file `chasier.css` / `chasier.js` (bukan `cashier`) memang ejaan dari sumber. Pertahankan.
5. Target: **Blade Laravel 13 + Bootstrap** (desain mudah dikonversi dari HTML + CSS + JavaScript standalone).
6. Kerjakan berurutan sesuai nomor bagian (1 → 4 untuk HTML, lalu JavaScript a → f).

### Struktur file yang dihasilkan

```
resources/views/<halaman-kasir>.blade.php   ← HTML (bagian 1–4)
public/css/chasier.css                      ← salin dari bagian "CSS — chasier.css"
public/js/chasier.js                        ← seluruh JavaScript (bagian JavaScript a–f)
public/css/struk.css                        ← (opsional) CSS halaman struk, lihat Lampiran A
logo-toko.png                               ← (opsional) logo untuk struk, lihat Lampiran A
```

---

## Ringkasan

Berikut HTML + CSS + JavaScript standalone yang bisa langsung dicoba di browser. Desainnya juga cukup mudah nantinya diubah menjadi Blade Laravel 13 + Bootstrap.

### Gambaran layout

Struktur halaman tersebut kira-kira:

```
┌─────────────────────────────────────────────────────────────────────┐
│ TOKO RETAIL MAKMUR                              TRX-20260911-001    │
│ Jl. Contoh No. 123                              11/09/2026 15:12    │
├───────────────────────────────────────┬─────────────────────────────┤
│                                       │ RINGKASAN PEMBAYARAN        │
│ Tambah Barang                         │                             │
│ ┌───────────────────────────┐ [Cari]  │ Total Item            5     │
│ │ cari / barcode            │         │ Subtotal        Rp 75.000   │
│ └───────────────────────────┘         │ Diskon (%)             0%   │
│                                       │ Diskon (Rp)          Rp 0   │
│ Pelanggan          No Member/HP       │ Pajak                Rp 0   │
│ ┌──────────────┐ ┌───────────────┐    │ Biaya Lain           Rp 0   │
│ │ Umum         │ │               │    │                             │
│ └──────────────┘ └───────────────┘    │ TOTAL AKHIR                 │
│                                       │ Rp 75.000                   │
├───────────────────────────────────────┤                             │
│ KERANJANG BELANJA                     │ UANG DIBAYAR                │
│                                       │ ┌─────────────────────────┐ │
│ No  Barang     Harga   Qty  Subtotal  │ │ Rp 100.000              │ │
│ 1   Indomie    3.500    2    7.000    │ └─────────────────────────┘ │
│ 2   Aqua       4.000    2    8.000    │                             │
│ 3   Beras     75.000    1   75.000    │ [Tunai][QRIS][Debit]        │
│                                       │                             │
│                                       │ KEMBALIAN                   │
│                                       │ Rp 25.000                   │
│                                       │                             │
│                                       │ [Tahan] [Batal]             │
│                                       │ [ BAYAR & CETAK ]           │
└───────────────────────────────────────┴─────────────────────────────┘
```

### Tampilan Halaman Kasir (hasil akhir)

Referensi visual dari PDF (halaman 2):

- **Header:** nama toko "TOKO RETAIL MAKMUR", alamat di bawahnya; di kanan: "No. Transaksi", nomor transaksi tebal, tanggal-waktu.
- **Kolom kiri:** card "Tambah Barang" (input cari + tombol biru "Cari", select "Pelanggan", input "No. Member / HP"), lalu card "Keranjang Belanja" dengan penghitung "0 Item" dan pesan kosong.
- **Kolom kanan:** card "Ringkasan Pembayaran" (Total Item, Subtotal, 4 input angka, kotak TOTAL AKHIR), kotak biru "Uang Dibayar" dengan input besar rata kanan, 6 tombol "Metode Pembayaran" (2 baris × 3, "Tunai" aktif berwarna biru), kotak hijau "KEMBALIAN", tombol kuning "Tahan", merah "Batal", dan hijau lebar "BAYAR & CETAK".
- **Footer gelap (terlihat di screenshot):** kiri "Kasir: Admin", kanan "F2 Cari Barang • F4 Bayar • ESC Batal".

### Fitur yang saya masukkan

- Struktur HTML awal dan header
- Pencarian barang berdasarkan nama/kode/barcode
- Daftar barang sementara
- Ringkasan pembayaran
- Qty +/-
- Hapus barang
- Subtotal
- Diskon nominal
- Diskon persen
- Pajak
- Biaya tambahan
- Total akhir
- Input uang bayar
- Perhitungan kembalian otomatis
- Status uang pembayaran: kurang/cukup
- Pilihan metode pembayaran
- Nomor transaksi
- Kasir
- Pelanggan
- Tombol **Bayar & Cetak**
- Tombol **Tahan Transaksi**
- Tombol **Batal Transaksi**
- Shortcut keyboard sederhana
- Tampilan responsif

---

## 1. Struktur HTML awal dan Header

```html
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kasir Retail POS</title>
    <link rel="stylesheet" href="{{ url(asset('css/chasier.css')) }}">
</head>
<body>
<div class="pos-container">
    <!-- HEADER -->
    <header class="header">
        <div>
            <div class="store-name">TOKO RETAIL MAKMUR</div>
            <div class="store-info">
                Jl. Contoh No. 123 • Jember • Telp. 0812-xxxx-xxxx
            </div>
        </div>
        <div class="transaction-info">
            <div>No. Transaksi</div>
            <div class="transaction-number" id="transactionNumber">
                <!-- Kode otomatis sesuai tanggal -->
                TRX-20260911-001
            </div>
            <div id="currentDate"></div>
        </div>
    </header>
</div>
</body>
</html>
```

**Tampilan setelah langkah ini:** hanya header (nama toko + info di kiri, No. Transaksi di kanan) di atas area abu-abu kosong.

Pada bagian link css silakan disesuaikan di mana lokasi file css berada, atau bisa download di repositori: **djuliar/minimarket**.

```html
<link rel="stylesheet" href="{{ url(asset('css/chasier.css')) }}">
```

Untuk aplikasi **kasir toko retail**, halaman pembayaran dibuat dengan konsep **POS (Point of Sale)**: area kiri untuk pencarian/pemilihan barang dan daftar belanja, area kanan untuk ringkasan transaksi dan pembayaran.

```html
<!-- MAIN -->
<main class="main">
    <!-- ================= LEFT ================= -->
    <section>
    </section>

    <!-- ================= RIGHT ================= -->
    <aside>
    </aside>
</main>
```

---

## 2. Pencarian barang berdasarkan nama/kode/barcode

Pada bagian `<section>` tambahkan `<card>` berikut:

```html
<!-- SEARCH -->
<div class="card">
    <div class="card-header">Tambah Barang</div>
    <div class="card-body">
        <div class="search-area">
            <div class="search-input">
                <input type="text" id="searchProduct" placeholder="Cari nama barang / kode / barcode..." autocomplete="off">
                <div class="product-results" id="productResults"></div>
            </div>

            <button class="btn btn-primary" onclick="searchProduct()">Cari</button>
        </div>

        <!-- CUSTOMER -->
        <div class="customer-area">
            <div class="form-group">
                <label>Pelanggan</label>

                <select class="form-control">
                    <option>Umum</option>
                    <option>Pelanggan Member</option>
                    <option>Member VIP</option>
                </select>
            </div>

            <div class="form-group">
                <label>No. Member / HP</label>
                <input type="text" class="form-control" placeholder="Opsional">
            </div>
        </div>
    </div>
</div>
```

**Tampilan setelah langkah ini:** card "Tambah Barang" di kolom kiri, berisi input pencarian + tombol "Cari", lalu select "Pelanggan" dan input "No. Member / HP" berdampingan.

---

## 3. Daftar barang sementara

Tambahkan di dalam `<section>`, di bawah card "Tambah Barang":

```html
<!-- CART -->
<div class="card" style="margin-top:15px">
    <div class="card-header">
        <div style="display:flex; justify-content:space-between; align-items:center;">
            <span>Keranjang Belanja</span>
            <span id="itemCount">0 Item</span>
        </div>
    </div>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th width="40">No</th>
                    <th>Barang</th>
                    <th width="100">Harga</th>
                    <th width="120" class="text-center">Qty</th>
                    <th width="120" class="text-right">Subtotal</th>
                    <th width="40"></th>
                </tr>
            </thead>

            <tbody id="cartBody">
                <tr id="emptyRow">
                    <td colspan="6" class="empty-cart">
                        Keranjang masih kosong.
                        <br>
                        Silakan cari atau scan barang.
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
```

**Tampilan setelah langkah ini:** di bawah card "Tambah Barang" muncul card "Keranjang Belanja" (header "0 Item" di kanan) dengan tabel kolom No, Barang, Harga, Qty, Subtotal, dan pesan "Keranjang masih kosong. Silakan cari atau scan barang."

---

## 4. Ringkasan pembayaran

Tambahkan di dalam `<aside>`:

```html
<div class="card">
    <div class="card-header">Ringkasan Pembayaran</div>

    <div class="card-body">
        <div class="payment-summary">

            <!-- TOTAL ITEM -->
            <div class="summary-row">
                <span>Total Item</span>
                <strong id="totalQty">0</strong>
            </div>

            <!-- SUBTOTAL -->
            <div class="summary-row">
                <span>Subtotal</span>
                <strong id="subtotal">Rp 0</strong>
            </div>

            <!-- DISCOUNT -->
            <div class="summary-row">
                <span>Diskon (%)</span>
                <input type="number" id="discountPercent" value="0" min="0" max="100" onchange="calculateTotal()">
            </div>

            <div class="summary-row">
                <span>Diskon (Rp)</span>
                <input type="number" id="discountAmount" value="0" min="0" onchange="calculateTotal()">
            </div>

            <!-- TAX -->
            <div class="summary-row">
                <span>Pajak / PPN</span>
                <input type="number" id="tax" value="0" min="0" onchange="calculateTotal()">
            </div>

            <!-- OTHER FEE -->
            <div class="summary-row">
                <span>Biaya Lain</span>
                <input type="number" id="otherFee" value="0" min="0" onchange="calculateTotal()">
            </div>

            <!-- TOTAL -->
            <div class="total-box">
                <div class="total-label">TOTAL AKHIR</div>
                <div class="total-value" id="grandTotal">Rp 0</div>
            </div>
        </div>
    </div>
</div>
```

### 4a. Bagian pembayaran

Tambahkan ini **di bawah ringkasan pembayaran**, di dalam `div.card`, di dalam `div.payment-summary` (yaitu setelah `.total-box` dan sebelum penutup `</div>` milik `.payment-summary`).

```html
<!-- PAYMENT -->
<div class="payment-box">
    <div class="payment-label">Uang Dibayar</div>
    <input type="number" id="payment" class="payment-input" placeholder="0" oninput="calculateChange()">

    <!-- PAYMENT METHOD -->
    <div class="payment-label" style="margin-top:15px">Metode Pembayaran</div>

    <div class="payment-method">
        <button class="active" onclick="selectPayment(this)">Tunai</button>
        <button onclick="selectPayment(this)">QRIS</button>
        <button onclick="selectPayment(this)">Debit</button>
        <button onclick="selectPayment(this)">Kredit</button>
        <button onclick="selectPayment(this)">E-Wallet</button>
        <button onclick="selectPayment(this)">Transfer</button>
    </div>

    <!-- CHANGE -->
    <div class="change-box" id="changeBox">
        <div class="change-label">KEMBALIAN</div>
        <div class="change-value" id="change">Rp 0</div>
    </div>
</div>
```

### 4b. Bagian action pembayaran

Tambahkan ini **di bawah pembayaran** (setelah `.payment-box`), di dalam `div.card`, di dalam `div.payment-summary`.

```html
<!-- ACTION -->
<div class="action-area">
    <button class="btn btn-warning" onclick="holdTransaction()">Tahan</button>
    <button class="btn btn-danger" onclick="cancelTransaction()">Batal</button>
    <button class="btn btn-success btn-pay" onclick="processPayment()">BAYAR & CETAK</button>
</div>
```

**Tampilan akhir kolom kanan:** kotak TOTAL AKHIR, kotak biru "Uang Dibayar", tombol metode pembayaran (Tunai aktif), kotak hijau "KEMBALIAN", tombol Tahan (kuning) + Batal (merah), dan BAYAR & CETAK (hijau lebar).

---

## CSS — `chasier.css`

Simpan blok berikut **apa adanya** sebagai `public/css/chasier.css`. Semua class yang dipakai markup HTML di bagian 1–4 sudah tercakup di sini, termasuk `.footer` dan aturan responsif.

```css
* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}

body {
    font-family: Arial, Helvetica, sans-serif;
    background: #f4f6f9;
    color: #212529;
}

.pos-container {
    min-height: 100vh;
    display: flex;
    flex-direction: column;
}

/* ================= HEADER ================= */

.header {
    background: #ffffff;
    border-bottom: 1px solid #dee2e6;
    padding: 15px 25px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.store-name {
    font-size: 22px;
    font-weight: bold;
}

.store-info {
    color: #6c757d;
    font-size: 13px;
    margin-top: 4px;
}

.transaction-info {
    text-align: right;
    font-size: 13px;
}

.transaction-number {
    font-weight: bold;
    font-size: 16px;
}

/* ================= MAIN ================= */

.main {
    flex: 1;
    display: grid;
    grid-template-columns: minmax(0, 1fr) 420px;
    gap: 15px;
    padding: 15px;
}

.card {
    background: white;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    overflow: hidden;
}

.card-header {
    padding: 14px 16px;
    border-bottom: 1px solid #dee2e6;
    font-weight: bold;
    background: #fafafa;
}

.card-body {
    padding: 16px;
}

/* ================= SEARCH ================= */

.search-area {
    display: flex;
    gap: 10px;
    margin-bottom: 15px;
}

.search-input {
    flex: 1;
    position: relative;
}

.search-input input {
    width: 100%;
    height: 48px;
    padding: 0 15px;
    border: 1px solid #ced4da;
    border-radius: 6px;
    font-size: 16px;
    outline: none;
}

.search-input input:focus {
    border-color: #0d6efd;
}

.btn {
    border: none;
    border-radius: 6px;
    padding: 0 18px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 600;
}

.btn-primary {
    background: #0d6efd;
    color: white;
}

.btn-secondary {
    background: #6c757d;
    color: white;
}

.btn-danger {
    background: #dc3545;
    color: white;
}

.btn-success {
    background: #198754;
    color: white;
}

.btn-warning {
    background: #ffc107;
    color: #212529;
}

/* ================= PRODUCT RESULT ================= */

.product-results {
    display: none;
    position: absolute;
    top: 52px;
    left: 0;
    right: 0;
    background: white;
    border: 1px solid #dee2e6;
    border-radius: 6px;
    z-index: 100;
    box-shadow: 0 5px 15px rgba(0, 0, 0, .1);
}

.product-item {
    padding: 12px 15px;
    border-bottom: 1px solid #eee;
    cursor: pointer;
    display: flex;
    justify-content: space-between;
}

.product-item:hover {
    background: #f8f9fa;
}

.product-name {
    font-weight: 600;
}

.product-code {
    font-size: 12px;
    color: #6c757d;
}

.product-price {
    font-weight: bold;
}

/* ================= TABLE ================= */

.table-wrapper {
    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
}

th {
    background: #f8f9fa;
    font-size: 13px;
    text-align: left;
    padding: 11px 10px;
    border-bottom: 1px solid #dee2e6;
}

td {
    padding: 10px;
    border-bottom: 1px solid #eee;
    font-size: 14px;
}

.text-right {
    text-align: right;
}

.text-center {
    text-align: center;
}

.qty-control {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 5px;
}

.qty-control button {
    width: 27px;
    height: 27px;
    border: 1px solid #ced4da;
    background: #fff;
    border-radius: 4px;
    cursor: pointer;
}

.qty-control input {
    width: 45px;
    height: 27px;
    text-align: center;
    border: 1px solid #ced4da;
    border-radius: 4px;
}

.remove-btn {
    color: #dc3545;
    border: none;
    background: none;
    cursor: pointer;
    font-size: 18px;
}

.empty-cart {
    text-align: center;
    padding: 60px 20px;
    color: #6c757d;
}

/* ================= CUSTOMER ================= */

.customer-area {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
    margin-top: 15px;
}

.form-group {
    margin-bottom: 12px;
}

.form-group label {
    display: block;
    font-size: 12px;
    font-weight: bold;
    margin-bottom: 5px;
    color: #495057;
}

.form-control {
    width: 100%;
    height: 40px;
    padding: 0 10px;
    border: 1px solid #ced4da;
    border-radius: 5px;
    outline: none;
}

/* ================= PAYMENT ================= */

.payment-summary {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 14px;
}

.summary-row input {
    width: 150px;
    text-align: right;
    height: 36px;
    border: 1px solid #ced4da;
    border-radius: 5px;
    padding: 0 10px;
}

.summary-total {
    border-top: 2px solid #212529;
    margin-top: 8px;
    padding-top: 12px;
    font-size: 20px;
    font-weight: bold;
}

.total-box {
    background: #f8f9fa;
    border-radius: 7px;
    padding: 15px;
    margin: 15px 0;
}

.total-label {
    color: #6c757d;
    font-size: 13px;
}

.total-value {
    font-size: 30px;
    font-weight: bold;
    margin-top: 5px;
}

/* ================= PAYMENT INPUT ================= */

.payment-box {
    background: #eef6ff;
    border: 1px solid #b6d4fe;
    padding: 15px;
    border-radius: 7px;
}

.payment-label {
    font-weight: bold;
    font-size: 13px;
    margin-bottom: 6px;
}

.payment-input {
    width: 100%;
    height: 55px;
    font-size: 26px;
    font-weight: bold;
    text-align: right;
    padding: 0 12px;
    border: 2px solid #0d6efd;
    border-radius: 6px;
}

.change-box {
    margin-top: 12px;
    padding: 14px;
    border-radius: 6px;
    background: #d1e7dd;
    color: #0f5132;
}

.change-label {
    font-size: 12px;
}

.change-value {
    font-size: 24px;
    font-weight: bold;
}

.short-payment {
    background: #f8d7da;
    color: #842029;
}

/* ================= PAYMENT METHOD ================= */

.payment-method {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 7px;
    margin-top: 10px;
}

.payment-method button {
    padding: 10px 5px;
    border: 1px solid #ced4da;
    background: white;
    border-radius: 5px;
    cursor: pointer;
    font-size: 12px;
}

.payment-method button.active {
    background: #0d6efd;
    color: white;
    border-color: #0d6efd;
}

/* ================= ACTION ================= */

.action-area {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px;
    margin-top: 15px;
}

.action-area button {
    height: 48px;
}

.btn-pay {
    grid-column: span 2;
    height: 58px !important;
    font-size: 17px;
}

/* ================= FOOTER ================= */

.footer {
    background: #212529;
    color: #adb5bd;
    padding: 8px 20px;
    font-size: 12px;
    display: flex;
    justify-content: space-between;
}

/* ================= RESPONSIVE ================= */

@media (max-width: 1000px) {
    .main {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 600px) {
    .header {
        padding: 12px;
    }

    .transaction-info {
        display: none;
    }

    .main {
        padding: 8px;
    }

    .customer-area {
        grid-template-columns: 1fr;
    }
}
```

### Markup footer (memakai class `.footer`)

Class `.footer` di CSS di atas dipakai oleh footer gelap di bagian bawah halaman. Tempatkan **setelah `</main>`**, di dalam `.pos-container`:

```html
<!-- FOOTER -->

<footer class="footer">

    <span>
        Kasir: Admin
    </span>

    <span>
        F2 Cari Barang • F4 Bayar • ESC Batal
    </span>

</footer>
```

Urutan akhir di dalam `.pos-container`: `<header>` → `<main>` → `<footer>`.

---

## JavaScript

Semua kode JavaScript di bawah ini ditaruh di antara tag `<script></script>` (atau digabung dalam satu file `chasier.js`, lihat bagian [Penggabungan file JavaScript](#penggabungan-file-javascript)).

### a. Sample Product Data

Tambahkan berikut di antara tag `<script></script>` untuk menjalankan JavaScript.

```javascript
const products = [
    {
        id: 1,
        code: "BRG001",
        barcode: "899123456001",
        name: "Indomie Goreng",
        price: 3500
    },
    {
        id: 2,
        code: "BRG002",
        barcode: "899123456002",
        name: "Aqua 600ml",
        price: 4000
    },
    {
        id: 3,
        code: "BRG003",
        barcode: "899123456003",
        name: "Teh Botol Sosro",
        price: 5000
    },
    {
        id: 4,
        code: "BRG004",
        barcode: "899123456004",
        name: "Beras 5 Kg",
        price: 75000
    },
    {
        id: 5,
        code: "BRG005",
        barcode: "899123456005",
        name: "Minyak Goreng 1 Liter",
        price: 18000
    },
    {
        id: 6,
        code: "BRG006",
        barcode: "899123456006",
        name: "Gula Pasir 1 Kg",
        price: 17000
    }
];
```

### b. Fungsi cari barang dan memasukkan ke cart

```javascript
/* ============================================
   1. SEARCH PRODUCT
============================================ */
const searchInput = document.getElementById("searchProduct");

searchInput.addEventListener("input", searchProduct);

function searchProduct() {
    const keyword = searchInput.value.toLowerCase().trim();
    const resultBox = document.getElementById("productResults");

    if (!keyword) {
        resultBox.style.display = "none";
        return;
    }

    const results = products.filter(
        (product) =>
            product.name.toLowerCase().includes(keyword) ||
            product.code.toLowerCase().includes(keyword) ||
            product.barcode.includes(keyword),
    );

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
```

```javascript
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
```

```javascript
/* ============================================
   CART
============================================ */
let cart = [];
```

```javascript
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
```

```javascript
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
```

```javascript
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
```

```javascript
/* ============================================
   UPDATE QTY
============================================ */
function updateQty(id, qty) {
    const item = cart.find((item) => item.id === id);
    if (!item) return;

    item.qty = Math.max(1, parseInt(qty) || 1);
    renderCart();
}
```

```javascript
/* ============================================
   REMOVE ITEM
============================================ */
function removeItem(id) {
    cart = cart.filter((item) => item.id !== id);
    renderCart();
}
```

### c. Proses perhitungan

```javascript
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
```

```javascript
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
```

```javascript
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
```

### d. Fungsi untuk Payment Method

```javascript
/* ============================================
   PAYMENT METHOD
============================================ */
function selectPayment(button) {
    document.querySelectorAll(".payment-method button").forEach((btn) => {
        btn.classList.remove("active");
    });

    button.classList.add("active");
}
```

```javascript
/* ============================================
   HOLD TRANSACTION
============================================ */
function holdTransaction() {
    if (cart.length === 0) {
        alert("Tidak ada transaksi untuk ditahan.");
        return;
    }

    alert("Transaksi berhasil ditahan.");
}
```

```javascript
/* ============================================
   PAYMENT
============================================ */
function processPayment() {
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

    const change = payment - total;

    alert(
        "Pembayaran berhasil!\n\n" +
        "Total   : " + formatRupiah(total) + "\n" +
        "Bayar   : " + formatRupiah(payment) + "\n" +
        "Kembali : " + formatRupiah(change),
    );

    // Nantinya dapat diganti:
    // window.location.href =
    // "/transaksi/" + transactionId + "/print";
}
```

```javascript
/* ============================================
   CANCEL TRANSACTION
============================================ */
function cancelTransaction() {
    if (!confirm("Batalkan transaksi ini?")) {
        return;
    }

    cart = [];

    document.getElementById("payment").value = "";
    document.getElementById("discountPercent").value = 0;
    document.getElementById("discountAmount").value = 0;

    renderCart();
}
```

### e. Tanggal, shortcut keyboard, dan inisialisasi

```javascript
/* ============================================
   DATE
============================================ */
document.getElementById("currentDate").innerText = new Date().toLocaleString(
    "id-ID",
);
```

```javascript
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
    if (event.key === "Escape") {
        cancelTransaction();
    }
});
```

```javascript
/* ============================================
   INITIAL
============================================ */
renderCart();
```

### f. Penggabungan file JavaScript

Semua file JavaScript bisa dikelompokkan menjadi satu file di `chasier.js`, dan dipanggil seperti ini:

```html
<script src="{{ url(asset('js/chasier.js')) }}"></script>
```

File JavaScript atau bisa di-download di repositori: **djuliar/minimarket**.

**Urutan isi `chasier.js`:** `products` → Search Product → Format Rupiah → `cart` → Add Cart → Render Cart → Change Qty → Update Qty → Remove Item → Calculate Total → Calculate Change → Get Grand Total → Payment Method → Hold Transaction → Payment → Cancel Transaction → Date → Keyboard Shortcut → Initial.

---

## Saran untuk versi aplikasi Laravel 13

### 1. Tambahkan barcode scanner

Input pencarian sebaiknya juga berfungsi sebagai input barcode.

Alurnya:

```
Scanner Barcode
      ↓
Input Barcode
      ↓
Cari Produk
      ↓
Produk ditemukan
      ↓
Jika sudah ada → Qty + 1
Jika belum ada → Tambahkan ke keranjang
```

Dengan demikian kasir tidak perlu menggunakan mouse untuk setiap barang.

### 2. Pisahkan transaksi dan detail transaksi

Struktur database Laravel yang disarankan:

```
transactions
────────────────────────
id
transaction_number
user_id
customer_id
subtotal
discount
tax
other_fee
grand_total
paid_amount
change_amount
payment_method
status
created_at
```

```
transaction_details
────────────────────────
id
transaction_id
product_id
product_code
product_name
price
qty
discount
subtotal
created_at
```

Ini akan memudahkan pembuatan:

- laporan penjualan
- cetak struk
- laporan per kasir
- laporan per periode
- laporan produk terlaris
- laporan laba
- retur barang
- audit transaksi.

### 3. Jangan hanya menyimpan total

Pada aplikasi retail, sebaiknya detail transaksi disimpan lengkap, bukan hanya:

```
total = Rp100.000
```

Tetapi:

```
Transaksi #TRX001

Indomie   2 x 3.500  =  7.000
Aqua      2 x 4.000  =  8.000
Beras     1 x 75.000 = 75.000
────────────────────────────────
Subtotal                90.000
Diskon                   5.000
PPN                          0
────────────────────────────────
TOTAL                   85.000
Bayar                  100.000
Kembali                 15.000
```

### 4. Tambahkan fitur "Parkir/Tahan Transaksi"

Ini sangat berguna untuk kasir retail. Misalnya:

```
Pelanggan A
     ↓
Scan 10 barang
     ↓
Belum bayar
     ↓
Tahan transaksi
     ↓
Pelanggan B
     ↓
Layani pelanggan B
     ↓
Selesai
     ↓
Kembali ke transaksi A
```

Bisa dibuat tombol:

```
Tahan Transaksi
```

dan halaman:

```
Transaksi Ditahan

TRX-001   5 Item   Rp 125.000   [Lanjutkan]
TRX-002   8 Item   Rp 340.000   [Lanjutkan]
TRX-003   2 Item   Rp 25.000    [Lanjutkan]
```

### 5. Tambahkan "Nominal Cepat"

Untuk kasir toko, ini sangat praktis. Misalnya total:

```
Rp 37.500
```

Tampilkan tombol:

```
[ Rp 40.000 ]
[ Rp 50.000 ]
[ Rp 100.000 ]
[ Uang Pas ]
```

Kasir cukup klik:

```
Rp 50.000
```

```
Bayar    Rp 50.000
Total    Rp 37.500
-------------------
Kembali  Rp 12.500
```

### 6. Cetak struk

Setelah **BAYAR & CETAK**, alur Laravel dapat dibuat:

```
POST /kasir/transaksi
        ↓
Simpan transaksi
        ↓
Simpan detail transaksi
        ↓
Kurangi stok
        ↓
Simpan pembayaran
        ↓
Generate nomor transaksi
        ↓
Redirect ke halaman struk
        ↓
window.print()
```

Struk dapat dibuat untuk:

- printer thermal 58 mm
- printer thermal 80 mm
- PDF
- printer biasa.

### 7. Fitur yang direkomendasikan untuk tahap berikutnya

Untuk Aplikasi Minimarket Laravel 13, modul kasir akhirnya bisa memiliki:

```
KASIR
│
├── Transaksi Baru
│   ├── Scan Barcode
│   ├── Cari Produk
│   ├── Keranjang
│   ├── Diskon
│   ├── Pajak
│   ├── Pembayaran
│   └── Cetak Struk
│
├── Transaksi Ditahan
│
├── Riwayat Transaksi
│
├── Retur Penjualan
│
├── Pembatalan Transaksi
│
└── Tutup Kasir
    ├── Saldo Awal
    ├── Penjualan Tunai
    ├── QRIS
    ├── Debit
    ├── E-Wallet
    └── Saldo Akhir
```

---

## Lampiran A — CSS Halaman Struk (58 mm)

> Lampiran ini **tidak ada di PDF praktikum**. Ditambahkan dari file `struk.html` dan `logo-toko.png` yang diunggah, sebagai pelengkap Saran nomor 6 (Cetak struk). Bagian ini hanya berisi CSS. Markup HTML struk mengikuti `struk.html`.

**Ringkasan:**

- Lebar struk `58mm` (printer thermal 58 mm), font `Courier New` 11px.
- Logo toko memakai `<img src="logo-toko.png" class="store-logo" alt="Logo Toko">` (lebar 35mm, tinggi maks 18mm). File `logo-toko.png` berisi logo "MAKMUR JAYA".
- Tombol "Cetak" dan "Tutup" (`.receipt-actions`) disembunyikan saat mencetak lewat `@media print`.
- `@page { size: 58mm auto; margin: 0; }` dipakai agar panjang kertas mengikuti isi.

**Simpan sebagai file terpisah** (`public/css/struk.css` atau blok `<style>` di halaman struk). **Jangan digabung ke `chasier.css`**, karena beberapa nama class bentrok dengan gaya halaman kasir (`.store-name`, `.transaction-info`, `.summary-row`, `.summary-label`, `.item`, dan `body`).

```css
* {
    box-sizing: border-box;
}

body {
    margin: 0;
    padding: 30px;
    background: #f1f1f1;
    font-family: Arial, Helvetica, sans-serif;
}

/*
        |--------------------------------------------------------------------------
        | CONTAINER
        |--------------------------------------------------------------------------
        */

.receipt-container {
    width: 58mm;
    margin: 0 auto;
}

/*
        |--------------------------------------------------------------------------
        | RECEIPT
        |--------------------------------------------------------------------------
        */

.receipt {
    width: 58mm;
    min-height: 100mm;

    background: white;

    padding: 4mm;

    color: #000;

    font-family:
        "Courier New",
        Courier,
        monospace;

    font-size: 11px;

    line-height: 1.35;

    box-shadow:
        0 2px 10px rgba(0, 0, 0, 0.15);
}

/*
        |--------------------------------------------------------------------------
        | HEADER
        |--------------------------------------------------------------------------
        */

.store-header {
    text-align: center;
}

.store-logo {
    width: 35mm;
    max-height: 18mm;

    object-fit: contain;

    margin-bottom: 2mm;
}

.store-name {
    font-size: 16px;
    font-weight: bold;

    margin-bottom: 1mm;
}

.store-address {
    font-size: 10px;
}

.store-phone {
    font-size: 10px;

    margin-top: 1mm;
}

/*
        |--------------------------------------------------------------------------
        | SEPARATOR
        |--------------------------------------------------------------------------
        */

.separator {
    border-top:
        1px dashed #000;

    margin:
        3mm 0;
}

/*
        |--------------------------------------------------------------------------
        | TRANSACTION INFO
        |--------------------------------------------------------------------------
        */

.transaction-info {
    width: 100%;
}

.transaction-row {
    display: flex;

    justify-content:
        space-between;

    gap: 5px;
}

.transaction-label {
    flex: 0 0 18mm;
}

.transaction-value {
    flex: 1;

    text-align: right;
}

/*
        |--------------------------------------------------------------------------
        | ITEMS
        |--------------------------------------------------------------------------
        */

.item {
    margin-bottom: 2mm;
}

.item-name {
    font-weight: bold;

    word-break: break-word;
}

.item-detail {
    display: flex;

    justify-content:
        space-between;

    gap: 3px;
}

.item-price {
    white-space: nowrap;
}

.item-total {
    text-align: right;

    white-space: nowrap;
}

/*
        |--------------------------------------------------------------------------
        | TOTAL
        |--------------------------------------------------------------------------
        */

.summary-row {
    display: flex;

    justify-content:
        space-between;

    margin-bottom: 1mm;
}

.summary-label {
    text-align: left;
}

.summary-value {
    text-align: right;

    white-space: nowrap;
}

.grand-total {
    font-size: 14px;

    font-weight: bold;

    margin-top: 2mm;
}

/*
        |--------------------------------------------------------------------------
        | FOOTER
        |--------------------------------------------------------------------------
        */

.receipt-footer {
    text-align: center;

    margin-top: 5mm;

    font-size: 10px;
}

.thank-you {
    font-weight: bold;

    font-size: 12px;

    margin-bottom: 1mm;
}

/*
        |--------------------------------------------------------------------------
        | BUTTON
        |--------------------------------------------------------------------------
        */

.receipt-actions {
    margin-top: 20px;

    display: flex;

    gap: 10px;

    justify-content: center;
}

.receipt-actions button {
    border: none;

    padding:
        10px 16px;

    border-radius: 5px;

    cursor: pointer;

    font-size: 14px;
}

.btn-print {
    background: #198754;

    color: white;
}

.btn-close {
    background: #6c757d;

    color: white;
}

/*
        |--------------------------------------------------------------------------
        | PRINT
        |--------------------------------------------------------------------------
        */

@media print {

    @page {
        size: 58mm auto;

        margin: 0;
    }

    body {
        padding: 0;

        margin: 0;

        background: white;
    }

    .receipt-container {
        width: 58mm;

        margin: 0;
    }

    .receipt {
        width: 58mm;

        box-shadow: none;

        padding: 4mm;
    }

    .receipt-actions {
        display: none;
    }
}
```

---

## Catatan Bug & Keterbatasan Kode PDF

> Bagian ini **hanya catatan**. Kode di atas sengaja disalin apa adanya dari PDF. AI assistant **tidak boleh** mengubah kode berdasarkan catatan ini kecuali diminta secara eksplisit.

### A. Perbedaan penyalinan dari PDF

| No | Lokasi | Keterangan |
|----|--------|------------|
| A1 | Card Keranjang (bagian 3) | PDF menampilkan `alignitems:center`. Ini hampir pasti karena pemotongan baris di PDF (`align-` / `items`). Di dokumen ini ditulis `align-items:center` (properti CSS yang valid). |
| A2 | Baris kode panjang | Beberapa baris kode di PDF terpotong ke baris berikutnya (mis. `placeholder="Cari nama barang / kode / barcode..."`, `${product.code} - ${product.barcode}`, `<input type="number" ... onchange=...>`). Di dokumen ini disatukan kembali menjadi satu baris. Isinya tidak diubah. |
| A3 | Indentasi | Indentasi PDF hilang saat ekstraksi teks, sehingga dirapikan ulang. Isi kode tidak diubah. |
| A4 | Posisi `<main>` | PDF tidak menyebut secara eksplisit di mana `<main>` ditempatkan. Dari screenshot dan `kasir.html`, tempatnya setelah `</header>` di dalam `.pos-container` (lalu diikuti `<footer>`). |
| A5 | Footer "Kasir: Admin" | Terlihat di screenshot hasil akhir (halaman 2) tetapi **tidak ada kodenya di PDF**. Markup dan CSS-nya diambil dari `kasir.html` dan dimasukkan ke bagian CSS. |

### B. Bug / keterbatasan logika di kode PDF

| No | Area | Deskripsi |
|----|------|-----------|
| B1 | Penamaan | `searchProduct` dipakai sekaligus sebagai `id` input dan nama fungsi. Berjalan, tetapi rawan membingungkan. |
| B2 | Tombol "Tahan" | `holdTransaction()` hanya menampilkan `alert`. Transaksi tidak disimpan dan keranjang tidak dikosongkan. |
| B3 | Tombol "Bayar & Cetak" | `processPayment()` hanya menampilkan `alert`. Tidak menyimpan transaksi, tidak mengosongkan keranjang, dan belum ada proses cetak (masih komentar). |
| B4 | Nomor transaksi | `TRX-20260911-001` statis di HTML (komentar bilang "otomatis sesuai tanggal", tetapi belum ada kodenya). |
| B5 | Pembatalan | `cancelTransaction()` tidak mereset `tax`, `otherFee`, metode pembayaran, maupun input pelanggan. |
| B6 | Tombol ESC | Tombol ESC selalu memanggil `cancelTransaction()` (muncul `confirm`) meskipun keranjang kosong. |
| B7 | Event input ringkasan | Input diskon/pajak/biaya lain memakai `onchange` (dihitung setelah kehilangan fokus), sedangkan "Uang Dibayar" memakai `oninput` (langsung). |
| B8 | Validasi diskon | Diskon persen hanya dibatasi atribut HTML `max="100"`, tanpa validasi di JavaScript. Diskon (Rp) bisa melebihi subtotal, tetapi total dikunci minimal 0 lewat `Math.max(0, ...)`. |
| B9 | Data pelanggan | `<select>` Pelanggan dan input "No. Member / HP" tidak punya `id`/`name`, sehingga tidak terbaca oleh JavaScript. |
| B10 | `#emptyRow` | `id="emptyRow"` ada di HTML awal, tetapi tidak dipakai oleh `renderCart()` (baris kosong dibuat ulang tanpa id). |
| B11 | Duplikasi logika | Rumus total ada di `calculateTotal()` dan `getGrandTotal()`. Jika salah satu diubah, yang lain harus ikut diubah. |
| B12 | Keamanan | Data produk disisipkan lewat `innerHTML`. Aman untuk data statis, tetapi jika nanti berasal dari database/API perlu di-escape (risiko XSS). |
| B13 | Qty | Input qty tidak dibatasi stok. Tidak ada cek stok sama sekali. |
| B14 | Pencarian | Pencarian tidak menangani Enter/scan barcode otomatis (hanya klik hasil). Fitur barcode scanner baru ada di bagian Saran. |
| B15 | URL asset | `{{ url(asset('css/chasier.css')) }}`: `asset()` sudah menghasilkan URL absolut, sehingga `url()` di luarnya redundan (tetap berfungsi). |
| B16 | Format mata uang | `formatRupiah` memakai `Intl.NumberFormat("id-ID")` sehingga output berbentuk `Rp 75.000` (mengikuti locale browser), bukan string manual. |
| B17 | Tombol Cari | Tombol "Cari" dan event `input` memanggil fungsi yang sama, sehingga tombol tersebut praktis redundan. |

### C. Catatan CSS (`chasier.css`)

| No | Area | Deskripsi |
|----|------|-----------|
| C1 | Class tidak terpakai | `.btn-secondary` dan `.summary-total` didefinisikan tetapi tidak dipakai oleh markup PDF. |
| C2 | `.product-results` | Default `display: none`. JavaScript yang mengubahnya menjadi `block` saat ada kata kunci. |
| C3 | `.btn-pay` | Memakai `height: 58px !important` untuk menimpa `.action-area button { height: 48px; }`. |
| C4 | Responsif | Breakpoint `1000px` (kolom kanan turun ke bawah) dan `600px` (info transaksi di header disembunyikan, form pelanggan 1 kolom). |
| C5 | Lebar kolom kanan | Grid `.main` memakai `minmax(0, 1fr) 420px`. Lebar ringkasan pembayaran tetap 420px pada layar lebar. |
| C6 | Bentrok dengan struk | Nama class di `chasier.css` dan CSS struk saling bertabrakan (lihat Lampiran A), jadi keduanya harus tetap terpisah. |

---

## Daftar Periksa Hasil Akhir (untuk verifikasi)

- [ ] Header menampilkan nama toko, alamat, nomor transaksi, dan tanggal-waktu (diisi JS, format `id-ID`)
- [ ] Mengetik di kolom cari menampilkan daftar hasil (nama / kode / barcode); klik hasil menambah ke keranjang
- [ ] Barang yang sama ditambahkan lagi → qty bertambah 1
- [ ] Tombol `−` / `+` dan input qty mengubah jumlah; qty ≤ 0 lewat tombol `−` menghapus barang
- [ ] Tombol `×` menghapus barang
- [ ] Total Item, Subtotal, dan TOTAL AKHIR terhitung otomatis (diskon %, diskon Rp, pajak, biaya lain)
- [ ] "Uang Dibayar" menghitung KEMBALIAN otomatis; jika kurang, label berubah menjadi **UANG KURANG** dan kelas `short-payment` aktif
- [ ] 6 metode pembayaran: hanya satu tombol yang `active`
- [ ] Tahan / Batal / BAYAR & CETAK menampilkan alert/konfirmasi sesuai kode
- [ ] F2 = fokus ke pencarian, F4 = fokus ke uang dibayar, ESC = batal transaksi
- [ ] Tampilan responsif (diatur di `chasier.css`, lihat bagian CSS)
- [ ] Footer gelap tampil di bawah dengan teks "Kasir: Admin" dan "F2 Cari Barang • F4 Bayar • ESC Batal"
