<?php
// messages-management.php

// Set judul halaman
$title = 'Messages Management';

// Mulai output buffering untuk konten utama
ob_start();
?>
<button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createNotificationModal">
    Create Message
</button>
<div id="alert-container"></div>
<div class="my-4">
    <div class="row row-cols-1 row-cols-md-3 g-4" data-masonry='{"percentPosition": true }' id="card-container">
    </div>
</div>

<!-- Modal Create Notification -->
<div class="modal fade" id="createNotificationModal" tabindex="-1" aria-labelledby="createNotificationModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createNotificationModalLabel">Create New Message</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="createNotificationForm" method="POST" action="/api/notifications/store">
                    <!-- CSRF tidak disertakan -->
                    <div class="mb-3">
                        <label for="judul" class="form-label">Judul</label>
                        <input type="text" class="form-control" id="judul" name="judul" required>
                    </div>
                    <div class="mb-3">
                        <label for="pesan" class="form-label">Pesan</label><br>
                        <button type="button" class="btn btn-primary btn-sm" data-insert="{NmPemilik}">Nama
                            Pelanggan</button>
                        <button type="button" class="btn btn-primary btn-sm" data-insert="{NoPolisi}">No Polisi</button>
                        <button type="button" class="btn btn-primary btn-sm" data-insert="{Tanggal}">Tanggal</button>
                        <textarea class="form-control" id="pesan" name="pesan" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="interval_days" class="form-label">Interval (Hari)</label>
                        <input type="number" class="form-control" id="interval_days" name="interval_days" required>
                        <div class="form-text" id="basic-addon4">1 Bulan = 30 Hari</div>
                    </div>
                    <button type="submit" class="btn btn-primary">Save</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Edit Notification -->
<div class="modal fade" id="editNotificationModal" tabindex="-1" aria-labelledby="editNotificationModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editNotificationModalLabel">Edit Message</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editNotificationForm" method="POST" action="">
                    <!-- CSRF tidak disertakan -->
                    <input type="hidden" id="editNotificationId" name="id">
                    <div class="mb-3">
                        <label for="editJudul" class="form-label">Judul</label>
                        <input type="text" class="form-control" id="editJudul" name="judul" required>
                    </div>
                    <div class="mb-3">
                        <label for="pesan" class="form-label">Pesan</label><br>
                        <button type="button" class="btn btn-primary btn-sm" data-insert="{NmPemilik}">Nama
                            Pelanggan</button>
                        <button type="button" class="btn btn-primary btn-sm" data-insert="{NoPolisi}">No Polisi</button>
                        <button type="button" class="btn btn-primary btn-sm" data-insert="{Tanggal}">Tanggal</button>
                        <textarea class="form-control" id="editPesan" name="pesan" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="editIntervalDays" class="form-label">Interval (Hari)</label>
                        <input type="number" class="form-control" id="editIntervalDays" name="interval_days" required>
                        <div class="form-text" id="basic-addon4">1 Bulan = 30 Hari</div>
                    </div>
                    <button type="submit" class="btn btn-primary">Update</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Konfirmasi -->
