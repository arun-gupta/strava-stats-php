import { Chart, registerables } from 'chart.js';

// Register Chart.js components
Chart.register(...registerables);

// Export for global use
window.Chart = Chart;

console.log('Strava Stats app initialized');
