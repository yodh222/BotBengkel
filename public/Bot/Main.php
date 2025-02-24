<?php
// File: execute.php

// Muat konfigurasi dan model-model yang diperlukan
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../app/models/BotToken.php';
require_once __DIR__ . '/../../app/models/LogMessage.php';
require_once __DIR__ . '/../../app/models/Notification.php';
require_once __DIR__ . '/../../app/models/Pelanggan.php';
require_once __DIR__ . '/../../app/models/Tbpos.php';
require_once __DIR__ . '/Whatsva.php';

/**
 * Fungsi untuk menuliskan log ke file BotLog.log.
 *
 * @param string $message Pesan log yang akan dituliskan.
 */
function logActivity($message)
{
    $logText = "[" . date("Y-m-d H:i:s") . "] " . $message . PHP_EOL;
    file_put_contents(__DIR__ . '/BotLog.log', $logText, FILE_APPEND);
}

/**
 * Ambil template pesan dari model Notification.
 *
 * @return array
 */
function getMessageTemplate()
{
    logActivity("Fetching message templates...");
    $templates = Notification::all();
    logActivity("Retrieved " . count($templates) . " message templates.");
    return $templates;
}

/**
 * Proses pesan dengan mengganti placeholder {NmPemilik}, {NoPolisi}, dan {Tanggal}
 * dengan nilai aktual.
 *
 * @param string $noPolisi
 * @param string $namaPelanggan
 * @param string $message
 * @return string
 */
function processMessage($noPolisi, $namaPelanggan, $message)
{
    logActivity("Processing message for customer '{$namaPelanggan}' with vehicle '{$noPolisi}'...");
    $current_date = date("d F Y");
    $processed_message = str_replace("{NmPemilik}", $namaPelanggan, $message);
    $processed_message = str_replace("{NoPolisi}", $noPolisi, $processed_message);
    $processed_message = str_replace("{Tanggal}", $current_date, $processed_message);
    logActivity("Processed message: " . $processed_message);
    return $processed_message;
}

/**
 * Ambil transaksi terbaru beserta informasi notifikasi.
 *
 * @return array Format: ['token' => <bot token>, 'data' => <array transaksi>]
 */
function newestTransaction()
{
    logActivity("Starting retrieval of latest transactions...");
    $notifications = Notification::allActive();
    $finalFiltered = [];

    foreach ($notifications as $notification) {
        logActivity("Processing notification '{$notification['judul']}' with interval {$notification['interval_days']} days.");
        // Ambil transaksi terbaru sesuai interval notifikasi (mengembalikan array of objects)
        $results = Tbpos::getNewestTransactionsWithNotifications($notification['interval_days']);
        logActivity("Retrieved " . count($results) . " transactions for notification '{$notification['judul']}'.");

        // Hapus duplikat berdasarkan KodePelanggan
        $uniqueResults = [];
        foreach ($results as $result) {
            $kode = $result->KodePelanggan;
            if (!isset($uniqueResults[$kode])) {
                $uniqueResults[$kode] = $result;
            }
        }
        logActivity("After deduplication, " . count($uniqueResults) . " unique transactions remain.");

        // Ambil data pelanggan dan log message secara batch
        $kodePelangganList = array_keys($uniqueResults);
        $pelangganData = Pelanggan::findByKodeIn($kodePelangganList);
        $noFakturList = array_map(function ($item) {
            return $item->NoFaktur;
        }, array_values($uniqueResults));
        $logMessages = LogMessage::getMessagesByNoFakturIn($noFakturList);

        foreach ($uniqueResults as $item) {
            $pelanggan = isset($pelangganData[$item->KodePelanggan]) ? $pelangganData[$item->KodePelanggan] : null;
            $item->nomor_hp = isset($pelanggan['NoTelp']) ? $pelanggan['NoTelp'] : null;
            $item->nama = isset($pelanggan['NmPemilik']) ? $pelanggan['NmPemilik'] : null;
            $item->tipe_pesan = [];

            // Pastikan log pesan yang sudah ada tidak mengulangi notifikasi yang sama
            $existingMessages = isset($logMessages[$item->NoFaktur]) ? $logMessages[$item->NoFaktur] : [];
            if (!in_array($notification['judul'], $existingMessages)) {
                $item->tipe_pesan[] = $notification['judul'];
            }

            // Simpan transaksi hanya jika nomor HP valid dan terdapat tipe pesan
            if (!empty($item->nomor_hp) && $item->nomor_hp !== '0' && !empty($item->tipe_pesan)) {
                $finalFiltered[] = $item;
            }
        }
    }
    $bot_token = BotToken::getToken();
    logActivity("Retrieved bot token and final filtered transactions: " . count($finalFiltered));
    return ['token' => $bot_token, 'data' => array_values($finalFiltered)];
}