<div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmDeleteModalLabel">Konfirmasi Hapus</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Apakah Anda yakin ingin menghapus data ini?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Hapus</button>
            </div>
        </div>
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

        // Fungsi untuk mengambil dan menampilkan data notifikasi
        window.generateCards = function() {
            $.ajax({
                url: '/api/notifications',
                method: 'GET',
                success: function(data) {
                    const $grid = $('#card-container').masonry({
                        itemSelector: '.col',
                        percentPosition: true
                    });

                    // Bersihkan grid terlebih dahulu
                    $grid.masonry('remove', $grid.find('.col')).masonry('layout');

                    let elements = [];
                    data.data.forEach(function(notification) {
                        var pesanFormatted = notification.pesan.replace(/\r\n/g, '<br>');
                        var interval_days = Math.floor(notification.interval_days / 30);
                        let interval;

                        if (interval_days > 0) {
                            if (interval_days % 30 === 0) {
                                interval = `${interval_days} Bulan`;
                            } else {
                                interval =
                                    `${interval_days} Bulan ${notification.interval_days % 30} Hari`;
                            }
                        } else {
                            interval = `${notification.interval_days} Hari`;
                        }

                        var cardHtml = `
                        <div class="col">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">${notification.judul}</h5>
                                    <p class="card-text">${pesanFormatted}</p>
                                    <ul class="list-unstyled">
                                        <li><strong>Interval Pengiriman:</strong> ${interval}</li>
                                        <li><strong>Status:</strong> ${notification.status == 'active' ? '<span class="badge bg-success">Aktif</span>' : '<span class="badge bg-danger">Nonaktif</span>' }</li>
                                    </ul>
                                    <div class="mt-auto">
                                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#editNotificationModal" onclick="editNotification(${notification.id})">Edit</button>
                                        <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#confirmDeleteModal" onclick="deleteNotification(${notification.id})">Delete</button>
                                        <button class="btn ${notification.status == 'active' ? 'btn-danger' : 'btn-success'}" onclick="statusNotification(${notification.id}, '${notification.status}')">${notification.status == 'active' ? 'Deactivate' : 'Activate'}</button>
                                    </div>
                                </div>
                            </div>
                        </div>`;
                        const $element = $(cardHtml);
                        elements.push($element[0]);
                    });

                    $grid.append(elements);
                    $grid.imagesLoaded(function() {
                        $grid.masonry('appended', elements).masonry('layout');
                    });
                },
            });
        };

        generateCards();

        // Create notification
        $('#createNotificationForm').on('submit', function(e) {
            e.preventDefault();
            var formData = $(this).serialize();
            $.ajax({
                url: '/api/notifications/store',
                method: 'POST',
                data: formData,
                success: function(data) {
                    $('#createNotificationModal').modal('hide');
                    generateCards();
                    showAlert(data.message, 'success');
                    $('#createNotificationForm')[0].reset();
                },
                error: function(xhr) {
                    showAlert(xhr.responseJSON.message, 'danger');
                }
            });
        });

        // Edit notification: ambil data notifikasi untuk di-edit
        window.editNotification = function(notificationId) {
            $.ajax({
                url: `/api/notifications/${notificationId}`,
                method: 'GET',
                success: function(data) {
                    $('#editNotificationId').val(data.id);
                    $('#editJudul').val(data.judul);
                    $('#editPesan').val(data.pesan);
                    $('#editIntervalDays').val(data.interval_days);
                    $('#editNotificationModal').modal('show');
                },
            });
        };

        // Update notification
        $('#editNotificationForm').on('submit', function(e) {
            e.preventDefault();
            var formData = $(this).serialize();
            $.ajax({
                url: `/api/notifications/update/${$('#editNotificationId').val()}`,
                method: 'POST',
                data: formData,
                success: function(data) {
                    $('#editNotificationModal').modal('hide');
                    generateCards(); // Refresh notification cards
                    showAlert(data.message, 'success');
                },
                error: function(xhr) {
                    showAlert(xhr.responseJSON.message, 'danger');
                }
            });
        });

        // Show alert
        window.showAlert = function(message, type = 'success') {
            const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
            $('#alert-container').html(alertHtml);
            $('.alert').addClass('show').fadeIn('slow');
            setTimeout(function() {
                $('.alert').fadeOut('slow', function() {
                    $(this).remove();
                });
            }, 5000);
        };

        // Hapus notification
        let deleteItemId = null;
        window.deleteNotification = function(notificationId) {
            deleteItemId = notificationId;
            $('#confirmDeleteModal').modal('show');
        };

        $('#confirmDeleteBtn').on('click', function() {
            if (deleteItemId) {
                $.ajax({
                    url: `/api/notifications/delete/${deleteItemId}`,
                    method: 'DELETE',
                    success: function(response) {
                        $('#confirmDeleteModal').modal('hide');
                        showAlert(response.message, 'success');
                        generateCards();
                        deleteItemId = null;
                    },
                    error: function(xhr) {
                        $('#confirmDeleteModal').modal('hide');
                        showAlert(xhr.responseJSON.message, 'danger');
                    }
                });
            }
        });

        // Toggle notification status (activate/deactivate)
        window.statusNotification = function(notificationId, currentStatus) {
            const newStatus = currentStatus == 'active' ? 'inactive' : 'active';
            $.ajax({
                url: `/api/notifications/toggle/${notificationId}`,
                method: 'PUT',
                data: {
                    status: newStatus
                },
                success: function(data) {
                    generateCards();
                    showAlert(data.message, 'success');
                },
            });
        };
    });

    $(document).on('click', '[data-insert]', function() {
        // Cari modal mana yang aktif
        let activeModal;
        let idField;
        if ($('#createNotificationModal').is(':visible')) {
            activeModal = '#createNotificationModal';
            idField = '#pesan';
        } else if ($('#editNotificationModal').is(':visible')) {
            activeModal = '#editNotificationModal';
            idField = '#editPesan';
        }
        // Pastikan modal aktif ditemukan
        if (activeModal) {
            const inputPesan = $(`${activeModal} ${idField}`);
            const stringToAdd = $(this).data('insert');
            if (inputPesan.length > 0) {
                const currentText = inputPesan.val();
                const cursorPosition = inputPesan[0].selectionStart;
                const updatedText = currentText.slice(0, cursorPosition) + stringToAdd + currentText.slice(
                    cursorPosition);
                inputPesan.val(updatedText);
            }
        }
    });
</script>
<?php
// Simpan skrip ke variabel $scripts
$scripts = ob_get_clean();

// Sertakan layout utama (misalnya, base.php)
include 'base.php';
