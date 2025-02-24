<?php
// app/controllers/LogMessageController.php

require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../Models/LogMessage.php';
require_once __DIR__ . '/../Models/Pelanggan.php';
require_once __DIR__ . '/../Models/Tbpos.php';

class LogMessageController extends Controller
{
    /**
     * Menampilkan daftar log message dalam format JSON.
     * Endpoint: GET /log-messages
     */
    public function index()
    {
        header('Content-Type: application/json');

        // Ambil seluruh log message
        $logs = LogMessage::all();  // Mengembalikan array asosiatif

        $data = [];
        foreach ($logs as $log) {
            // Kolom "action": jika status 'failed' tampilkan tombol "Resend", jika tidak tampilkan badge "Sent"
            if ($log['status'] === 'failed') {
                $action = '<button class="btn btn-primary btn-sm resend-btn" data-id="' . htmlspecialchars($log['id']) . '">Resend</button>';
            } else {
                $action = '<span class="badge bg-success">Sent</span>';
            }

            // Kolom "nama_pelanggan": Ambil NoFaktur dari log, cari data melalui model Tbpos, lalu ambil pelanggan berdasarkan KodePelanggan.
            $nofaktur = $log['NoFaktur'];
            $tbpos = Tbpos::findByNoFaktur($nofaktur);
            if ($tbpos) {
                $pelanggan = Pelanggan::findByKode($tbpos['KodePelanggan']);
                $nama_pelanggan = $pelanggan ? $pelanggan['NmPemilik'] : 'Pelanggan Tidak Ditemukan';
            } else {
                $nama_pelanggan = 'Pelanggan Tidak Ditemukan';
            }

            // Tambahkan kolom tambahan ke data log
            $log['action'] = $action;
            $log['nama_pelanggan'] = $nama_pelanggan;
            $data[] = $log;
        }

        // Kembalikan respon dalam format JSON, misalnya sesuai format DataTables
        echo json_encode(['data' => $data], JSON_PRETTY_PRINT);
    }

    /**
     * Menyimpan log message baru.
     * Endpoint: POST /log-messages/store
     */
    public function store()
    {
        header('Content-Type: application/json');

        // Validasi input secara manual
        $NoFaktur = isset($_POST['NoFaktur']) ? trim($_POST['NoFaktur']) : null;
        $pesan = isset($_POST['pesan']) ? trim($_POST['pesan']) : null;
        $nomor_hp = isset($_POST['nomor_hp']) ? trim($_POST['nomor_hp']) : null;
        $status = isset($_POST['status']) ? trim($_POST['status']) : null;
        $pesan_error = isset($_POST['pesan_error']) ? trim($_POST['pesan_error']) : null;

        if (!$NoFaktur || !$pesan || !$nomor_hp || !$status) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Missing required fields'], JSON_PRETTY_PRINT);
            return;
        }

        // Pastikan status hanya 'sent' atau 'failed'
        if (!in_array($status, ['sent', 'failed'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid status'], JSON_PRETTY_PRINT);
            return;
        }

        $data = [
            'NoFaktur' => $NoFaktur,
            'pesan' => $pesan,
            'nomor_hp' => $nomor_hp,
            'status' => $status,
            'pesan_error' => $pesan_error,
        ];

        $insertId = LogMessage::store($data);
        if ($insertId) {
            http_response_code(201);
            echo json_encode(['success' => true, 'message' => 'Log message stored.', 'data' => ['id' => $insertId]], JSON_PRETTY_PRINT);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to store log message.'], JSON_PRETTY_PRINT);
        }
    }
}
