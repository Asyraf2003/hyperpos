<section class="screen">
    <div class="band">
        <small>03 / POS Keypad</small>
        <h2>Input Cepat Kasir</h2>
    </div>

    <div class="stack">
        <section class="card">
            <h3>Nominal Servis</h3>
            <div class="total" data-keypad-output data-value="">Rp 0</div>
            <div class="keypad">
                @foreach (['1','2','3','4','5','6','7','8','9','C','0','000'] as $key)
                    <button class="btn" data-key="{{ $key }}" type="button">{{ $key }}</button>
                @endforeach
            </div>
        </section>

        <section class="card">
            <h3>Aksi Cepat</h3>
            <div class="grid">
                <button class="btn alt" type="button">Servis</button>
                <button class="btn alt" type="button">Produk</button>
                <button class="btn alt" type="button">Paket</button>
                <button class="btn" type="button">Proses</button>
            </div>
        </section>
    </div>
</section>