/**
 * Simpan log pesan ke database dan tuliskan ke file log.
 *
 * @param string $nofaktur
 * @param string $nomor_hp
 * @param string $pesan
 * @param string $status
 * @param mixed  $pesan_error (default: "None")
 */
function storeLogMessage($nofaktur, $nomor_hp, $pesan, $status, $pesan_error = "None")
{
    logActivity("Storing log for NoFaktur '{$nofaktur}' with status '{$status}'.");
    try {
        $logMessage = new LogMessage();
        $result = $logMessage::store([
            "NoFaktur"    => $nofaktur,
            "pesan"       => $pesan,
            "nomor_hp"    => $nomor_hp,
            "status"      => $status,
            "pesan_error" => $pesan_error
        ]);
        $logText = "NoFaktur: {$nofaktur}, Nomor HP: {$nomor_hp}, Pesan: {$pesan}, Status: {$status}, Pesan Error: {$pesan_error}";
        logActivity("Database log result: " . ($result ? "Success" : "Failure") . ". " . $logText);
        echo "Log untuk NoFaktur {$nofaktur} " . ($result ? "berhasil" : "gagal") . " disimpan." . PHP_EOL;
    } catch (Exception $e) {
        logActivity("Error storing log for NoFaktur '{$nofaktur}': " . $e->getMessage());
        echo "Error menyimpan log untuk NoFaktur {$nofaktur}: " . $e->getMessage() . PHP_EOL;
    }
}

/**
 * Fungsi utama untuk mengirim pesan.
 */
function send()
{
    logActivity("Starting send() function...");
    $data = newestTransaction();
    $bot_token = $data['token'];
    $transactions = $data['data'];
    $messageTemplates = getMessageTemplate();

    // Buat instance Whatsva untuk mengirim pesan
    $whatsva = new Whatsva();
    logActivity("Total transactions to process: " . count($transactions));

    if (count($transactions) > 0) {
        foreach ($transactions as $item) {
            $nofaktur      = isset($item->NoFaktur) ? $item->NoFaktur : null;
            $noPolisi      = isset($item->NoPolisi) ? $item->NoPolisi : null;
            $namaPelanggan = isset($item->nama) ? $item->nama : null;
            $tipePesanList = isset($item->tipe_pesan) ? $item->tipe_pesan : [];

            logActivity("Processing transaction NoFaktur '{$nofaktur}' for customer '{$namaPelanggan}'.");

            foreach ($tipePesanList as $tipe) {
                // Filter template pesan berdasarkan judul
                $messageFiltered = array_filter($messageTemplates, function ($x) use ($tipe) {
                    return isset($x['judul']) && $x['judul'] === $tipe;
                });
                $messageFiltered = array_values($messageFiltered);

                if (!empty($messageFiltered)) {
                    $pesan = processMessage($noPolisi, $namaPelanggan, $messageFiltered[0]['pesan']);
                    $nomor_hp = isset($item->nomor_hp) ? $item->nomor_hp : null;

                    logActivity("Sending message for NoFaktur '{$nofaktur}' to nomor HP '{$nomor_hp}': " . $pesan);

                    $send_response = json_decode($whatsva->sendMessageText($bot_token, $nomor_hp, $pesan), true);
                    if (isset($send_response['success']) && $send_response['success'] === true) {
                        storeLogMessage($nofaktur, $nomor_hp, $messageFiltered[0]['judul'], 'sent');
                        logActivity("Message sent successfully for NoFaktur '{$nofaktur}'.");
                    } else {
                        $errorMsg = isset($send_response['message']) ? $send_response['message'] : '';
                        storeLogMessage($nofaktur, $nomor_hp, $messageFiltered[0]['judul'], 'failed', $errorMsg);
                        logActivity("Failed to send message for NoFaktur '{$nofaktur}'. Error: {$errorMsg}");
                    }
                } else {
                    logActivity("Message template for '{$tipe}' not found for NoFaktur '{$nofaktur}'.");
                    echo "Template pesan untuk {$tipe} tidak ditemukan." . PHP_EOL;
                }
            }
        }
    } else {
        logActivity("No transactions available.");
        echo "Tidak ada data transaksi." . PHP_EOL;
    }
}

send();
