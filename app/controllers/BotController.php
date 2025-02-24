<?php
// app/controllers/BotController.php

require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../Models/BotToken.php';
require_once __DIR__ . '/../Models/Notification.php';
require_once __DIR__ . '/../Models/LogMessage.php';
require_once __DIR__ . '/../Models/Pelanggan.php';
require_once __DIR__ . '/../Models/Tbpos.php';

class BotController extends Controller
{
    protected $botPath;

    public function __construct()
    {
        // Misalnya, BASE_PATH didefinisikan di config.php
        // Gunakan BASE_PATH untuk path file (pastikan BASE_PATH sudah didefinisikan)
        $this->botPath = BASE_PATH . '/public/Bot/main.py';
    }

    /**
     * Update bot token.
     * Endpoint: POST /api/bot/updateToken
     */
    public function updateToken()
    {
        header('Content-Type: application/json');

        if (!isset($_POST['new_bot_token']) || trim($_POST['new_bot_token']) === '') {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'New bot token is required'], JSON_PRETTY_PRINT);
            return;
        }
        $new_bot_token = trim($_POST['new_bot_token']);

        // Gunakan model BotToken untuk update
        if (BotToken::updateToken($new_bot_token)) {
            // Redirect kembali ke halaman sebelumnya
            header("Location: " . $_SERVER['HTTP_REFERER']);
            exit;
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to update bot token'], JSON_PRETTY_PRINT);
        }
    }

    /**
     * Mengambil transaksi terbaru dan menentukan pesan yang perlu dikirim ulang.
     * Endpoint: GET /api/bot/newestTransaction
     */
    public function newestTransaction()
    {
        header('Content-Type: application/json');

        $notifications = Notification::allActive();
        $finalFiltered = [];

        foreach ($notifications as $notification) {
            $results = Tbpos::getNewestTransactionsWithNotifications($notification['interval_days']); // Array of objects

            $uniqueResults = [];
            foreach ($results as $result) {
                $kode = $result->KodePelanggan;
                if (!isset($uniqueResults[$kode])) {
                    $uniqueResults[$kode] = $result;
                }
            }

            $kodePelangganList = array_keys($uniqueResults);
            $pelangganData = Pelanggan::findByKodeIn($kodePelangganList);

            $noFakturList = array_map(function ($item) {
                return $item->NoFaktur;
            }, array_values($uniqueResults));
            $logMessages = LogMessage::getMessagesByNoFakturIn($noFakturList);

            foreach ($uniqueResults as $item) {
                $pelanggan = $pelangganData[$item->KodePelanggan] ?? null;
                $item->nomor_hp = $pelanggan['NoTelp'] ?? null;
                $item->nama = $pelanggan['NmPemilik'] ?? null;
                $item->tipe_pesan = [];

                $existingMessages = $logMessages[$item->NoFaktur] ?? [];
                if (!in_array($notification['judul'], $existingMessages)) {
                    $item->tipe_pesan[] = $notification['judul'];
                }

                if (!empty($item->nomor_hp) && $item->nomor_hp !== '0' && !empty($item->tipe_pesan)) {
                    $finalFiltered[] = $item;
                }
            }
        }

        $bot_token = BotToken::getToken();

        echo json_encode(['token' => $bot_token, 'data' => array_values($finalFiltered)], JSON_PRETTY_PRINT);
    }

    /**
     * Mengirim perintah demo ke skrip Python untuk demo pengiriman pesan.
     * Endpoint: POST /api/bot/testDemo
     */
    public function testDemo()
    {
        header('Content-Type: application/json');

        $customer_name = isset($_POST['customer_name']) ? trim($_POST['customer_name']) : null;
        $police_number = isset($_POST['police_number']) ? trim($_POST['police_number']) : null;
        $phone = isset($_POST['phone']) ? trim($_POST['phone']) : null;
        $message = isset($_POST['message']) ? trim($_POST['message']) : null;

        if (!$customer_name || !$police_number || !$phone || !$message) {
            http_response_code(400);
            echo json_encode(['message' => 'Missing required fields'], JSON_PRETTY_PRINT);
            return;
        }

        // Ambil token menggunakan model BotToken
        $token = BotToken::getToken();

        // Lokasi file Demo.php
        $demoScriptPath = __DIR__ . '/../../public/Bot/Demo.php'; // Ganti dengan path aktual file Demo.php

        $command = sprintf(
            'php %s --noPolisi %s --nama %s --nomor_hp %s --pesan %s --token %s',
            escapeshellarg($demoScriptPath),
            escapeshellarg($police_number),
            escapeshellarg($customer_name),
            escapeshellarg($phone),
            escapeshellarg($message),
            escapeshellarg($token)
        );

        exec($command, $output, $statusCode);

        if ($statusCode === 0 && in_array('Pesan berhasil dikirim.', $output)) {
            echo json_encode(['message' => 'Command executed successfully.']);
        } else {
            echo json_encode([
                'message' => 'Command failed.',
                'error' => implode("\n", $output),
                'status_code' => $statusCode,
            ], JSON_PRETTY_PRINT);
        }
    }
}
