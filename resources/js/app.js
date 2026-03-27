import './bootstrap';
import Chart from 'chart.js/auto';

const completionChartElement = document.querySelector('[data-completion-chart]');

if (completionChartElement) {
    const completed = Number(completionChartElement.dataset.completed ?? 0);
    const remaining = Number(completionChartElement.dataset.remaining ?? 0);
    const total = completed + remaining;

    new Chart(completionChartElement, {
        type: 'doughnut',
        data: {
            labels: ['完了', '未完了'],
            datasets: [
                {
                    data: [completed, remaining],
                    backgroundColor: ['#3f6b4f', '#b85c38'],
                    borderColor: ['#f5f8f1', '#fffaf1'],
                    borderWidth: 4,
                    hoverOffset: 10,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '70%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        usePointStyle: true,
                        padding: 18,
                        color: '#20201d',
                        font: {
                            family: 'Hiragino Sans, Yu Gothic, sans-serif',
                            size: 13,
                        },
                    },
                },
                tooltip: {
                    callbacks: {
                        label(context) {
                            const value = context.raw;
                            const percent = total > 0 ? Math.round((value / total) * 100) : 0;

                            return `${context.label}: ${value}件 (${percent}%)`;
                        },
                    },
                },
            },
        },
    });
}

const todoFilterRoot = document.querySelector('[data-todo-filters]');

if (todoFilterRoot) {
    const keywordInput = todoFilterRoot.querySelector('[data-filter-keyword]');
    const statusSelect = todoFilterRoot.querySelector('[data-filter-status]');
    const emptyState = document.querySelector('[data-filter-empty]');
    const todoItems = [...document.querySelectorAll('[data-todo-item]')];

    const applyTodoFilters = () => {
        const keyword = (keywordInput?.value ?? '').trim().toLowerCase();
        const status = statusSelect?.value ?? 'all';
        let visibleCount = 0;

        for (const item of todoItems) {
            const title = (item.dataset.todoTitle ?? '').toLowerCase();
            const itemStatus = item.dataset.todoStatus ?? 'open';
            const matchesKeyword = keyword === '' || title.includes(keyword);
            const matchesStatus = status === 'all' || itemStatus === status;
            const isVisible = matchesKeyword && matchesStatus;

            item.hidden = !isVisible;

            if (isVisible) {
                visibleCount += 1;
            }
        }

        if (emptyState) {
            emptyState.hidden = visibleCount !== 0;
        }
    };

    keywordInput?.addEventListener('input', applyTodoFilters);
    statusSelect?.addEventListener('change', applyTodoFilters);

    applyTodoFilters();
}
