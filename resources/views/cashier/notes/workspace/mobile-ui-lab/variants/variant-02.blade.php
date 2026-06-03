<section class="screen">
    <div class="band">
        <small>02 / Stepper Wizard</small>
        <h2>Nota Bertahap</h2>
    </div>

    <div class="stack">
        <section class="card is-active" data-step id="step-info">
            <h3>Step 1 - Informasi</h3>
            <div class="field"><label>Customer</label><input placeholder="Nama"></div>
            <div class="field"><label>No. HP</label><input placeholder="08xx"></div>
            <button class="btn" data-next-step="#step-items" type="button">Lanjut rincian</button>
        </section>

        <section class="card" data-step id="step-items">
            <h3>Step 2 - Rincian</h3>
            <div class="product-list" data-product-list></div>
            <button class="btn" data-next-step="#step-pay" type="button">Lanjut bayar</button>
        </section>

        <section class="card" data-step id="step-pay">
            <h3>Step 3 - Pembayaran</h3>
            <div class="cart-list" data-cart-list></div>
            <div class="row"><b data-total data-seed="0">Rp 0</b><button class="btn" type="button">Selesai</button></div>
        </section>
    </div>
</section>
