import Chart from 'chart.js/auto';

const source = document.getElementById('dashboard-charts');

if (source) {
    const data = JSON.parse(source.textContent);
    const dark = document.documentElement.classList.contains('dark');
    const brand = '#0b4f9c';
    const accent = '#d0121a';
    const palette = [brand, accent, '#0f766e', '#b45309', '#475569', '#7c3aed'];

    Chart.defaults.color = dark ? '#cbd5e1' : '#475569';
    Chart.defaults.font.family = "'Source Sans 3', sans-serif";
    Chart.defaults.borderColor = dark ? '#334155' : '#e2e8f0';

    const legend = { position: 'bottom', labels: { boxWidth: 12, padding: 14 } };

    const mount = (id, config) => {
        const canvas = document.getElementById(id);

        if (canvas) {
            new Chart(canvas, config);
        }
    };

    mount('chart-quality', {
        type: 'doughnut',
        data: {
            labels: data.quality.labels,
            datasets: [{ data: data.quality.values, backgroundColor: palette, borderWidth: 0 }],
        },
        options: { plugins: { legend }, maintainAspectRatio: false },
    });

    mount('chart-contracts', {
        type: 'doughnut',
        data: {
            labels: data.contracts.labels,
            datasets: [{ data: data.contracts.values, backgroundColor: palette, borderWidth: 0 }],
        },
        options: { plugins: { legend }, maintainAspectRatio: false },
    });

    mount('chart-departments', {
        type: 'bar',
        data: {
            labels: data.departments.labels,
            datasets: [{ label: 'Funcionarios', data: data.departments.values, backgroundColor: brand, borderRadius: 6 }],
        },
        options: {
            plugins: { legend: { display: false } },
            maintainAspectRatio: false,
            scales: { y: { beginAtZero: true, ticks: { precision: 0 } } },
        },
    });

    mount('chart-leaves', {
        type: 'bar',
        data: {
            labels: data.leaves.labels,
            datasets: [{ label: 'Licencias', data: data.leaves.values, backgroundColor: accent, borderRadius: 6 }],
        },
        options: {
            plugins: { legend: { display: false } },
            maintainAspectRatio: false,
            scales: { y: { beginAtZero: true, ticks: { precision: 0 } } },
        },
    });
}
