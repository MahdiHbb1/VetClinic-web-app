// Main JavaScript file for VetClinic Management System

// Real-time search functionality
function initializeSearch(inputId, tableId) {
    const searchInput = document.getElementById(inputId);
    if (!searchInput) return;

    searchInput.addEventListener('keyup', function() {
        const searchTerm = this.value.toLowerCase();
        const table = document.getElementById(tableId);
        if (!table) return;

        const rows = table.querySelectorAll('tbody tr');
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(searchTerm) ? '' : 'none';
        });
    });
}

// Sweet Alert confirmations
function confirmDelete(url, itemName) {
    Swal.fire({
        title: 'Konfirmasi Hapus',
        text: `Apakah Anda yakin ingin menghapus ${itemName}?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#EF4444',
        cancelButtonColor: '#6B7280',
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = url;
        }
    });
}

// Show success message
function showSuccess(message) {
    Swal.fire({
        icon: 'success',
        title: 'Berhasil!',
        text: message,
        timer: 3000,
        showConfirmButton: false
    });
}

// Show error message
function showError(message) {
    Swal.fire({
        icon: 'error',
        title: 'Error!',
        text: message
    });
}

// Format currency to IDR
function formatRupiah(number) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR'
    }).format(number);
}

// Add medicine row in prescription form
let medicineCounter = 1;
function addMedicineRow(medicineOptions) {
    medicineCounter++;
    const container = document.getElementById('medicineContainer');
    if (!container) return;

    const row = document.createElement('div');
    row.className = 'grid grid-cols-6 gap-2 mb-2';
    row.id = `medicine-${medicineCounter}`;
    
    row.innerHTML = `
        <select name="obat_id[]" class="form-input" onchange="updatePrice(this, ${medicineCounter})">
            <option value="">Pilih Obat</option>
            ${medicineOptions}
        </select>
        <input type="text" name="dosis[]" placeholder="Dosis" class="form-input">
        <input type="text" name="frekuensi[]" placeholder="Frekuensi" class="form-input">
        <input type="number" name="jumlah[]" placeholder="Qty" class="form-input" onchange="calculateSubtotal(${medicineCounter})">
        <input type="text" name="subtotal[]" id="subtotal-${medicineCounter}" readonly class="form-input bg-gray-100">
        <button type="button" onclick="removeMedicineRow(${medicineCounter})" class="btn btn-danger">
            <i class="fas fa-trash"></i>
        </button>
    `;
    
    container.appendChild(row);
}

// Remove medicine row
function removeMedicineRow(id) {
    const row = document.getElementById(`medicine-${id}`);
    if (row) {
        row.remove();
        calculateTotal();
    }
}

// Calculate subtotal for medicine row
function calculateSubtotal(rowId) {
    const row = document.getElementById(`medicine-${rowId}`);
    if (!row) return;

    const price = parseFloat(row.dataset.price) || 0;
    const quantity = parseInt(row.querySelector('input[name="jumlah[]"]').value) || 0;
    const subtotal = price * quantity;
    
    const subtotalInput = row.querySelector(`#subtotal-${rowId}`);
    if (subtotalInput) {
        subtotalInput.value = formatRupiah(subtotal);
        calculateTotal();
    }
}

// Calculate total from all subtotals
function calculateTotal() {
    let total = 0;
    document.querySelectorAll('[id^="subtotal-"]').forEach(input => {
        const value = input.value.replace(/[^0-9]/g, '');
        total += parseInt(value) || 0;
    });
    
    const totalElement = document.getElementById('totalAmount');
    if (totalElement) {
        totalElement.textContent = formatRupiah(total);
    }
}

// Initialize DataTables
function initializeDataTable(tableId, options = {}) {
    const defaultOptions = {
        pageLength: 10,
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json'
        },
        responsive: true
    };

    const table = document.getElementById(tableId);
    if (table) {
        return new DataTable(table, { ...defaultOptions, ...options });
    }
}

// Initialize on document load
document.addEventListener('DOMContentLoaded', function() {
    // Initialize all DataTables
    const tables = document.querySelectorAll('.datatable');
    tables.forEach(table => {
        initializeDataTable(table.id);
    });

    // Initialize all search boxes
    const searchBoxes = document.querySelectorAll('[data-search-table]');
    searchBoxes.forEach(searchBox => {
        initializeSearch(searchBox.id, searchBox.dataset.searchTable);
    });
});

// Fetch pets by owner
function fetchPetsByOwner(ownerId) {
    if (!ownerId) return;

    fetch(`/vetclinic/api/get_pets.php?owner_id=${ownerId}`)
        .then(response => response.json())
        .then(data => {
            const petSelect = document.getElementById('pet_id');
            if (!petSelect) return;

            petSelect.innerHTML = '<option value="">Pilih Hewan</option>';
            data.forEach(pet => {
                petSelect.innerHTML += `
                    <option value="${pet.pet_id}">
                        ${pet.nama_hewan} (${pet.jenis})
                    </option>
                `;
            });
        })
        .catch(error => console.error('Error fetching pets:', error));
}

// Show/hide loading spinner
function toggleLoading(show = true) {
    const spinner = document.getElementById('loadingSpinner');
    if (spinner) {
        spinner.style.display = show ? 'flex' : 'none';
    }
}

// Handle file upload preview
function previewImage(input, previewId) {
    const preview = document.getElementById(previewId);
    if (!preview) return;

    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
        }
        reader.readAsDataURL(input.files[0]);
    }
}

