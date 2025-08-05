document.addEventListener('DOMContentLoaded', function() {
    // Check if the necessary elements and data exist
    const openModalBtn = document.getElementById('open-graph-modal');
    const modal = document.getElementById('disturbance-graph-modal');
    
    // If there's no button, there's no data, so we stop.
    if (!openModalBtn || !modal) {
        return;
    }

    const closeModalBtn = modal.querySelector('.graph-modal-close');
    const chartCanvas = document.getElementById('disturbance-chart');
    let disturbanceChart = null; // To hold the chart instance

    // --- Function to render the chart ---
    function renderChart() {
        // Prevent re-rendering if the chart already exists
        if (disturbanceChart) {
            return;
        }

        // The data is passed from PHP using wp_localize_script
        const chartData = beforeafter_graph_data.points;
        const siteName = beforeafter_graph_data.sitename || 'Site';

        const ctx = chartCanvas.getContext('2d');
        disturbanceChart = new Chart(ctx, {
            type: 'bar', // A bar chart is good for yearly discrete data
            data: {
                labels: chartData.map(d => d.x), // Years on the X-axis
                datasets: [{
                    label: `Disturbed Area (ha) for ${siteName}`,
                    data: chartData.map(d => d.y), // Disturbance values on the Y-axis
                    backgroundColor: 'rgba(44, 82, 60, 0.7)', // A nice mossy green color
                    borderColor: 'rgba(44, 82, 60, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Area (hectares)'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Year'
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed.y !== null) {
                                    label += context.parsed.y.toFixed(2) + ' ha';
                                }
                                return label;
                            }
                        }
                    }
                }
            }
        });
    }

    // --- Modal Event Listeners ---
    openModalBtn.addEventListener('click', function() {
        modal.classList.add('is-visible');
        // Render the chart only when the modal is opened for the first time
        renderChart();
    });

    closeModalBtn.addEventListener('click', function() {
        modal.classList.remove('is-visible');
    });

    // Close modal if user clicks outside the content area
    modal.addEventListener('click', function(event) {
        if (event.target === modal) {
            modal.classList.remove('is-visible');
        }
    });

    // Close modal with the Escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape' && modal.classList.contains('is-visible')) {
            modal.classList.remove('is-visible');
        }
    });
});
