const chartText = '#64748b';
const chartGrid = '#e2e8f0';
const chartPrimary = '#1d4ed8';
const chartAccent = '#0f766e';

const commonOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            labels: {
                color: chartText,
                boxWidth: 12,
                font: {
                    size: 12,
                    weight: '600',
                },
            },
        },
    },
    scales: {
        x: {
            ticks: {
                color: chartText,
            },
            grid: {
                display: false,
            },
        },
        y: {
            beginAtZero: true,
            ticks: {
                color: chartText,
                precision: 0,
            },
            grid: {
                color: chartGrid,
            },
        },
    },
};

const ctx1 = document.getElementById('departamentosChart');

if (ctx1) {
    new Chart(ctx1, {
        type: 'bar',
        data: {
            labels: window.departamentosLabels,
            datasets: [{
                label: 'Empleados',
                data: window.departamentosData,
                backgroundColor: chartPrimary,
                borderColor: chartPrimary,
                borderRadius: 6,
                maxBarThickness: 42,
            }],
        },
        options: commonOptions,
    });
}

const ctx2 = document.getElementById('contratacionesChart');

if (ctx2) {
    new Chart(ctx2, {
        type: 'line',
        data: {
            labels: window.contratacionesLabels,
            datasets: [{
                label: 'Contrataciones',
                data: window.contratacionesData,
                borderColor: chartAccent,
                backgroundColor: 'rgba(15,118,110,.12)',
                pointBackgroundColor: chartAccent,
                pointBorderColor: '#ffffff',
                pointBorderWidth: 2,
                pointRadius: 4,
                fill: true,
                tension: 0.35,
            }],
        },
        options: commonOptions,
    });
}

const ctx3 = document.getElementById('gananciasEmpleadoChart');

if (ctx3) {
    new Chart(ctx3, {
        type: 'line',
        data: {
            labels: window.gananciasEmpleadoLabels || [],
            datasets: [{
                label: 'Total pagado',
                data: window.gananciasEmpleadoData || [],
                borderColor: chartPrimary,
                backgroundColor: 'rgba(29,78,216,.12)',
                pointBackgroundColor: chartPrimary,
                pointBorderColor: '#ffffff',
                pointBorderWidth: 2,
                pointRadius: 4,
                fill: true,
                tension: 0.35,
            }],
        },
        options: commonOptions,
    });
}

const closeAnalysisModal = (modal) => {
    if (!modal) {
        return;
    }

    modal.hidden = true;

    if (!document.querySelector('.analysis-modal:not([hidden])')) {
        document.body.classList.remove('modal-open');
    }
};

document.querySelectorAll('[data-modal-target]').forEach((button) => {
    button.addEventListener('click', () => {
        const modal = document.getElementById(button.dataset.modalTarget);

        if (!modal) {
            return;
        }

        modal.hidden = false;
        document.body.classList.add('modal-open');
        modal.querySelector('button[data-modal-close]')?.focus();
    });
});

document.querySelectorAll('.analysis-modal').forEach((modal) => {
    modal.querySelectorAll('[data-modal-close]').forEach((control) => {
        control.addEventListener('click', () => closeAnalysisModal(modal));
    });
});

document.addEventListener('keydown', (event) => {
    if (event.key !== 'Escape') {
        return;
    }

    closeAnalysisModal(document.querySelector('.analysis-modal:not([hidden])'));
});
