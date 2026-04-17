(() => {
    const payloadElement = document.getElementById('admin-dashboard-analytics-payload');

    if (!payloadElement || typeof ApexCharts === 'undefined') {
        return;
    }

    let payload = {};

    try {
        payload = JSON.parse(payloadElement.textContent || '{}');
    } catch (error) {
        console.error('Payload analytics dashboard tidak valid.', error);
        return;
    }

    const containers = {
        stock: document.getElementById('admin-chart-stock-status-donut'),
        topSelling: document.getElementById('admin-chart-top-selling-bar'),
        cashflow: document.getElementById('admin-chart-cashflow-line'),
        operational: document.getElementById('admin-chart-operational-performance-bar'),
    };

    const charts = payload && typeof payload === 'object' ? payload.charts || {} : {};
    const chartInstances = {};

    const formatNumber = (value) => new Intl.NumberFormat('id-ID').format(Number(value || 0));
    const formatRupiah = (value) => `Rp ${formatNumber(value)}`;

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

    const theme = () => ({
        primary: getCssValue('--bs-primary', '#435ebe'),
        success: getCssValue('--bs-success', '#16a34a'),
        warning: getCssValue('--bs-warning', '#f59e0b'),
        danger: getCssValue('--bs-danger', '#ef4444'),
        info: getCssValue('--bs-info', '#06b6d4'),
        text: getCssValue('--bs-body-color', '#25396f'),
        muted: getCssValue('--bs-secondary-color', '#7c8db5'),
        border: getCssValue('--bs-border-color', '#dce7f1'),
        surface: getCssValue('--bs-body-bg', '#ffffff'),
        soft: getCssValue('--bs-tertiary-bg', '#f2f7ff'),
    });

    const shortDate = (value) => {
        const text = String(value || '');

        return text.length >= 10 ? text.slice(-2) : text;
    };

    const destroyChart = (key) => {
        if (chartInstances[key]) {
            chartInstances[key].destroy();
            chartInstances[key] = null;
        }
    };

    const emptyState = (container, message, colors) => {
        if (!container) {
            return;
        }

        container.innerHTML = `
            <div style="
                min-height: 260px;
                display: flex;
                align-items: center;
                justify-content: center;
                text-align: center;
                color: ${colors.muted};
                font-size: .84rem;
                font-weight: 700;
                line-height: 1.6;
                padding: 1rem;
            ">
                ${message}
            </div>
        `;
    };

    const buildCommonOptions = (colors) => ({
        chart: {
            fontFamily: 'Nunito, Inter, Segoe UI, sans-serif',
            foreColor: colors.muted,
            toolbar: {
                show: false,
            },
            background: 'transparent',
            animations: {
                enabled: true,
                easing: 'easeinout',
                speed: 450,
            },
        },
        legend: {
            position: 'bottom',
            horizontalAlign: 'center',
            fontSize: '12px',
            labels: {
                colors: colors.muted,
            },
            markers: {
                width: 10,
                height: 10,
                radius: 99,
            },
            itemMargin: {
                horizontal: 12,
                vertical: 6,
            },
        },
        stroke: {
            curve: 'smooth',
            width: 3,
        },
        dataLabels: {
            enabled: false,
        },
        grid: {
            borderColor: colors.border,
            strokeDashArray: 4,
            padding: {
                left: 6,
                right: 12,
                top: 6,
                bottom: 0,
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

    const renderStockDonut = () => {
        const key = 'stock';
        const container = containers.stock;
        const data = charts.stock_status_donut || {};
        const colors = theme();
        const segments = Array.isArray(data.segments) ? data.segments : [];
        const values = segments.map((segment) => Number(segment?.value || 0));

        destroyChart(key);

        if (!container || !segments.length || values.every((value) => value === 0)) {
            emptyState(container, 'Belum ada data status stok untuk divisualisasikan.', colors);
            return;
        }

        container.innerHTML = '';

        chartInstances[key] = new ApexCharts(container, {
            ...buildCommonOptions(colors),
            chart: {
                ...buildCommonOptions(colors).chart,
                type: 'donut',
                height: 280,
            },
            series: values,
            labels: segments.map((segment) => `${segment?.label || '-'} (${formatNumber(segment?.value || 0)})`),
            colors: [
                colors.success,
                colors.warning,
                colors.danger,
                colors.info,
            ],
            plotOptions: {
                pie: {
                    donut: {
                        size: '70%',
                        labels: {
                            show: true,
                            name: {
                                show: true,
                                color: colors.muted,
                                fontSize: '14px',
                                fontWeight: 700,
                                offsetY: 18,
                            },
                            value: {
                                show: true,
                                color: colors.text,
                                fontSize: '28px',
                                fontWeight: 800,
                                offsetY: -10,
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
            stroke: {
                width: 0,
            },
            tooltip: {
                theme: isDark() ? 'dark' : 'light',
                y: {
                    formatter: (value) => `${formatNumber(value)} Produk`,
                },
            },
        });

        chartInstances[key].render();
    };

    const renderTopSellingBar = () => {
        const key = 'topSelling';
        const container = containers.topSelling;
        const data = charts.top_selling_bar || {};
        const categories = Array.isArray(data.categories) ? data.categories : [];
        const seriesRow = Array.isArray(data.series) && data.series[0] ? data.series[0] : null;
        const values = Array.isArray(seriesRow?.values) ? seriesRow.values.map((value) => Number(value || 0)) : [];
        const colors = theme();

        destroyChart(key);

        if (!container || !categories.length || !values.length) {
            emptyState(container, 'Belum ada data produk terjual pada bulan aktif.', colors);
            return;
        }

        container.innerHTML = '';

        chartInstances[key] = new ApexCharts(container, {
            ...buildCommonOptions(colors),
            chart: {
                ...buildCommonOptions(colors).chart,
                type: 'bar',
                height: 320,
            },
            series: [
                {
                    name: seriesRow?.label || 'Qty Terjual',
                    data: values,
                },
            ],
            colors: [colors.primary],
            plotOptions: {
                bar: {
                    horizontal: true,
                    borderRadius: 8,
                    barHeight: '55%',
                    distributed: false,
                    dataLabels: {
                        position: 'top',
                    },
                },
            },
            dataLabels: {
                enabled: true,
                style: {
                    fontSize: '11px',
                    fontWeight: 800,
                },
                formatter: (value) => formatNumber(value),
                offsetX: 12,
            },
            xaxis: {
                categories: categories.map((row) => row?.label || '-'),
                labels: {
                    style: {
                        colors: colors.muted,
                        fontSize: '11px',
                    },
                    formatter: (value) => formatNumber(value),
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
                        colors: colors.text,
                        fontSize: '12px',
                        fontWeight: 700,
                    },
                    maxWidth: 220,
                },
            },
            tooltip: {
                theme: isDark() ? 'dark' : 'light',
                y: {
                    formatter: (value, opts) => {
                        const index = opts.dataPointIndex;
                        const detail = Array.isArray(data.detail) ? data.detail[index] || {} : {};

                        return `${formatNumber(value)} Unit | ${formatRupiah(detail.gross_revenue_rupiah || 0)}`;
                    },
                },
            },
        });

        chartInstances[key].render();
    };

    const renderCashflowLine = () => {
        const key = 'cashflow';
        const container = containers.cashflow;
        const data = charts.cashflow_line || {};
        const labels = Array.isArray(data.labels) ? data.labels : [];
        const series = Array.isArray(data.series) ? data.series : [];
        const colors = theme();

        destroyChart(key);

        if (!container || !labels.length || !series.length) {
            emptyState(container, 'Belum ada data tren arus kas pada bulan aktif.', colors);
            return;
        }

        container.innerHTML = '';

        const seriesColors = {
            cash_in: colors.success,
            cash_out: colors.danger,
            net_cash_flow: colors.info,
        };

        chartInstances[key] = new ApexCharts(container, {
            ...buildCommonOptions(colors),
            chart: {
                ...buildCommonOptions(colors).chart,
                type: 'area',
                height: 320,
            },
            series: series.map((row) => ({
                name: row?.label || '-',
                data: Array.isArray(row?.values) ? row.values.map((value) => Number(value || 0)) : [],
            })),
            colors: series.map((row) => seriesColors[row?.key] || colors.primary),
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.34,
                    opacityTo: 0.06,
                    stops: [0, 90, 100],
                },
            },
            markers: {
                size: 4,
                strokeWidth: 0,
                hover: {
                    size: 6,
                },
            },
            xaxis: {
                categories: labels.map((label) => shortDate(label)),
                labels: {
                    style: {
                        colors: colors.muted,
                        fontSize: '11px',
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
                        colors: colors.muted,
                        fontSize: '11px',
                    },
                    formatter: (value) => formatNumber(value),
                },
            },
            tooltip: {
                theme: isDark() ? 'dark' : 'light',
                y: {
                    formatter: (value) => formatRupiah(value),
                },
            },
        });

        chartInstances[key].render();
    };

    const renderOperationalBar = () => {
        const key = 'operational';
        const container = containers.operational;
        const data = charts.operational_performance_bar || {};
        const labels = Array.isArray(data.labels) ? data.labels : [];
        const series = Array.isArray(data.series) ? data.series : [];
        const colors = theme();

        destroyChart(key);

        if (!container || !labels.length || !series.length) {
            emptyState(container, 'Belum ada data kinerja operasional pada bulan aktif.', colors);
            return;
        }

        container.innerHTML = '';

        const seriesColors = {
            operational_profit: colors.primary,
            operational_expense: colors.warning,
            refund: colors.danger,
        };

        chartInstances[key] = new ApexCharts(container, {
            ...buildCommonOptions(colors),
            chart: {
                ...buildCommonOptions(colors).chart,
                type: 'bar',
                height: 320,
            },
            series: series.map((row) => ({
                name: row?.label || '-',
                data: Array.isArray(row?.values) ? row.values.map((value) => Number(value || 0)) : [],
            })),
            colors: series.map((row) => seriesColors[row?.key] || colors.info),
            plotOptions: {
                bar: {
                    horizontal: false,
                    borderRadius: 6,
                    columnWidth: '52%',
                },
            },
            xaxis: {
                categories: labels.map((label) => shortDate(label)),
                labels: {
                    style: {
                        colors: colors.muted,
                        fontSize: '11px',
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
                        colors: colors.muted,
                        fontSize: '11px',
                    },
                    formatter: (value) => formatNumber(value),
                },
            },
            tooltip: {
                theme: isDark() ? 'dark' : 'light',
                y: {
                    formatter: (value) => formatRupiah(value),
                },
            },
        });

        chartInstances[key].render();
    };

    const renderAll = () => {
        renderStockDonut();
        renderTopSellingBar();
        renderCashflowLine();
        renderOperationalBar();
    };

    renderAll();

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
