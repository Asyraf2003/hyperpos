(() => {
    const payloadElement = document.getElementById('admin-dashboard-analytics-payload');

    if (!payloadElement || typeof ApexCharts === 'undefined') {
        return;
    }

    let payload = {};

    const parseInlinePayload = () => {
        try {
            payload = JSON.parse(payloadElement.textContent || '{}');
            return true;
        } catch (error) {
            console.error('Payload analytics dashboard tidak valid.', error);
            payload = {};
            return false;
        }
    };

    const loadRemotePayload = async () => {
        const url = payloadElement.dataset.url || '';

        if (!url) {
            parseInlinePayload();
            renderAll();
            return;
        }

        try {
            const response = await fetch(url, {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }

            payload = await response.json();
            renderAll();
        } catch (error) {
            console.error('Gagal memuat analytics dashboard.', error);

            if (parseInlinePayload()) {
                renderAll();
            }
        }
    };

    const containers = {
        stock: document.getElementById('admin-chart-stock-status-donut'),
        topSelling: document.getElementById('admin-chart-top-selling-bar'),
        cashflow: document.getElementById('admin-chart-cashflow-line'),
    };

    const currentCharts = () => (payload && typeof payload === 'object' ? payload.charts || {} : {});
    const instances = {};

    const formatNumber = (value) => new Intl.NumberFormat('id-ID').format(Number(value || 0));
    const formatRupiah = (value) => `Rp ${formatNumber(value)}`;

    const compactNumber = (value) => {
        const number = Number(value || 0);
        const abs = Math.abs(number);

        if (abs >= 1000000000) {
            return `${(number / 1000000000).toFixed(abs >= 10000000000 ? 0 : 1).replace('.0', '')} M`;
        }

        if (abs >= 1000000) {
            return `${(number / 1000000).toFixed(abs >= 10000000 ? 0 : 1).replace('.0', '')} Jt`;
        }

        if (abs >= 1000) {
            return `${(number / 1000).toFixed(abs >= 10000 ? 0 : 1).replace('.0', '')} Rb`;
        }

        return formatNumber(number);
    };

    const getCssValue = (name, fallback) => {
        const root = getComputedStyle(document.documentElement).getPropertyValue(name).trim();
        const body = getComputedStyle(document.body).getPropertyValue(name).trim();

        return root || body || fallback;
    };

    const isDark = () => {
        const html = document.documentElement;
        const body = document.body;

        return (
            html.classList.contains('dark') ||
            body.classList.contains('dark') ||
            html.getAttribute('data-bs-theme') === 'dark' ||
            body.getAttribute('data-bs-theme') === 'dark'
        );
    };

    const palette = () => ({
        primary: getCssValue('--bs-primary', '#435ebe'),
        success: getCssValue('--bs-success', '#28c76f'),
        warning: getCssValue('--bs-warning', '#fdac41'),
        danger: getCssValue('--bs-danger', '#ea5455'),
        info: getCssValue('--bs-info', '#00cfe8'),
        text: getCssValue('--bs-body-color', '#25396f'),
        muted: getCssValue('--bs-secondary-color', '#7c8db5'),
        border: getCssValue('--bs-border-color', '#ebeef5'),
    });

    const truncateLabel = (value, max = 14) => {
        const text = String(value || '');
        return text.length > max ? `${text.slice(0, max - 1)}…` : text;
    };

    const shortDate = (value) => {
        const text = String(value || '');
        return text.length >= 10 ? text.slice(-2) : text;
    };

    const destroy = (key) => {
        if (instances[key]) {
            instances[key].destroy();
            instances[key] = null;
        }
    };

    const emptyState = (container, message, colors) => {
        if (!container) {
            return;
        }

        container.innerHTML = `
            <div style="
                min-height: 340px;
                display: flex;
                align-items: center;
                justify-content: center;
                text-align: center;
                color: ${colors.muted};
                font-size: .9rem;
                font-weight: 700;
                line-height: 1.6;
                padding: 1rem;
            ">
                ${message}
            </div>
        `;
    };

    const baseOptions = (colors) => ({
        chart: {
            fontFamily: 'Nunito, Inter, Segoe UI, sans-serif',
            foreColor: colors.muted,
            background: 'transparent',
            toolbar: {
                show: false,
            },
            zoom: {
                enabled: false,
            },
            animations: {
                enabled: true,
                easing: 'easeinout',
                speed: 500,
            },
            parentHeightOffset: 0,
        },
        legend: {
            position: 'bottom',
            horizontalAlign: 'center',
            fontSize: '12px',
            fontWeight: 700,
            labels: {
                colors: colors.muted,
            },
            markers: {
                width: 10,
                height: 10,
                radius: 99,
            },
            itemMargin: {
                horizontal: 10,
                vertical: 6,
            },
        },
        dataLabels: {
            enabled: false,
        },
        grid: {
            borderColor: colors.border,
            strokeDashArray: 3,
            padding: {
                left: 8,
                right: 12,
                top: 8,
                bottom: 2,
            },
        },
        tooltip: {
            theme: isDark() ? 'dark' : 'light',
        },
        noData: {
            text: 'Belum ada data.',
            align: 'center',
            verticalAlign: 'middle',
            style: {
                color: colors.muted,
                fontSize: '13px',
                fontFamily: 'Nunito, Inter, Segoe UI, sans-serif',
            },
        },
    });

    const renderStock = () => {
        const charts = currentCharts();
        const key = 'stock';
        const container = containers.stock;
        const data = charts.stock_status_donut || {};
        const colors = palette();
        const segments = Array.isArray(data.segments) ? data.segments : [];
        const values = segments.map((segment) => Number(segment?.value || 0));

        destroy(key);

        if (!container || !segments.length || values.every((value) => value === 0)) {
            emptyState(container, 'Belum ada data status stok untuk divisualisasikan.', colors);
            return;
        }

        container.innerHTML = '';

        instances[key] = new ApexCharts(container, {
            ...baseOptions(colors),
            chart: {
                ...baseOptions(colors).chart,
                type: 'donut',
                height: 340,
            },
            series: values,
            labels: segments.map((segment) => `${segment?.label || '-'} (${formatNumber(segment?.value || 0)})`),
            colors: [colors.success, colors.warning, colors.danger, colors.info],
            stroke: {
                width: 0,
            },
            plotOptions: {
                pie: {
                    expandOnClick: false,
                    donut: {
                        size: '72%',
                        labels: {
                            show: true,
                            name: {
                                show: true,
                                offsetY: 16,
                                color: colors.muted,
                                fontSize: '14px',
                                fontWeight: 700,
                            },
                            value: {
                                show: true,
                                offsetY: -14,
                                color: colors.text,
                                fontSize: '32px',
                                fontWeight: 800,
                                formatter: () => formatNumber(data.total_value || 0),
                            },
                            total: {
                                show: true,
                                showAlways: true,
                                label: 'Produk',
                                color: colors.muted,
                                fontSize: '13px',
                                fontWeight: 700,
                                formatter: () => formatNumber(data.total_value || 0),
                            },
                        },
                    },
                },
            },
            tooltip: {
                theme: isDark() ? 'dark' : 'light',
                y: {
                    formatter: (value) => `${formatNumber(value)} Produk`,
                },
            },
        });

        instances[key].render();
    };

    const renderTopSelling = () => {
        const charts = currentCharts();
        const key = 'topSelling';
        const container = containers.topSelling;
        const data = charts.top_selling_bar || {};
        const colors = palette();
        const categories = Array.isArray(data.categories) ? data.categories : [];
        const details = Array.isArray(data.detail) ? data.detail : [];
        const seriesRow = Array.isArray(data.series) && data.series[0] ? data.series[0] : null;
        const values = Array.isArray(seriesRow?.values) ? seriesRow.values.map((value) => Number(value || 0)) : [];

        destroy(key);

        if (!container || !categories.length || !values.length) {
            emptyState(container, 'Belum ada data produk terjual pada bulan aktif.', colors);
            return;
        }

        container.innerHTML = '';

        instances[key] = new ApexCharts(container, {
            ...baseOptions(colors),
            chart: {
                ...baseOptions(colors).chart,
                type: 'bar',
                height: 340,
            },
            series: [
                {
                    name: seriesRow?.label || 'Qty Terjual',
                    data: values,
                },
            ],
            colors: [colors.primary, colors.info, colors.success, colors.warning, colors.danger],
            plotOptions: {
                bar: {
                    horizontal: false,
                    borderRadius: 10,
                    columnWidth: '72%',
                    distributed: true,
                },
            },
            stroke: {
                show: true,
                width: 2,
                colors: ['transparent'],
            },
            xaxis: {
                categories: categories.map((row) => truncateLabel(row?.label || '-', 12)),
                labels: {
                    style: {
                        colors: categories.map(() => colors.text),
                        fontSize: '12px',
                        fontWeight: 800,
                    },
                    rotate: 0,
                },
                axisBorder: {
                    color: colors.border,
                },
                axisTicks: {
                    color: colors.border,
                },
            },
            yaxis: {
                labels: {
                    style: {
                        colors: [colors.muted],
                        fontSize: '12px',
                        fontWeight: 800,
                    },
                    formatter: (value) => compactNumber(value),
                },
            },
            dataLabels: {
                enabled: true,
                offsetY: -20,
                style: {
                    fontSize: '12px',
                    fontWeight: 800,
                    colors: [colors.text],
                },
                formatter: (value) => compactNumber(value),
            },
            legend: {
                show: false,
            },
            tooltip: {
                theme: isDark() ? 'dark' : 'light',
                y: {
                    formatter: (value, opts) => {
                        const detail = details[opts.dataPointIndex] || {};
                        return `${formatNumber(value)} Unit | ${formatRupiah(detail.gross_revenue_rupiah || 0)}`;
                    },
                },
            },
        });

        instances[key].render();
    };

    const renderOperationalArea = () => {
        const charts = currentCharts();
        const key = 'cashflow';
        const container = containers.cashflow;
        const data = charts.operational_performance_bar || {};
        const colors = palette();
        const labels = Array.isArray(data.labels) ? data.labels : [];
        const series = Array.isArray(data.series) ? data.series : [];

        destroy(key);

        if (!container || !labels.length || !series.length) {
            emptyState(container, 'Belum ada data laba operasional pada bulan aktif.', colors);
            return;
        }

        container.innerHTML = '';

        const colorMap = {
            operational_profit: colors.primary,
            operational_expense: colors.warning,
            refund: colors.danger,
        };

        instances[key] = new ApexCharts(container, {
            ...baseOptions(colors),
            chart: {
                ...baseOptions(colors).chart,
                type: 'area',
                height: 340,
            },
            series: series.map((row) => ({
                name: row?.label || '-',
                data: Array.isArray(row?.values) ? row.values.map((value) => Number(value || 0)) : [],
            })),
            colors: series.map((row) => colorMap[row?.key] || colors.info),
            stroke: {
                curve: 'smooth',
                width: 3,
            },
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.26,
                    opacityTo: 0.06,
                    stops: [0, 90, 100],
                },
            },
            markers: {
                size: 3.5,
                strokeWidth: 0,
                hover: {
                    size: 5.5,
                },
            },
            xaxis: {
                categories: labels.map((label) => shortDate(label)),
                labels: {
                    style: {
                        colors: labels.map(() => colors.muted),
                        fontSize: '11px',
                        fontWeight: 800,
                    },
                },
                axisBorder: {
                    color: colors.border,
                },
                axisTicks: {
                    color: colors.border,
                },
            },
            yaxis: {
                labels: {
                    style: {
                        colors: [colors.muted],
                        fontSize: '11px',
                        fontWeight: 800,
                    },
                    formatter: (value) => compactNumber(value),
                },
            },
            tooltip: {
                shared: true,
                intersect: false,
                theme: isDark() ? 'dark' : 'light',
                y: {
                    formatter: (value) => formatRupiah(value),
                },
            },
        });

        instances[key].render();
    };

    const renderAll = () => {
        renderStock();
        renderTopSelling();
        renderOperationalArea();
    };

    loadRemotePayload();

    let frame = null;
    const rerender = () => {
        if (frame !== null) {
            cancelAnimationFrame(frame);
        }

        frame = requestAnimationFrame(() => {
            frame = null;
            renderAll();
        });
    };

    const observer = new MutationObserver(rerender);
    observer.observe(document.documentElement, {
        attributes: true,
        attributeFilter: ['class', 'data-bs-theme'],
    });

    if (document.body) {
        observer.observe(document.body, {
            attributes: true,
            attributeFilter: ['class', 'data-bs-theme'],
        });
    }

    window.addEventListener('resize', rerender, { passive: true });
})();
