<style>
    .realtime-toast-stack {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
        display: flex;
        flex-direction: column;
        gap: 10px;
        width: min(360px, calc(100vw - 40px));
    }

    .realtime-toast {
        background: #fff;
        border-left: 5px solid #198754;
        border-radius: 8px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.18);
        padding: 14px 16px 14px 18px;
        animation: realtime-toast-in 0.25s ease-out;
    }

    .realtime-toast-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 6px;
    }

    .realtime-toast-title {
        font-weight: 700;
        color: #146c43;
        font-size: 14px;
    }

    .realtime-toast-close {
        border: 0;
        background: none;
        color: #6c757d;
        cursor: pointer;
        font-size: 18px;
        line-height: 1;
        padding: 0;
    }

    .realtime-toast-number {
        font-weight: 600;
        font-size: 13px;
        color: #212529;
    }

    .realtime-toast-meta {
        margin-top: 4px;
        font-size: 12px;
        color: #6c757d;
    }

    .realtime-toast-link {
        display: inline-block;
        margin-top: 8px;
        font-size: 12px;
        font-weight: 600;
        color: #0d6efd;
        text-decoration: none;
    }

    .realtime-toast-link:hover {
        text-decoration: underline;
    }

    @keyframes realtime-toast-in {
        from {
            opacity: 0;
            transform: translateY(-8px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>

<div class="realtime-toast-stack" id="realtimeToastStack"></div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (!window.Echo) {
            return;
        }

        const stack = document.getElementById('realtimeToastStack');
        const rupiah = new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            maximumFractionDigits: 0,
        });

        function showOrderToast(data) {
            const toast = document.createElement('div');
            toast.className = 'realtime-toast';

            const head = document.createElement('div');
            head.className = 'realtime-toast-head';

            const title = document.createElement('span');
            title.className = 'realtime-toast-title';
            title.textContent = 'Transaksi Baru';

            const close = document.createElement('button');
            close.type = 'button';
            close.className = 'realtime-toast-close';
            close.setAttribute('aria-label', 'Tutup');
            close.textContent = '×';

            head.append(title, close);

            const number = document.createElement('div');
            number.className = 'realtime-toast-number';
            number.textContent = data.transaction_number || 'Transaksi #' + data.id;

            const meta = document.createElement('div');
            meta.className = 'realtime-toast-meta';
            meta.textContent = rupiah.format(data.grand_total || 0) + ' • Kasir: ' + (data.kasir || '-');

            toast.append(head, number, meta);

            if (data.struk_url) {
                const link = document.createElement('a');
                link.className = 'realtime-toast-link';
                link.href = data.struk_url;
                link.target = '_blank';
                link.rel = 'noopener';
                link.textContent = 'Lihat struk';
                toast.append(link);
            }

            stack.appendChild(toast);

            const remove = function () {
                toast.remove();
            };

            close.addEventListener('click', remove);
            setTimeout(remove, 8000);
        }

        window.Echo.channel('orders').listen('.order.placed', function (data) {
            console.log('Transaksi baru:', data);
            showOrderToast(data);
        });
    });
</script>
