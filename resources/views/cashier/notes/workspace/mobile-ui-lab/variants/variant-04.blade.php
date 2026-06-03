<section class="screen">
    <div class="band">
        <small>04 / Bottom Sheet</small>
        <h2>Checkout Mengambang</h2>
    </div>

    <div class="stack">
        <section class="card">
            <h3>Tambah Rincian</h3>
            <div class="product-list" data-product-list></div>
        </section>

        <section class="card">
            <h3>Item Dipilih</h3>
            <div class="cart-list" data-cart-list></div>
        </section>

        <section class="card row bottom-bar">
            <div><span class="muted">Total</span><div class="total" data-total>Rp 0</div></div>
            <button class="btn" data-toggle="#checkout-sheet" type="button">Checkout</button>
        </section>

        <div class="drawer-panel" id="checkout-sheet">
            <h3>Konfirmasi Pembayaran</h3>
            <button class="btn alt" data-pay="Tanpa pembayaran" type="button">Tanpa pembayaran</button>
            <button class="btn" data-pay="Bayar penuh" type="button">Bayar penuh</button>
            <span class="muted">Pilihan: <b data-pay-text>Belum dipilih</b></span>
        </div>
    </div>
</section>
