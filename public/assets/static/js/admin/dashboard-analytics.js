(() => {
    const payloadElement = document.getElementById('admin-dashboard-analytics-payload');

    if (!payloadElement) {
        return;
    }

    let payload = {};

    try {
        payload = JSON.parse(payloadElement.textContent || '{}');
    } catch (error) {
        console.error('Dashboard analytics payload tidak valid.', error);
        return;
    }

    const containers = {
        stock: document.getElementById('admin-chart-stock-status-donut'),
        topSelling: document.getElementById('admin-chart-top-selling-bar'),
        cashflow: document.getElementById('admin-chart-cashflow-line'),
        operational: document.getElementById('admin-chart-operational-performance-bar'),
    };

    const charts = payload && typeof payload === 'object' ? payload.charts || {} : {};

    const formatNumber = (value) => new Intl.NumberFormat('id-ID').format(Number(value || 0));
    const formatRupiah = (value) => `Rp ${formatNumber(value)}`;
    const shortDate = (value) => {
        const text = String(value || '');

        return text.length >= 10 ? text.slice(-2) : text;
    };

    const getTheme = () => {
        const root = getComputedStyle(document.documentElement);
        const body = getComputedStyle(document.body);

        const pick = (name, fallback) =>
            root.getPropertyValue(name).trim()
            || body.getPropertyValue(name).trim()
            || fallback;

        return {
            primary: pick('--bs-primary', '#435ebe'),
            success: pick('--bs-success', '#16a34a'),
            warning: pick('--bs-warning', '#f59e0b'),
            danger: pick('--bs-danger', '#ef4444'),
            info: pick('--bs-info', '#06b6d4'),
            text: pick('--bs-body-color', '#1f2937'),
            muted: pick('--bs-secondary-color', '#6b7280'),
            border: pick('--bs-border-color', '#d1d5db'),
            surface: pick('--bs-body-bg', '#ffffff'),
            soft: pick('--bs-tertiary-bg', '#f8fafc'),
        };
    };

    const colorFromToken = (token, theme) => {
        if (token === 'success') return theme.success;
        if (token === 'warning') return theme.warning;
        if (token === 'danger') return theme.danger;
        if (token === 'info') return theme.info;

        return theme.primary;
    };

    const emptyState = (message, theme) => `
        <div style="
            min-height: 200px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: ${theme.muted};
            font-size: .84rem;
            font-weight: 700;
            line-height: 1.6;
            padding: 1rem;
        ">
            ${message}
        </div>
    `;

    const renderLegend = (items, theme) => `
        <div style="display:flex;flex-wrap:wrap;gap:.75rem 1rem;margin-bottom:.9rem;">
            ${items.map((item) => `
                <div style="display:inline-flex;align-items:center;gap:.45rem;color:${theme.muted};font-size:.8rem;font-weight:700;">
                    <span style="width:10px;height:10px;border-radius:999px;background:${item.color};display:inline-block;"></span>
                    <span>${item.label}</span>
                </div>
            `).join('')}
        </div>
    `;

    const renderStockDonut = (container, data, theme) => {
        if (!container) return;

        const segments = Array.isArray(data?.segments) ? data.segments : [];
        const total = Number(data?.total_value || 0);

        if (!segments.length || total <= 0) {
            container.innerHTML = emptyState('Belum ada data status stok untuk divisualisasikan.', theme);
            return;
        }

        let start = 0;
        const gradient = segments
            .map((segment) => {
                const value = Number(segment?.value || 0);
                const percent = total > 0 ? (value / total) * 100 : 0;
                const end = start + percent;
                const color = colorFromToken(segment?.color_token, theme);
                const rule = `${color} ${start.toFixed(2)}% ${end.toFixed(2)}%`;

                start = end;

                return rule;
            })
            .join(', ');

        const legendItems = segments.map((segment) => ({
            label: `${segment?.label || '-'} (${formatNumber(segment?.value || 0)})`,
            color: colorFromToken(segment?.color_token, theme),
        }));

        container.innerHTML = `
            <div style="display:grid;grid-template-columns:minmax(0,190px) 1fr;gap:1rem;align-items:center;height:100%;">
                <div style="display:flex;justify-content:center;">
                    <div style="
                        width:168px;
                        height:168px;
                        border-radius:999px;
                        background:conic-gradient(${gradient});
                        display:grid;
                        place-items:center;
                        position:relative;
                    ">
                        <div style="
                            width:110px;
                            height:110px;
                            border-radius:999px;
                            background:${theme.surface};
                            border:1px solid ${theme.border};
                            display:flex;
                            flex-direction:column;
                            align-items:center;
                            justify-content:center;
                            text-align:center;
                            padding:.5rem;
                        ">
                            <div style="font-size:1.25rem;font-weight:800;color:${theme.text};line-height:1.2;">
                                ${formatNumber(total)}
                            </div>
                            <div style="font-size:.78rem;font-weight:700;color:${theme.muted};line-height:1.4;">
                                Produk
                            </div>
                        </div>
                    </div>
                </div>
                <div>
                    ${renderLegend(legendItems, theme)}
                    <div style="display:grid;gap:.65rem;">
                        ${segments.map((segment) => `
                            <div style="
                                display:flex;
                                align-items:center;
                                justify-content:space-between;
                                gap:.75rem;
                                border:1px solid ${theme.border};
                                border-radius:14px;
                                background:${theme.surface};
                                padding:.75rem .9rem;
                            ">
                                <div style="display:flex;align-items:center;gap:.55rem;min-width:0;">
                                    <span style="width:10px;height:10px;border-radius:999px;background:${colorFromToken(segment?.color_token, theme)};display:inline-block;"></span>
                                    <span style="font-size:.82rem;color:${theme.muted};font-weight:700;line-height:1.5;">
                                        ${segment?.label || '-'}
                                    </span>
                                </div>
                                <span style="font-size:.9rem;color:${theme.text};font-weight:800;white-space:nowrap;">
                                    ${formatNumber(segment?.value || 0)}
                                </span>
                            </div>
                        `).join('')}
                    </div>
                </div>
            </div>
        `;
    };

    const renderTopSellingBar = (container, data, theme) => {
        if (!container) return;

        const rows = Array.isArray(data?.detail) ? data.detail : [];

        if (!rows.length) {
            container.innerHTML = emptyState('Belum ada produk terjual pada bulan aktif.', theme);
            return;
        }

        const max = Math.max(...rows.map((row) => Number(row?.sold_qty || 0)), 1);

        container.innerHTML = `
            <div style="display:grid;gap:.85rem;">
                ${rows.map((row, index) => {
                    const soldQty = Number(row?.sold_qty || 0);
                    const width = Math.max((soldQty / max) * 100, soldQty > 0 ? 6 : 0);

                    return `
                        <div style="display:grid;grid-template-columns:minmax(0,180px) 1fr auto;gap:.85rem;align-items:center;">
                            <div style="min-width:0;">
                                <div style="font-size:.86rem;font-weight:800;color:${theme.text};line-height:1.45;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                    ${index + 1}. ${row?.label || '-'}
                                </div>
                                <div style="font-size:.78rem;font-weight:700;color:${theme.muted};line-height:1.5;">
                                    ${row?.code || 'Tanpa kode'}
                                </div>
                            </div>
                            <div style="
                                height:12px;
                                border-radius:999px;
                                background:${theme.soft};
                                overflow:hidden;
                                border:1px solid ${theme.border};
                            ">
                                <span style="
                                    display:block;
                                    width:${width}%;
                                    min-width:${soldQty > 0 ? '10px' : '0'};
                                    height:100%;
                                    border-radius:999px;
                                    background:${theme.primary};
                                "></span>
                            </div>
                            <div style="text-align:right;">
                                <div style="font-size:.84rem;font-weight:800;color:${theme.text};line-height:1.4;">
                                    ${formatNumber(soldQty)} Unit
                                </div>
                                <div style="font-size:.76rem;font-weight:700;color:${theme.muted};line-height:1.4;">
                                    ${formatRupiah(row?.gross_revenue_rupiah || 0)}
                                </div>
                            </div>
                        </div>
                    `;
                }).join('')}
            </div>
        `;
    };

    const renderLineChart = (container, data, theme) => {
        if (!container) return;

        const labels = Array.isArray(data?.labels) ? data.labels : [];
        const series = Array.isArray(data?.series) ? data.series : [];

        if (!labels.length || !series.length) {
            container.innerHTML = emptyState('Belum ada data tren arus kas pada bulan aktif.', theme);
            return;
        }

        const allValues = series.flatMap((entry) => Array.isArray(entry?.values) ? entry.values : []);
        const min = Math.min(0, ...allValues.map((value) => Number(value || 0)));
        const max = Math.max(1, ...allValues.map((value) => Number(value || 0)));
        const range = Math.max(max - min, 1);

        const width = 720;
        const height = 250;
        const padding = { top: 18, right: 16, bottom: 34, left: 16 };
        const chartWidth = width - padding.left - padding.right;
        const chartHeight = height - padding.top - padding.bottom;

        const x = (index) => {
            if (labels.length === 1) {
                return padding.left + chartWidth / 2;
            }

            return padding.left + (index * chartWidth) / (labels.length - 1);
        };

        const y = (value) => padding.top + chartHeight - (((Number(value || 0) - min) / range) * chartHeight);

        const lineColors = {
            cash_in: theme.success,
            cash_out: theme.danger,
            net_cash_flow: theme.info,
        };

        const guideValues = [0, .25, .5, .75, 1].map((step) => min + (range * step));

        const guides = guideValues.map((value) => {
            const guideY = y(value);

            return `
                <line x1="${padding.left}" y1="${guideY}" x2="${width - padding.right}" y2="${guideY}" stroke="${theme.border}" stroke-width="1" opacity="0.8" />
            `;
        }).join('');

        const legend = renderLegend(
            series.map((entry) => ({
                label: entry?.label || '-',
                color: lineColors[entry?.key] || theme.primary,
            })),
            theme,
        );

        const paths = series.map((entry) => {
            const values = Array.isArray(entry?.values) ? entry.values : [];
            const color = lineColors[entry?.key] || theme.primary;
            const points = values.map((value, index) => `${x(index)},${y(value)}`).join(' ');

            const lastIndex = values.length - 1;
            const lastX = lastIndex >= 0 ? x(lastIndex) : 0;
            const lastY = lastIndex >= 0 ? y(values[lastIndex]) : 0;

            return `
                <polyline
                    fill="none"
                    stroke="${color}"
                    stroke-width="3"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    points="${points}"
                />
                <circle cx="${lastX}" cy="${lastY}" r="4.5" fill="${color}" />
            `;
        }).join('');

        const ticks = labels.map((label, index) => `
            <div style="
                min-width:0;
                text-align:center;
                font-size:.73rem;
                font-weight:700;
                color:${theme.muted};
                line-height:1.4;
            ">
                ${shortDate(label)}
            </div>
        `).join('');

        container.innerHTML = `
            ${legend}
            <div style="overflow-x:auto;padding-bottom:.35rem;">
                <div style="min-width:720px;">
                    <svg viewBox="0 0 ${width} ${height}" width="100%" height="250" role="img" aria-label="${data?.title || 'Chart'}">
                        ${guides}
                        ${paths}
                    </svg>
                    <div style="display:grid;grid-template-columns:repeat(${labels.length}, minmax(22px, 1fr));gap:.25rem;margin-top:.25rem;">
                        ${ticks}
                    </div>
                </div>
            </div>
        `;
    };

    const renderGroupedBarChart = (container, data, theme) => {
        if (!container) return;

        const labels = Array.isArray(data?.labels) ? data.labels : [];
        const series = Array.isArray(data?.series) ? data.series : [];

        if (!labels.length || !series.length) {
            container.innerHTML = emptyState('Belum ada data kinerja operasional pada bulan aktif.', theme);
            return;
        }

        const allValues = series.flatMap((entry) => Array.isArray(entry?.values) ? entry.values : []);
        const max = Math.max(1, ...allValues.map((value) => Number(value || 0)));

        const width = Math.max(720, labels.length * 54);
        const height = 260;
        const padding = { top: 18, right: 16, bottom: 38, left: 16 };
        const chartWidth = width - padding.left - padding.right;
        const chartHeight = height - padding.top - padding.bottom;
        const groupWidth = chartWidth / Math.max(labels.length, 1);
        const barGap = 4;
        const innerWidth = Math.max(groupWidth - 10, 18);
        const barWidth = Math.max(
            Math.min((innerWidth - (Math.max(series.length - 1, 0) * barGap)) / Math.max(series.length, 1), 18),
            8,
        );

        const barColors = {
            operational_profit: theme.primary,
            operational_expense: theme.warning,
            refund: theme.danger,
        };

        const legend = renderLegend(
            series.map((entry) => ({
                label: entry?.label || '-',
                color: barColors[entry?.key] || theme.info,
            })),
            theme,
        );

        const guides = [0, .25, .5, .75, 1].map((step) => {
            const guideY = padding.top + chartHeight - (chartHeight * step);

            return `
                <line x1="${padding.left}" y1="${guideY}" x2="${width - padding.right}" y2="${guideY}" stroke="${theme.border}" stroke-width="1" opacity="0.8" />
            `;
        }).join('');

        const bars = labels.map((label, labelIndex) => {
            const groupLeft = padding.left + (labelIndex * groupWidth) + ((groupWidth - innerWidth) / 2);

            return series.map((entry, seriesIndex) => {
                const value = Number((entry?.values || [])[labelIndex] || 0);
                const barHeight = max > 0 ? (value / max) * chartHeight : 0;
                const x = groupLeft + (seriesIndex * (barWidth + barGap));
                const y = padding.top + chartHeight - barHeight;
                const color = barColors[entry?.key] || theme.info;

                return `
                    <rect
                        x="${x}"
                        y="${y}"
                        width="${barWidth}"
                        height="${barHeight}"
                        rx="6"
                        fill="${color}"
                    />
                `;
            }).join('');
        }).join('');

        const ticks = labels.map((label) => `
            <div style="
                min-width:0;
                text-align:center;
                font-size:.73rem;
                font-weight:700;
                color:${theme.muted};
                line-height:1.4;
            ">
                ${shortDate(label)}
            </div>
        `).join('');

        container.innerHTML = `
            ${legend}
            <div style="overflow-x:auto;padding-bottom:.35rem;">
                <div style="min-width:${width}px;">
                    <svg viewBox="0 0 ${width} ${height}" width="100%" height="260" role="img" aria-label="${data?.title || 'Chart'}">
                        ${guides}
                        ${bars}
                    </svg>
                    <div style="display:grid;grid-template-columns:repeat(${labels.length}, minmax(22px, 1fr));gap:.25rem;margin-top:.25rem;">
                        ${ticks}
                    </div>
                </div>
            </div>
        `;
    };

    const render = () => {
        const theme = getTheme();

        renderStockDonut(containers.stock, charts.stock_status_donut || {}, theme);
        renderTopSellingBar(containers.topSelling, charts.top_selling_bar || {}, theme);
        renderLineChart(containers.cashflow, charts.cashflow_line || {}, theme);
        renderGroupedBarChart(containers.operational, charts.operational_performance_bar || {}, theme);
    };

    render();

    let frame = null;
    const rerender = () => {
        if (frame !== null) {
            cancelAnimationFrame(frame);
        }

        frame = requestAnimationFrame(() => {
            frame = null;
            render();
        });
    };

    const rootObserver = new MutationObserver(rerender);
    rootObserver.observe(document.documentElement, {
        attributes: true,
        attributeFilter: ['class', 'data-bs-theme'],
    });

    if (document.body) {
        const bodyObserver = new MutationObserver(rerender);
        bodyObserver.observe(document.body, {
            attributes: true,
            attributeFilter: ['class', 'data-bs-theme'],
        });
    }

    window.addEventListener('resize', rerender, { passive: true });
})();
