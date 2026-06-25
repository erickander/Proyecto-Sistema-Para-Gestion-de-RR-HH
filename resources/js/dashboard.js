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

const ctx4 = document.getElementById('rankingIaChart');

if (ctx4) {
    new Chart(ctx4, {
        type: 'bar',
        data: {
            labels: window.rankingIaLabels || [],
            datasets: [
                {
                    label: 'Final',
                    data: window.rankingIaFinalData || [],
                    backgroundColor: chartPrimary,
                    borderRadius: 6,
                    maxBarThickness: 42,
                },
                {
                    label: 'CV',
                    data: window.rankingIaCvData || [],
                    backgroundColor: '#0f766e',
                    borderRadius: 6,
                    maxBarThickness: 42,
                },
                {
                    label: 'Test',
                    data: window.rankingIaTestData || [],
                    backgroundColor: '#9333ea',
                    borderRadius: 6,
                    maxBarThickness: 42,
                },
            ],
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

const closeOpenActionDrawers = (except = null) => {
    document.querySelectorAll('.action-drawer[open]').forEach((drawer) => {
        if (drawer !== except) {
            drawer.removeAttribute('open');
        }
    });
};

document.querySelectorAll('[data-modal-target]').forEach((button) => {
    button.addEventListener('click', () => {
        const modal = document.getElementById(button.dataset.modalTarget);

        if (!modal) {
            return;
        }

        closeOpenActionDrawers();
        modal.hidden = false;
        document.body.classList.add('modal-open');
        modal.querySelector('button[data-modal-close]')?.focus();
    });
});

document.querySelectorAll('.analysis-modal').forEach((modal) => {
    modal.addEventListener('mousedown', (event) => {
        if (event.target === modal || event.target.matches('.analysis-modal__backdrop')) {
            closeAnalysisModal(modal);
        }
    });

    modal.querySelectorAll('[data-modal-close]').forEach((control) => {
        control.addEventListener('click', () => closeAnalysisModal(modal));
    });
});

document.querySelectorAll('.action-drawer').forEach((drawer) => {
    drawer.addEventListener('toggle', () => {
        if (drawer.open) {
            closeAnalysisModal(document.querySelector('.analysis-modal:not([hidden])'));
            closeOpenActionDrawers(drawer);
        }
    });
});

document.addEventListener('mousedown', (event) => {
    document.querySelectorAll('.action-drawer[open]').forEach((drawer) => {
        const form = drawer.querySelector('.action-form');
        const summary = drawer.querySelector('summary');

        if (form?.contains(event.target) || summary?.contains(event.target)) {
            return;
        }

        drawer.removeAttribute('open');
    });
});

document.addEventListener('keydown', (event) => {
    if (event.key !== 'Escape') {
        return;
    }

    closeAnalysisModal(document.querySelector('.analysis-modal:not([hidden])'));
    closeOpenActionDrawers();
});

const addOptionInput = (builder) => {
    const list = builder?.querySelector('[data-options-list]');

    if (!list) {
        return;
    }

    const label = document.createElement('label');
    const input = document.createElement('input');
    const count = list.querySelectorAll('input').length + 1;
    const prefix = builder.dataset.optionPrefix || 'opciones';

    input.name = `${prefix}[]`;
    input.required = true;
    input.placeholder = `Opcion ${count}`;
    label.append(input);
    list.append(label);
    input.focus();
};

const refreshQuestionBuilder = (builder) => {
    const cards = builder.querySelectorAll('[data-question-card]');

    cards.forEach((card, index) => {
        const title = card.querySelector('.question-builder-card__header strong');
        const removeButton = card.querySelector('[data-remove-question]');

        if (title) {
            title.textContent = `Pregunta ${index + 1}`;
        }

        if (removeButton) {
            removeButton.hidden = cards.length === 1;
        }
    });
};

document.addEventListener('click', (event) => {
    const addOptionButton = event.target.closest('[data-add-option]');

    if (addOptionButton) {
        addOptionInput(addOptionButton.closest('[data-option-builder]'));
        return;
    }

    const addQuestionButton = event.target.closest('[data-add-question]');

    if (addQuestionButton) {
        const builder = addQuestionButton.closest('[data-question-builder]');
        const list = builder?.querySelector('[data-question-list]');
        const template = builder?.querySelector('[data-question-template]');

        if (!builder || !list || !template) {
            return;
        }

        const index = Date.now();
        const number = list.querySelectorAll('[data-question-card]').length + 1;
        const baseOrder = Number(list.dataset.baseOrder || 1);
        const html = template.innerHTML
            .replaceAll('__INDEX__', index)
            .replaceAll('__NUMBER__', number)
            .replaceAll('__ORDER__', baseOrder + number - 1);

        list.insertAdjacentHTML('beforeend', html);
        refreshQuestionBuilder(builder);
        list.querySelector('[data-question-card]:last-child textarea')?.focus();
        return;
    }

    const removeQuestionButton = event.target.closest('[data-remove-question]');

    if (removeQuestionButton) {
        const builder = removeQuestionButton.closest('[data-question-builder]');
        const card = removeQuestionButton.closest('[data-question-card]');

        card?.remove();

        if (builder) {
            refreshQuestionBuilder(builder);
        }
    }
});

document.querySelectorAll('[data-question-builder]').forEach(refreshQuestionBuilder);
