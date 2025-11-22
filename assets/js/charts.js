// Chart configurations for VetClinic Management System

// Revenue Chart Configuration
function initializeRevenueChart(canvasId, data) {
    const ctx = document.getElementById(canvasId).getContext('2d');
    
    return new Chart(ctx, {
        type: 'bar',
        data: {
            labels: data.months,
            datasets: [{
                label: 'Pendapatan (Rp)',
                data: data.values,
                backgroundColor: 'rgba(59, 130, 246, 0.5)',
                borderColor: 'rgba(59, 130, 246, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                title: {
                    display: true,
                    text: 'Grafik Pendapatan 6 Bulan Terakhir'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'Rp ' + value.toLocaleString('id-ID');
                        }
                    }
                }
            }
        }
    });
}

// Diagnoses Distribution Chart
function initializeDiagnosesChart(canvasId, data) {
    const ctx = document.getElementById(canvasId).getContext('2d');
    
    return new Chart(ctx, {
        type: 'pie',
        data: {
            labels: data.labels,
            datasets: [{
                data: data.values,
                backgroundColor: [
                    'rgba(59, 130, 246, 0.5)',
                    'rgba(16, 185, 129, 0.5)',
                    'rgba(239, 68, 68, 0.5)',
                    'rgba(245, 158, 11, 0.5)',
                    'rgba(139, 92, 246, 0.5)'
                ],
                borderColor: [
                    'rgba(59, 130, 246, 1)',
                    'rgba(16, 185, 129, 1)',
                    'rgba(239, 68, 68, 1)',
                    'rgba(245, 158, 11, 1)',
                    'rgba(139, 92, 246, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                },
                title: {
                    display: true,
                    text: 'Distribusi Diagnosa'
                }
            }
        }
    });
}

// Pet Weight History Chart
function initializePetWeightChart(canvasId, data) {
    const ctx = document.getElementById(canvasId).getContext('2d');
    
    return new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.dates,
            datasets: [{
                label: 'Berat (kg)',
                data: data.weights,
                borderColor: 'rgba(59, 130, 246, 1)',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false
                },
                title: {
                    display: true,
                    text: 'Riwayat Berat Badan'
                }
            },
            scales: {
                y: {
                    beginAtZero: false,
                    title: {
                        display: true,
                        text: 'Berat (kg)'
                    }
                }
            }
        }
    });
}

// Service Popularity Chart
function initializeServiceChart(canvasId, data) {
    const ctx = document.getElementById(canvasId).getContext('2d');
    
    return new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: data.labels,
            datasets: [{
                data: data.values,
                backgroundColor: [
                    'rgba(59, 130, 246, 0.5)',
                    'rgba(16, 185, 129, 0.5)',
                    'rgba(239, 68, 68, 0.5)',
                    'rgba(245, 158, 11, 0.5)',
                    'rgba(139, 92, 246, 0.5)'
                ],
                borderColor: [
                    'rgba(59, 130, 246, 1)',
                    'rgba(16, 185, 129, 1)',
                    'rgba(239, 68, 68, 1)',
                    'rgba(245, 158, 11, 1)',
                    'rgba(139, 92, 246, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                },
                title: {
                    display: true,
                    text: 'Layanan Terpopuler'
                }
            }
        }
    });
}

// Doctor Performance Chart
function initializeDoctorPerformanceChart(canvasId, data) {
    const ctx = document.getElementById(canvasId).getContext('2d');
    
    return new Chart(ctx, {
        type: 'bar',
        data: {
            labels: data.doctors,
            datasets: [{
                label: 'Pasien Ditangani',
                data: data.patients,
                backgroundColor: 'rgba(59, 130, 246, 0.5)',
                borderColor: 'rgba(59, 130, 246, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false
                },
                title: {
                    display: true,
                    text: 'Kinerja Dokter'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Jumlah Pasien'
                    }
                }
            }
        }
    });
}

// Initialize charts when data is available
function initializeCharts(data) {
    if (data.revenue && document.getElementById('revenueChart')) {
        initializeRevenueChart('revenueChart', data.revenue);
    }
    
    if (data.diagnoses && document.getElementById('diagnosesChart')) {
        initializeDiagnosesChart('diagnosesChart', data.diagnoses);
    }
    
    if (data.petWeight && document.getElementById('petWeightChart')) {
        initializePetWeightChart('petWeightChart', data.petWeight);
    }
    
    if (data.services && document.getElementById('serviceChart')) {
        initializeServiceChart('serviceChart', data.services);
    }
    
    if (data.doctorPerformance && document.getElementById('doctorPerformanceChart')) {
        initializeDoctorPerformanceChart('doctorPerformanceChart', data.doctorPerformance);
    }
}

// Fetch chart data and initialize
function loadChartData() {
    fetch('/vetclinic/api/dashboard_stats.php')
        .then(response => response.json())
        .then(data => {
            initializeCharts(data);
        })
        .catch(error => console.error('Error loading chart data:', error));
}