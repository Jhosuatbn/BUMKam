import '../assets/vendor/fonts/iconify/iconify.css';
import '../assets/vendor/scss/core.scss';
import '../assets/css/demo.css';
import '../assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.scss';
import '../assets/vendor/libs/apex-charts/apex-charts.scss';

import '../assets/vendor/js/helpers.js';
import '../assets/js/config.js';
import '../assets/vendor/libs/jquery/jquery.js';
import '../assets/vendor/libs/popper/popper.js';
import '../assets/vendor/js/bootstrap.js';
import '../assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js';
import '../assets/vendor/js/menu.js';
import '../assets/js/main.js';

import ApexCharts from 'apexcharts';

window.ApexCharts = ApexCharts;

const formatNumber = new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 });

function mountDashboardCharts() {
    const dataElement = document.querySelector('#bumkam-dashboard-chart-data');
    if (!dataElement) return;

    let data;
    try {
        data = JSON.parse(dataElement.textContent);
    } catch (error) {
        console.error('Data grafik dashboard tidak dapat dibaca.', error);
        return;
    }

    const mount = (selector, options) => {
        const element = document.querySelector(selector);
        if (element) new ApexCharts(element, options).render();
    };

    const base = {
        chart: { fontFamily: 'Public Sans, sans-serif', toolbar: { show: false }, parentHeightOffset: 0, redrawOnParentResize: true, redrawOnWindowResize: true },
        dataLabels: { enabled: false },
        grid: { borderColor: '#d9dee3', strokeDashArray: 5 },
        tooltip: { theme: 'light' },
    };

    mount('#bumkam-monthly-orders-chart', {
        ...base,
        chart: { ...base.chart, type: 'bar', height: 300 },
        series: [
            { name: 'Tahun ini', data: data.monthlyOrders ?? [] },
            { name: 'Tahun lalu', data: data.previousYearOrders ?? [] },
        ],
        colors: ['#696cff', '#03c3ec'],
        plotOptions: { bar: { borderRadius: 6, columnWidth: '38%' } },
        xaxis: { categories: data.monthLabels ?? [], axisBorder: { show: false }, axisTicks: { show: false } },
        yaxis: { min: 0, forceNiceScale: true, labels: { formatter: value => formatNumber.format(value) } },
        tooltip: { y: { formatter: value => `${formatNumber.format(value)} pemesanan` } },
    });

    const mountSparkline = (selector, series, color, formatter) => {
        mount(selector, {
            chart: { type: 'area', height: 64, sparkline: { enabled: true }, animations: { enabled: true } },
            series: [{ data: series ?? [] }],
            colors: [color],
            stroke: { curve: 'smooth', width: 2.5 },
            fill: { type: 'gradient', gradient: { opacityFrom: 0.28, opacityTo: 0.02 } },
            tooltip: { y: { formatter } },
        });
    };

    mountSparkline('#bumkam-order-sparkline', data.monthlyOrders, '#71dd37', value => `${formatNumber.format(value)} pemesanan`);
    mountSparkline('#bumkam-sales-sparkline', data.monthlyRevenue, '#03c3ec', value => `Rp ${formatNumber.format(value)}`);

    mount('#bumkam-payment-ratio-chart', {
        chart: { type: 'radialBar', height: 190, sparkline: { enabled: true } },
        series: [Number(data.paymentPaidPercent ?? 0)],
        colors: ['#71dd37'],
        plotOptions: { radialBar: { startAngle: -135, endAngle: 135, hollow: { size: '62%' }, track: { background: '#e7e7ff', strokeWidth: '100%' }, dataLabels: { name: { show: false }, value: { fontSize: '22px', fontWeight: 600, offsetY: -2, formatter: value => `${Math.round(value)}%` } } } },
        stroke: { lineCap: 'round' },
    });

    mount('#bumkam-order-status-chart', {
        chart: { type: 'donut', height: 190, width: '100%', redrawOnParentResize: true, redrawOnWindowResize: true },
        series: data.orderStatus ?? [0, 0],
        labels: ['Lunas', 'Belum lunas'],
        colors: ['#71dd37', '#ffab00'],
        stroke: { width: 3, colors: ['#fff'] },
        dataLabels: { enabled: false },
        legend: { show: false },
        plotOptions: { pie: { donut: { size: '70%' } } },
        tooltip: { y: { formatter: value => `${formatNumber.format(value)} pemesanan` } },
    });

    mount('#bumkam-revenue-trend-chart', {
        ...base,
        chart: { ...base.chart, type: 'area', height: 240 },
        series: [{ name: 'Nilai pemesanan', data: data.monthlyRevenue ?? [] }],
        colors: ['#03c3ec'],
        stroke: { curve: 'smooth', width: 3 },
        fill: { type: 'gradient', gradient: { opacityFrom: 0.35, opacityTo: 0.02 } },
        xaxis: { categories: data.monthLabels ?? [], axisBorder: { show: false }, axisTicks: { show: false } },
        yaxis: { labels: { formatter: value => `Rp ${formatNumber.format(value)}` } },
        tooltip: { y: { formatter: value => `Rp ${formatNumber.format(value)}` } },
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', mountDashboardCharts, { once: true });
} else {
    mountDashboardCharts();
}

