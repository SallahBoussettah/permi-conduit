/**
 * QCM Reports JavaScript
 * Handles chart visualizations for QCM reports
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize charts if Chart.js is available
    if (typeof Chart !== 'undefined') {
        initializeCharts();
    }
    
    /**
     * Initialize all charts on the page
     */
    function initializeCharts() {
        // Dashboard charts
        initializePaperStatsChart();
        initializeMonthlyStatsChart();
        
        // Candidate detail charts
        initializeCandidatePerformanceChart();
    }
    
    /**
     * Initialize the paper stats chart on the dashboard
     */
    function initializePaperStatsChart() {
        const paperStatsChart = document.getElementById('paper-stats-chart');
        if (!paperStatsChart) return;
        
        // Get data from the data attributes
        const chartData = JSON.parse(paperStatsChart.dataset.chartData || '{}');
        if (!chartData.labels || !chartData.datasets) return;
        
        new Chart(paperStatsChart.getContext('2d'), {
            type: 'bar',
            data: {
                labels: chartData.labels,
                datasets: [
                    {
                        label: 'Completed Exams',
                        data: chartData.datasets.completed,
                        backgroundColor: 'rgba(59, 130, 246, 0.6)',
                        borderColor: 'rgba(59, 130, 246, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Passed Exams',
                        data: chartData.datasets.passed,
                        backgroundColor: 'rgba(16, 185, 129, 0.6)',
                        borderColor: 'rgba(16, 185, 129, 1)',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        ticks: {
                            autoSkip: false,
                            maxRotation: 45,
                            minRotation: 45
                        }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });
    }
    
    /**
     * Initialize the monthly stats chart on the dashboard
     */
    function initializeMonthlyStatsChart() {
        const monthlyStatsChart = document.getElementById('monthly-stats-chart');
        if (!monthlyStatsChart) return;
        
        // Get data from the data attributes
        const chartData = JSON.parse(monthlyStatsChart.dataset.chartData || '{}');
        if (!chartData.labels || !chartData.datasets) return;
        
        new Chart(monthlyStatsChart.getContext('2d'), {
            type: 'line',
            data: {
                labels: chartData.labels,
                datasets: [
                    {
                        label: 'Total Exams',
                        data: chartData.datasets.total,
                        backgroundColor: 'rgba(79, 70, 229, 0.2)',
                        borderColor: 'rgba(79, 70, 229, 1)',
                        borderWidth: 2,
                        tension: 0.3,
                        fill: true
                    },
                    {
                        label: 'Passed Exams',
                        data: chartData.datasets.passed,
                        backgroundColor: 'rgba(16, 185, 129, 0.2)',
                        borderColor: 'rgba(16, 185, 129, 1)',
                        borderWidth: 2,
                        tension: 0.3,
                        fill: true
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });
    }
    
    /**
     * Initialize the candidate performance chart
     */
    function initializeCandidatePerformanceChart() {
        const candidatePerformanceChart = document.getElementById('candidate-performance-chart');
        if (!candidatePerformanceChart) return;
        
        // Get data from the data attributes
        const chartData = JSON.parse(candidatePerformanceChart.dataset.chartData || '{}');
        if (!chartData.labels || !chartData.datasets) return;
        
        new Chart(candidatePerformanceChart.getContext('2d'), {
            type: 'line',
            data: {
                labels: chartData.labels,
                datasets: [
                    {
                        label: 'Score (%)',
                        data: chartData.datasets.scores,
                        backgroundColor: 'rgba(79, 70, 229, 0.2)',
                        borderColor: 'rgba(79, 70, 229, 1)',
                        borderWidth: 2,
                        tension: 0.3,
                        fill: false,
                        pointBackgroundColor: function(context) {
                            const index = context.dataIndex;
                            const value = chartData.datasets.passed[index];
                            return value ? 'rgba(16, 185, 129, 1)' : 'rgba(239, 68, 68, 1)';
                        },
                        pointRadius: 6
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            callback: function(value) {
                                return value + '%';
                            }
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            afterLabel: function(context) {
                                const index = context.dataIndex;
                                const passed = chartData.datasets.passed[index];
                                return 'Status: ' + (passed ? 'Passed' : 'Failed');
                            }
                        }
                    }
                }
            }
        });
    }
    
    /**
     * Handle filter form submission
     */
    const filterForm = document.getElementById('filter-form');
    if (filterForm) {
        const sortSelect = filterForm.querySelector('select[name="sort"]');
        const directionSelect = filterForm.querySelector('select[name="direction"]');
        
        // Auto-submit form when sort or direction changes
        if (sortSelect) {
            sortSelect.addEventListener('change', function() {
                filterForm.submit();
            });
        }
        
        if (directionSelect) {
            directionSelect.addEventListener('change', function() {
                filterForm.submit();
            });
        }
    }
    
    /**
     * Handle export button
     */
    const exportBtn = document.getElementById('export-data-btn');
    if (exportBtn) {
        exportBtn.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Show loading state
            const originalText = exportBtn.innerHTML;
            exportBtn.innerHTML = '<svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Exporting...';
            exportBtn.disabled = true;
            
            // Redirect to export URL
            window.location.href = exportBtn.getAttribute('href');
            
            // Reset button after a delay
            setTimeout(function() {
                exportBtn.innerHTML = originalText;
                exportBtn.disabled = false;
            }, 3000);
        });
    }
    
    /**
     * Initialize tabs
     */
    const tabsContainer = document.querySelector('[role="tablist"]');
    if (tabsContainer) {
        const tabs = tabsContainer.querySelectorAll('[role="tab"]');
        const tabPanels = document.querySelectorAll('[role="tabpanel"]');
        
        tabs.forEach(tab => {
            tab.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Deactivate all tabs
                tabs.forEach(t => {
                    t.setAttribute('aria-selected', 'false');
                    t.classList.remove('border-indigo-500', 'text-indigo-600');
                    t.classList.add('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300');
                });
                
                // Hide all panels
                tabPanels.forEach(panel => {
                    panel.classList.add('hidden');
                });
                
                // Activate clicked tab
                this.setAttribute('aria-selected', 'true');
                this.classList.remove('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300');
                this.classList.add('border-indigo-500', 'text-indigo-600');
                
                // Show corresponding panel
                const panelId = this.getAttribute('aria-controls');
                const panel = document.getElementById(panelId);
                if (panel) {
                    panel.classList.remove('hidden');
                }
            });
        });
    }
}); 