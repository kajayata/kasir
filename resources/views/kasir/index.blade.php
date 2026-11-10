<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Kasir Retail POS</title>
    <link rel="stylesheet" href="{{ asset('css/chasier.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
</head>
<body>
<div class="admin-shell">
    @include('partials.sidebar')
    <div class="adm-content adm-content--flush">
        <div class="pos-container">

    <!-- HEADER -->
    <header class="header">
        <div class="header-left">
            <button type="button" class="menu-toggle" onclick="toggleSidebar()" aria-label="Menu">&#9776;</button>
            <div>
                <div class="store-name">TOKO RETAIL MAKMUR</div>
                <div class="store-info">
                    Jl. Contoh No. 123 • Jember • Telp. 0812-xxxx-xxxx
                </div>
            </div>
        </div>
        <div class="transaction-info">
            <div>No. Transaksi</div>
            <div class="transaction-number" id="transactionNumber">
                {{ $transactionNumber }}
            </div>
            <div id="currentDate"></div>
        </div>
    </header>

    <!-- MAIN -->
    <main class="main">
        <!-- ================= LEFT ================= -->
        <section>
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
                            <label for="customer">Pelanggan</label>

                            <select class="form-control" id="customer" name="customer_id">
                                @foreach ($customers as $customer)
                                    <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="customerPhone">No. Member / HP</label>
                            <input type="text" class="form-control" id="customerPhone" name="customer_phone" placeholder="Opsional">
                        </div>
                    </div>
                </div>
            </div>

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
        </section>

        <!-- ================= RIGHT ================= -->
        <aside>
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
                            <input type="number" id="discountPercent" value="0" min="0" max="100" onchange="calculateTotal(); renderQuickCash()">
                        </div>

                        <div class="summary-row">
                            <span>Diskon (Rp)</span>
                            <input type="number" id="discountAmount" value="0" min="0" onchange="calculateTotal(); renderQuickCash()">
                        </div>

                        <!-- TAX -->
                        <div class="summary-row">
                            <span>Pajak / PPN</span>
                            <input type="number" id="tax" value="0" min="0" onchange="calculateTotal(); renderQuickCash()">
                        </div>

                        <!-- OTHER FEE -->
                        <div class="summary-row">
                            <span>Biaya Lain</span>
                            <input type="number" id="otherFee" value="0" min="0" onchange="calculateTotal(); renderQuickCash()">
                        </div>

                        <!-- TOTAL -->
                        <div class="total-box">
                            <div class="total-label">TOTAL AKHIR</div>
                            <div class="total-value" id="grandTotal">Rp 0</div>
                        </div>

                        <!-- PAYMENT -->
                        <div class="payment-box">
                            <div class="payment-label">Uang Dibayar</div>
                            <input type="number" id="payment" class="payment-input" placeholder="0" oninput="calculateChange(); renderQuickCash()">

                            <div class="quick-cash" id="quickCash"></div>

                            <!-- PAYMENT METHOD -->
                            <div class="payment-label" style="margin-top:15px">Metode Pembayaran</div>

                            <div class="payment-method" id="paymentMethod">
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

                        <!-- ACTION -->
                        <div class="action-area">
                            <button class="btn btn-warning" onclick="holdTransaction()">Tahan</button>
                            <button class="btn btn-danger" onclick="cancelTransaction()">Batal</button>
                            <button class="btn btn-success btn-pay" onclick="processPayment()">BAYAR &amp; CETAK</button>
                        </div>
                    </div>
                </div>
            </div>
        </aside>
    </main>

    <!-- FOOTER -->
    <footer class="footer">
        <span>Kasir: {{ $kasir }}</span>

        <span>
            F2 Cari Barang • F4 Bayar • ESC Batal
        </span>
    </footer>
</div>
    </div>
</div>

@include('partials.echo')
@include('partials.order-listener')

<script>
    window.KASIR = {
        csrf: @json(csrf_token()),
        searchUrl: @json(route('kasir.search')),
        storeUrl: @json(route('kasir.store')),
        holdUrl: @json(route('kasir.hold')),
        transactionUrl: @json(url('kasir/transaksi')),
        resumeId: @json($resumeId),
    };
</script>
<script src="{{ asset('js/chasier.js') }}"></script>
<script src="{{ asset('js/kasir-extra.js') }}"></script>
</body>
</html>
