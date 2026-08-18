/**
 * Data Visualisation Dashboard (Chart.js)
 * Renders verified observations stats charts.
 */
document.addEventListener('DOMContentLoaded', () => {
    const ctx = document.getElementById('speciesChart');
    if (!ctx) return; // Exit if no chart element exists on the page

    // Fetch values pre-populated by PHP
    const labels = window.chartLabels || [];
    const dataValues = window.chartData || [];

    if (labels.length === 0 || dataValues.length === 0) {
        // Render fallback text if no data exists
        const container = ctx.parentNode;
        container.innerHTML = '<p style="text-align:center; color: var(--color-text-muted); padding-top: 100px;">No statistics data available.</p>';
        return;
    }

    // Initialize Chart.js Bar Chart
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Observations Count',
                data: dataValues,
                backgroundColor: 'rgba(74, 112, 60, 0.75)',   /* Moss Green (var(--color-secondary) with opacity) */
                borderColor: 'rgba(30, 63, 32, 1)',          /* Deep Forest Green (var(--color-primary)) */
                borderWidth: 2,
                borderRadius: 4,
                hoverBackgroundColor: 'rgba(30, 63, 32, 0.9)',
                hoverBorderColor: 'rgba(18, 38, 19, 1)'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false // Hide dataset legend since it's self-explanatory
                },
                tooltip: {
                    backgroundColor: '#1a1917',
                    titleFont: { family: "'Outfit', sans-serif", size: 13 },
                    bodyFont: { family: "'Outfit', sans-serif", size: 12 },
                    padding: 10,
                    cornerRadius: 4,
                    borderColor: '#cfa844',
                    borderWidth: 1
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1,
                        color: '#6b665f',
                        font: { family: "'Outfit', sans-serif", size: 11 }
                    },
                    grid: {
                        color: '#e6e2db'
                    }
                },
                x: {
                    ticks: {
                        color: '#6b665f',
                        font: { family: "'Outfit', sans-serif", size: 11, weight: '500' }
                    },
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
});
