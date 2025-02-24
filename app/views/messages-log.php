<?php
// messages-log.php

// Set judul halaman
$title = 'Log Message';

// Mulai output buffering untuk konten utama
ob_start();
?>
<div class="card">
    <div class="card-body">
        <h3 class="mb-4">Log Messages</h3>
        <table id="logMessageTable" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Customer Name</th>
                    <th>Title Message</th>
                    <th>Phone Number</th>
                    <th>Status</th>
                    <th>Error Message</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
</div>
<?php
// Simpan seluruh konten ke variabel $content
$content = ob_get_clean();

// Mulai output buffering untuk bagian skrip
ob_start();
?>
<script>
$(document).ready(function() {
    const table = $('#logMessageTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: '/api/log-messages', // URL endpoint untuk mengambil data log messages
        columns: [{
                data: 'id'
            },
            {
                data: 'nama_pelanggan'
            },
            {
                data: 'pesan'
            },
            {
                data: 'nomor_hp'
            },
            {
                data: 'status'
            },
            {
                data: 'pesan_error'
            },
            {
                data: 'action',
                name: 'action',
                orderable: false,
                searchable: false,
                render: function(data, type, row) {
                    if (row.status === 'failed') {
                        return `<button class="btn btn-primary btn-sm resend-btn" data-id="${row.id}">Resend</button>`;
                    }
                    return `<span class="badge bg-success">Sent</span>`;
                }
            }
        ]
    });

    // Fungsi Resend
    $('#logMessageTable').on('click', '.resend-btn', function() {
        const id = $(this).data('id');

        $.ajax({
            url: `/api/log-messages/${id}/resend`,
            type: 'POST',
            success: function(response) {
                console.log(response);
                table.ajax.reload();
            },
            error: function(xhr, status, error) {
                const errorMessage =
                    `Error: ${xhr.status} ${xhr.statusText}\nResponse: ${xhr.responseText}`;
                alert(errorMessage);
                console.log(errorMessage);
            }
        });
    });
});
</script>
<?php
// Simpan skrip ke variabel $scripts
$scripts = ob_get_clean();

// Sertakan layout utama (misalnya, base.php) yang akan memanggil $title, $content, dan $scripts
include 'base.php';