// Toast notification system
function showToast(message, type = 'info', duration = 3000) {
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    
    const icons = {
        success: 'check-circle',
        error: 'times-circle',
        warning: 'exclamation-triangle',
        info: 'info-circle'
    };
    
    toast.innerHTML = `
        <i class="fas fa-${icons[type]} mr-2"></i>
        <span>${message}</span>
    `;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.style.animation = 'slideOutRight 0.3s ease-out';
        setTimeout(() => toast.remove(), 300);
    }, duration);
}

// Form validation
function validateForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return false;
    
    let isValid = true;
    const requiredFields = form.querySelectorAll('[required]');
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            isValid = false;
            field.classList.add('border-red-500');
            
            // Add error message if not exists
            if (!field.nextElementSibling || !field.nextElementSibling.classList.contains('error-message')) {
                const error = document.createElement('p');
                error.className = 'error-message text-red-500 text-sm mt-1';
                error.textContent = 'Field ini wajib diisi';
                field.parentNode.insertBefore(error, field.nextSibling);
            }
        } else {
            field.classList.remove('border-red-500');
            const error = field.nextElementSibling;
            if (error && error.classList.contains('error-message')) {
                error.remove();
            }
        }
    });
    
    return isValid;
}

// Auto-save form data to localStorage
function autoSaveForm(formId, key) {
    const form = document.getElementById(formId);
    if (!form) return;
    
    const inputs = form.querySelectorAll('input, textarea, select');
    
    inputs.forEach(input => {
        input.addEventListener('change', () => {
            const formData = new FormData(form);
            const data = Object.fromEntries(formData);
            localStorage.setItem(key, JSON.stringify(data));
        });
    });
}

// Restore form data from localStorage
function restoreForm(formId, key) {
    const form = document.getElementById(formId);
    if (!form) return;
    
    const savedData = localStorage.getItem(key);
    if (!savedData) return;
    
    const data = JSON.parse(savedData);
    
    Object.keys(data).forEach(name => {
        const input = form.querySelector(`[name="${name}"]`);
        if (input) {
            input.value = data[name];
        }
    });
}

// Clear saved form data
function clearSavedForm(key) {
    localStorage.removeItem(key);
}

// Print functionality
function printElement(elementId) {
    const element = document.getElementById(elementId);
    if (!element) return;
    
    const printWindow = window.open('', '', 'height=600,width=800');
    printWindow.document.write('<html><head><title>Print</title>');
    printWindow.document.write('<link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">');
    printWindow.document.write('<link href="/assets/css/style.css" rel="stylesheet">');
    printWindow.document.write('</head><body>');
    printWindow.document.write(element.innerHTML);
    printWindow.document.write('</body></html>');
    printWindow.document.close();
    
    setTimeout(() => {
        printWindow.print();
        printWindow.close();
    }, 250);
}

// Export table to CSV
function exportTableToCSV(tableId, filename = 'export.csv') {
    const table = document.getElementById(tableId);
    if (!table) return;
    
    let csv = [];
    const rows = table.querySelectorAll('tr');
    
    rows.forEach(row => {
        const cols = row.querySelectorAll('td, th');
        const rowData = [];
        
        cols.forEach(col => {
            rowData.push('"' + col.textContent.replace(/"/g, '""') + '"');
        });
        
        csv.push(rowData.join(','));
    });
    
    const csvContent = csv.join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    
    link.href = URL.createObjectURL(blob);
    link.download = filename;
    link.click();
}

// Debounce function for search inputs
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Initialize enhanced search with debounce
function initializeEnhancedSearch(inputId, searchFunction) {
    const searchInput = document.getElementById(inputId);
    if (!searchInput) return;
    
    const debouncedSearch = debounce(searchFunction, 300);
    searchInput.addEventListener('input', function() {
        debouncedSearch(this.value);
    });
}

// Check if element is in viewport
function isInViewport(element) {
    const rect = element.getBoundingClientRect();
    return (
        rect.top >= 0 &&
        rect.left >= 0 &&
        rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
        rect.right <= (window.innerWidth || document.documentElement.clientWidth)
    );
}

// Lazy load images
function lazyLoadImages() {
    const images = document.querySelectorAll('img[data-src]');
    
    images.forEach(img => {
        if (isInViewport(img)) {
            img.src = img.dataset.src;
            img.removeAttribute('data-src');
        }
    });
}

// Initialize lazy loading on scroll
window.addEventListener('scroll', debounce(lazyLoadImages, 100));
document.addEventListener('DOMContentLoaded', lazyLoadImages);