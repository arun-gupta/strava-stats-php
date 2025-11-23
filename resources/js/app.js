import { Chart, registerables } from 'chart.js';
import ChartDataLabels from 'chartjs-plugin-datalabels';

// Register Chart.js components
Chart.register(...registerables, ChartDataLabels);

// Export for global use
window.Chart = Chart;

// Initialize activity chart when data is available
function initActivityChart() {
    console.log('initActivityChart called');
    console.log('window.activityData:', window.activityData);

    // Check if we have activity data and chart element
    if (typeof window.activityData === 'undefined' || !window.activityData) {
        console.log('No activity data available');
        return;
    }

    const chartElement = document.getElementById('activityChart');
    if (!chartElement) {
        console.log('Chart element not found');
        return;
    }

    console.log('Creating chart with data:', window.activityData);

    // Extract labels and data from activityData object
    const labels = Object.keys(window.activityData);
    const data = Object.values(window.activityData);
    const total = data.reduce((sum, val) => sum + val, 0);

    // Hide loading spinner and show chart
    const loadingElement = document.getElementById('chartLoading');
    if (loadingElement) {
        loadingElement.style.display = 'none';
    }
    chartElement.style.display = 'block';

    // Create pie chart
    new Chart(chartElement, {
        type: 'pie',
        data: {
            labels: labels,
            datasets: [{
                data: data,
                backgroundColor: [
                    '#fc4c02', // Strava orange
                    '#2d3748', // Dark gray
                    '#4299e1', // Blue
                    '#48bb78', // Green
                    '#ed8936', // Orange
                    '#9f7aea', // Purple
                    '#f56565', // Red
                    '#38b2ac', // Teal
                ],
                borderWidth: 2,
                borderColor: '#ffffff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 15,
                        font: {
                            size: 12
                        }
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.parsed || 0;
                            const percentage = ((value / total) * 100).toFixed(1);
                            return `${label}: ${value} (${percentage}%)`;
                        }
                    }
                },
                datalabels: {
                    color: '#ffffff',
                    font: {
                        weight: 'bold',
                        size: 14
                    },
                    formatter: function(value, context) {
                        const percentage = ((value / total) * 100).toFixed(1);
                        return `${value}\n(${percentage}%)`;
                    }
                }
            }
        }
    });

    console.log('Chart created successfully');
}

// Initialize example chart for empty state
function initExampleChart() {
    // Check if we have example data and chart element
    if (typeof window.exampleData === 'undefined' || !window.exampleData) {
        return;
    }

    const chartElement = document.getElementById('exampleChart');
    if (!chartElement) {
        return;
    }

    // Extract labels and data from exampleData object
    const labels = Object.keys(window.exampleData);
    const data = Object.values(window.exampleData);
    const total = data.reduce((sum, val) => sum + val, 0);

    // Create pie chart (without datalabels plugin for example)
    new Chart(chartElement, {
        type: 'pie',
        data: {
            labels: labels,
            datasets: [{
                data: data,
                backgroundColor: [
                    '#fc4c02', // Strava orange
                    '#2d3748', // Dark gray
                    '#4299e1', // Blue
                ],
                borderWidth: 2,
                borderColor: '#ffffff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 10,
                        font: {
                            size: 11
                        }
                    }
                },
                datalabels: {
                    display: false // Hide data labels on example chart
                }
            }
        }
    });
}

// Try to initialize immediately and also on DOMContentLoaded
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function() {
        initActivityChart();
        initExampleChart();
    });
} else {
    // DOM is already ready, call immediately
    initActivityChart();
    initExampleChart();
}

console.log('Strava Stats app initialized');
