// FILE: modules/js/dashboard.js
// VERSI: DEBUG MODE & ROBUST CHART

function initChart() {
    // Cek element dulu
    var chartElement = document.querySelector("#chart-activity");
    if (!chartElement) {
        console.warn("Element #chart-activity tidak ditemukan");
        return;
    }

    const isDark = document.documentElement.classList.contains('dark');
    const textColor = isDark ? '#94a3b8' : '#475569';
    const gridColor = isDark ? '#334155' : '#e2e8f0';

    var options = {
        series: [{ name: "Loading...", data: [] }], // Inisialisasi awal
        chart: {
            id: 'activityChart',
            type: 'area',
            height: '100%', 
            fontFamily: 'Plus Jakarta Sans, sans-serif',
            foreColor: textColor,
            toolbar: { show: false },
            zoom: { enabled: false },
            background: 'transparent',
            animations: { enabled: true } // Pastikan animasi nyala
        },
        dataLabels: { enabled: false },
        stroke: { curve: 'smooth', width: 2 },
        fill: {
            type: 'gradient',
            gradient: {
                shadeIntensity: 1,
                opacityFrom: 0.4,
                opacityTo: 0.05,
                stops: [0, 100]
            }
        },
        // Palet warna yang aman
        colors: ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6'],
        grid: {
            borderColor: gridColor,
            strokeDashArray: 4,
            padding: { top: 0, right: 0, bottom: 0, left: 10 }
        },
        xaxis: {
            categories: [],
            axisBorder: { show: false },
            axisTicks: { show: false },
            labels: { style: { fontSize: '10px' } },
            tooltip: { enabled: false }
        },
        yaxis: {
            show: true,
            labels: { style: { fontSize: '10px' }, formatter: (val) => val.toFixed(0) }
        },
        tooltip: {
            theme: isDark ? 'dark' : 'light',
            y: { formatter: function (val) { return val + " hits" } }
        },
        legend: {
            position: 'top',
            horizontalAlign: 'right',
            offsetY: -20
        }
    };

    // Hapus chart lama jika ada bug
    if(window.activityChart) {
        try { window.activityChart.destroy(); } catch(e) {}
    }
    
    window.activityChart = new ApexCharts(chartElement, options);
    window.activityChart.render();
}

function loadDashboardData() {
    const period = document.getElementById('filter-period') ? document.getElementById('filter-period').value : 'today';

    fetch(`api_dashboard.php?period=${period}`)
        .then(response => response.json())
        .then(data => {
            // DEBUG: Lihat di Console Browser (F12) jika grafik masih hilang
            console.log("Dashboard Data:", data);

            // Update Statistik
            animateValue("val-online", data.online_users || 0);
            animateValue("val-unique", data.unique_visits || 0);
            animateValue("val-leads", data.total_leads || 0);
            animateValue("val-hits", data.total_hits || 0);

            // Update Grafik
            if (window.activityChart) {
                // Update Kategori (Sumbu X)
                if(data.chart_labels && data.chart_labels.length > 0){
                    window.activityChart.updateOptions({
                        xaxis: { categories: data.chart_labels }
                    });
                }

                // Update Data Series (Sumbu Y)
                if (data.chart_series && Array.isArray(data.chart_series) && data.chart_series.length > 0) {
                    window.activityChart.updateSeries(data.chart_series);
                } else {
                    console.warn("Chart series kosong atau format salah");
                    window.activityChart.updateSeries([{ name: 'Tidak ada data', data: [] }]);
                }
            } else {
                // Jika chart belum ada (mungkin loadPage belum selesai), init ulang
                initChart();
            }

            // Update Tabel
            renderLogs(data.recent_logs);
        })
        .catch(err => console.error("API Error:", err));
}

function animateValue(id, end) {
    const obj = document.getElementById(id);
    if(!obj) return;
    if(obj.innerText == end) return;
    
    let startTimestamp = null;
    const duration = 500;
    const start = parseInt(obj.innerText.replace(/,/g, '')) || 0;
    
    const step = (timestamp) => {
        if (!startTimestamp) startTimestamp = timestamp;
        const progress = Math.min((timestamp - startTimestamp) / duration, 1);
        obj.innerHTML = Math.floor(progress * (end - start) + start).toLocaleString();
        if (progress < 1) {
            window.requestAnimationFrame(step);
        } else {
            obj.innerHTML = end.toLocaleString();
        }
    };
    window.requestAnimationFrame(step);
}

function renderLogs(logs) {
    const container = document.getElementById('log-list-body');
    if (!container) return;

    if (!logs || logs.length === 0) {
        container.innerHTML = '<tr><td class="p-4 text-center text-xs text-slate-400">Belum ada aktivitas.</td></tr>';
        return;
    }

    let html = '';
    logs.forEach(log => {
        html += `
            <tr class="group hover:bg-slate-50 dark:hover:bg-white/5 transition">
                <td class="px-4 py-3">
                    <div class="flex items-center gap-3">
                        <div class="flex flex-col items-center justify-center w-10 h-10 shrink-0 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-500 font-mono text-[10px] leading-none border border-slate-200 dark:border-slate-700">
                            <span class="font-bold text-slate-700 dark:text-slate-300">${log.time}</span>
                            ${log.date ? `<span class="text-[8px] mt-0.5">${log.date}</span>` : ''}
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="text-xs font-bold text-slate-700 dark:text-slate-200 truncate flex items-center gap-1.5">
                                ${log.ip}
                                <span class="px-1.5 py-0.5 rounded-md text-[9px] font-bold ${log.badge_class}">
                                    ${log.status}
                                </span>
                            </div>
                            <div class="text-[10px] text-slate-500 dark:text-slate-400 truncate">${log.device}</div>
                        </div>
                    </div>
                </td>
            </tr>
        `;
    });
    container.innerHTML = html;
}

// Event Listeners
document.addEventListener("DOMContentLoaded", () => {
    initChart();
    loadDashboardData();
    setInterval(loadDashboardData, 60000); 
});

// Fallback untuk SPA
if (typeof ApexCharts !== 'undefined') {
    // Beri sedikit delay agar DOM render dulu
    setTimeout(() => {
        initChart();
        loadDashboardData();
    }, 100);
}