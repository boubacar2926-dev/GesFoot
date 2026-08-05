// ============================================================
// Football Club Platform — Main JS
// ============================================================

document.addEventListener('DOMContentLoaded', () => {

    // Sidebar toggle (mobile)
    const toggler = document.getElementById('sidebarToggler');
    const sidebar  = document.querySelector('.sidebar');
    const overlay  = document.querySelector('.sidebar-overlay');

    if (toggler && sidebar) {
        toggler.addEventListener('click', () => {
            sidebar.classList.toggle('open');
            overlay && overlay.classList.toggle('d-none');
        });
    }
    if (overlay) {
        overlay.addEventListener('click', () => {
            sidebar && sidebar.classList.remove('open');
            overlay.classList.add('d-none');
        });
    }

    // Auto-dismiss alerts after 5s
    document.querySelectorAll('.alert.alert-dismissible').forEach(el => {
        setTimeout(() => {
            const bsAlert = bootstrap.Alert.getOrCreateInstance(el);
            if (bsAlert) bsAlert.close();
        }, 5000);
    });

    // Confirm delete buttons
    document.querySelectorAll('[data-confirm]').forEach(el => {
        el.addEventListener('click', e => {
            const msg = el.dataset.confirm || 'Confirmer cette action ?';
            if (!confirm(msg)) e.preventDefault();
        });
    });

    // Mark active sidebar link
    const links = document.querySelectorAll('.sidebar nav a');
    const path  = window.location.pathname;
    links.forEach(link => {
        if (link.getAttribute('href') && path.includes(link.getAttribute('href').split('?')[0])) {
            link.classList.add('active');
        }
    });

    // Preview image upload
    document.querySelectorAll('input[type="file"][data-preview]').forEach(input => {
        input.addEventListener('change', () => {
            const target = document.getElementById(input.dataset.preview);
            if (!target) return;
            const file = input.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = e => target.src = e.target.result;
                reader.readAsDataURL(file);
            }
        });
    });

    // DataTable init (if DataTables loaded)
    if (typeof DataTable !== 'undefined') {
        document.querySelectorAll('.datatable').forEach(table => {
            new DataTable(table, {
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/fr-FR.json'
                },
                pageLength: 25,
                order: []
            });
        });
    }

    // Tooltips
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
        new bootstrap.Tooltip(el);
    });
});
