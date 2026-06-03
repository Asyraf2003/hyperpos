.screen,
.card,
.receipt,
.drawer-panel {
    background: #fff;
    box-shadow: 0 18px 45px rgba(15, 23, 42, .10);
}

.screen {
    overflow: hidden;
    border-radius: 30px;
}

.band {
    padding: 18px;
    background: #4f46e5;
    color: #fff;
}

.band h2 {
    margin: 4px 0 0;
    font-size: 1.35rem;
}

.stack {
    display: grid;
    gap: 12px;
    padding: 14px;
}

.card,
.receipt {
    display: grid;
    gap: 12px;
    padding: 16px;
    border-radius: 22px;
}

.card h3,
.receipt h3 {
    margin: 0;
    font-size: 1rem;
}

.muted {
    color: #64748b;
    font-size: .88rem;
}

.field {
    display: grid;
    gap: 6px;
}

.field label {
    font-size: .78rem;
    font-weight: 900;
}

.field input,
.field textarea {
    width: 100%;
    min-height: 46px;
    padding: 10px 12px;
    border: 1px solid #dbe3ef;
    border-radius: 14px;
    background: #f8fafc;
    font: inherit;
}

.btn,
.chip {
    border: 0;
    font-weight: 900;
}

.btn {
    min-height: 48px;
    padding: 0 16px;
    border-radius: 16px;
    background: #4f46e5;
    color: #fff;
}

.btn.alt,
.chip {
    background: #eef2ff;
    color: #4338ca;
}

.grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
}

.product-list,
.cart-list {
    display: grid;
    gap: 9px;
}

.row {
    display: flex;
    justify-content: space-between;
    gap: 10px;
    align-items: center;
}

.total {
    font-size: 1.35rem;
    font-weight: 900;
}
