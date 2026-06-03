<script>
(() => {
    const root = document.querySelector('[data-demo-root]');
    if (!root) return;

    const products = [
        ['Oli Mesin MPX', 65000],
        ['Kampas Rem', 120000],
        ['Busi Iridium', 85000],
        ['Roller CVT', 95000],
        ['V-Belt', 160000],
    ];

    const cart = [];
    const money = (value) => `Rp ${Number(value || 0).toLocaleString('id-ID')}`;

    const cartTotal = () => cart.reduce((sum, item) => sum + item.price, 0);

    const render = () => {
        const total = cartTotal();

        root.querySelectorAll('[data-total]').forEach((target) => {
            target.textContent = money(total || Number(target.dataset.seed || 0));
        });

        root.querySelectorAll('[data-cart-list]').forEach((list) => {
            list.innerHTML = cart.length
                ? cart.map((item, index) => `
                    <div class="row">
                        <span>${item.name}</span>
                        <button class="chip" data-remove-item="${index}">${money(item.price)} ×</button>
                    </div>
                `).join('')
                : '<span class="muted">Belum ada item dummy.</span>';
        });
    };

    root.querySelectorAll('[data-product-list]').forEach((list) => {
        list.innerHTML = products.map((item, index) => `
            <button class="btn alt row" data-add-product="${index}">
                <span>${item[0]}</span>
                <b>${money(item[1])}</b>
            </button>
        `).join('');
    });

    root.addEventListener('click', (event) => {
        const add = event.target.closest('[data-add-product]');
        if (add) {
            const product = products[Number(add.dataset.addProduct)];
            cart.push({ name: product[0], price: product[1] });
            render();
        }

        const remove = event.target.closest('[data-remove-item]');
        if (remove) {
            cart.splice(Number(remove.dataset.removeItem), 1);
            render();
        }

        const next = event.target.closest('[data-next-step]');
        if (next) {
            const current = root.querySelector('[data-step].is-active');
            const nextPanel = root.querySelector(next.dataset.nextStep);
            current?.classList.remove('is-active');
            nextPanel?.classList.add('is-active');
        }

        const toggle = event.target.closest('[data-toggle]');
        if (toggle) {
            root.querySelector(toggle.dataset.toggle)?.classList.toggle('is-open');
        }

        const pay = event.target.closest('[data-pay]');
        if (pay) {
            root.querySelector('[data-pay-text]').textContent = pay.dataset.pay;
        }

        const key = event.target.closest('[data-key]');
        if (key) {
            const output = root.querySelector('[data-keypad-output]');
            const current = output.dataset.value || '';
            output.dataset.value = key.dataset.key === 'C' ? '' : current + key.dataset.key;
            output.textContent = money(output.dataset.value || 0);
        }
    });

    render();
})();
</script>
