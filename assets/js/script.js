$(document).ready(function() {
    // Toggle sidebar mobile
    $('.toggle-sidebar').click(function(e) {
        e.stopPropagation();
        $('.sidebar').toggleClass('show');
    });

    $(document).click(function(e) {
        if ($(window).width() <= 768) {
            if (!$(e.target).closest('.sidebar').length && !$(e.target).closest('.toggle-sidebar').length) {
                $('.sidebar').removeClass('show');
            }
        }
    });

    // Cek NIK duplikat via AJAX
    let nikTimer;
    $('#nik').on('input', function() {
        clearTimeout(nikTimer);
        const nik = $(this).val().trim();
        const id = $('#penduduk_id').val() || '';
        const $msg = $('#nik-duplicate-msg');

        if (nik.length >= 10) {
            nikTimer = setTimeout(function() {
                $.ajax({
                    url: '../admin/penduduk/cek_nik.php',
                    method: 'POST',
                    data: { nik: nik, id: id },
                    dataType: 'json',
                    success: function(res) {
                        if (res.duplicate) {
                            $msg.text('NIK ' + nik + ' sudah terdaftar!').addClass('show');
                            $('#nik').addClass('is-invalid');
                        } else {
                            $msg.removeClass('show');
                            $('#nik').removeClass('is-invalid');
                        }
                    }
                });
            }, 500);
        } else {
            $msg.removeClass('show');
            $('#nik').removeClass('is-invalid');
        }
    });

    // Auto-hide flash messages
    setTimeout(function() {
        $('.flash-message').fadeOut(500);
    }, 5000);

    $('.close-flash').click(function() {
        $(this).closest('.flash-message').fadeOut(300);
    });

    // Confirm delete
    $('.btn-delete').click(function(e) {
        if (!confirm('Apakah Anda yakin ingin menghapus data ini?')) {
            e.preventDefault();
        }
    });

    // Initialize DataTables
    if ($.fn.DataTable) {
        $('.table-datatable').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json'
            },
            pageLength: 25,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Semua"]]
        });
    }
});
