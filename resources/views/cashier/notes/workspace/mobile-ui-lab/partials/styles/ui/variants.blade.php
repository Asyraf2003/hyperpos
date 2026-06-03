.lab-v02 body,
.lab-v02 {
    background: #ecfeff;
}

.lab-v03 {
    background: #111827;
}

.lab-v03 .lab-top,
.lab-v03 .card {
    background: #1f2937;
    color: #f9fafb;
}

.keypad {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 10px;
}

.keypad .btn {
    min-height: 58px;
    background: #374151;
}

.bottom-bar {
    position: sticky;
    bottom: 12px;
    z-index: 10;
}

.drawer-panel {
    display: none;
    padding: 16px;
    border-radius: 24px 24px 0 0;
}

.drawer-panel.is-open {
    display: grid;
    gap: 12px;
}

.chat {
    display: grid;
    gap: 10px;
}

.bubble {
    width: fit-content;
    max-width: 88%;
    padding: 12px 14px;
    border-radius: 18px;
    background: #e0f2fe;
}

.bubble.me {
    justify-self: end;
    background: #dcfce7;
}

.table {
    width: 100%;
    border-collapse: collapse;
}

.table th,
.table td {
    padding: 10px 6px;
    border-bottom: 1px solid #e5e7eb;
    text-align: left;
}

.table input {
    width: 100%;
    padding: 8px;
    border: 1px solid #dbe3ef;
    border-radius: 10px;
}

@media (max-width: 520px) {
    .grid {
        grid-template-columns: 1fr;
    }

    .lab-shell {
        padding-left: 10px;
        padding-right: 10px;
    }
}
