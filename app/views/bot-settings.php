<?php
// bot-settings.php

$title = 'Bot Settings';

// Ambil bot token dari database menggunakan model BotToken
require_once __DIR__ . '/../Models/BotToken.php';
$botToken = BotToken::getToken();

ob_start();
?>
<div id="alert-container"></div>
<div class="card">
    <div class="card-body">
        <!-- Bot Token -->
        <div class="mb-5">
            <h2 class="h5 mb-3">Bot Token</h2>
            <div class="input-group mb-3">
                <input type="text" class="form-control" disabled value="<?= htmlspecialchars($botToken) ?>"
                    aria-describedby="basic-addon2">
                <button type="button" class="btn btn-secondary mb-0" id="edit-token-button"
                    data-bs-target="#editTokenModal" data-bs-toggle="modal">Edit</button>
            </div>
        </div>

        <!-- Modal Edit Token -->
        <div class="modal fade" id="editTokenModal" tabindex="-1" aria-labelledby="editTokenModalLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editTokenModalLabel">Edit Bot Token</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="editTokenForm" method="POST" action="/api/bot/updateToken">
                            <!-- CSRF tidak disertakan -->
                            <div class="mb-3">
                                <label for="new-bot-token" class="form-label">New Bot Token</label>
                                <input type="text" class="form-control" id="new-bot-token" name="new_bot_token"
                                    required>
                            </div>
                            <button type="submit" class="btn btn-primary">Save Token</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Demo Send Message -->
        <div>
            <h2 class="h5 mb-3">Demo: Send Message</h2>
            <form id="sendMessageForm">
                <div class="mb-3">
                    <label for="customer-name" class="form-label">Customer Name</label>
                    <input type="text" name="customer_name" id="customer-name" class="form-control"
                        placeholder="Enter Customer Name" required>
                </div>
                <div class="mb-3">
                    <label for="police-number" class="form-label">Police Number</label>
                    <input type="text" name="police_number" id="police-number" class="form-control"
                        placeholder="Enter Police Number" required>
                </div>
                <div class="mb-3">
                    <label for="phone" class="form-label">Phone Number</label>
                    <input type="text" name="phone" id="phone" class="form-control"
                        placeholder="Enter phone number (ex: 08123456789)" required>
                </div>
                <div class="mb-3">
                    <label for="message" class="form-label">Message</label><br>
                    <button type="button" class="btn btn-primary btn-sm" data-insert="{NmPemilik}">Nama
                        Pelanggan</button>
                    <button type="button" class="btn btn-primary btn-sm" data-insert="{NoPolisi}">No Polisi</button>
                    <button type="button" class="btn btn-primary btn-sm" data-insert="{Tanggal}">Tanggal</button>
                    <textarea name="message" id="message" rows="4" class="form-control" placeholder="Enter your message"
                        required></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Send Message</button>
            </form>
        </div>
    </div>
</div>
<?php
// Simpan seluruh konten ke variabel $content
$content = ob_get_clean();

ob_start();
?>
<script>
$(document).ready(function() {
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

    $('#sendMessageForm').on('submit', function(e) {
        e.preventDefault();
        const formData = {
            customer_name: $('#customer-name').val(),
            police_number: $('#police-number').val(),
            phone: $('#phone').val(),
            message: $('#message').val()
        };
        $.ajax({
            url: '/demo',
            method: 'POST',
            data: formData,
            success: function(response) {
                showAlert(response.message);
                console.log(response);
            },
            error: function(xhr) {
                console.log(xhr.responseJSON.message);
                showAlert('Error: ' + xhr.responseJSON.message, 'danger');
            }
        });
    });

    $(document).on('click', '[data-insert]', function() {
        const inputPesan = $('#message');
        const stringToAdd = $(this).data('insert');
        if (inputPesan.length > 0) {
            const currentText = inputPesan.val();
            const cursorPosition = inputPesan[0].selectionStart;
            const updatedText = currentText.slice(0, cursorPosition) + stringToAdd + currentText.slice(
                cursorPosition);
            inputPesan.val(updatedText);
        }
    });
});
</script>
<?php
$scripts = ob_get_clean();
include 'base.php';