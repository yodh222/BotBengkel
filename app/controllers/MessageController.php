<?php
require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../Models/Notification.php';

class MessageController extends Controller
{
    public function index()
    {
        $this->view('messages-management');
    }

    // Method untuk mengambil notifikasi. Jika $id diberikan, ambil satu notifikasi; jika tidak, ambil semua.
    public function notification($id = null)
    {
        header('Content-Type: application/json');

        if ($id !== null) {
            $notification = Notification::find($id);
            if ($notification) {
                http_response_code(200);
                echo json_encode($notification, JSON_PRETTY_PRINT);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Notification not found'], JSON_PRETTY_PRINT);
            }
        } else {
            $notifications = Notification::all();
            http_response_code(200);
            echo json_encode(['data' => $notifications], JSON_PRETTY_PRINT);
        }
    }

    // Fungsi untuk menyimpan notifikasi baru
    public function store()
    {
        header('Content-Type: application/json');

        $judul = isset($_POST['judul']) ? $_POST['judul'] : null;
        $pesan = isset($_POST['pesan']) ? $_POST['pesan'] : null;
        $interval_days = isset($_POST['interval_days']) ? $_POST['interval_days'] : null;

        if (!$judul || !$pesan || !$interval_days) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing required fields'], JSON_PRETTY_PRINT);
            return;
        }

        $data = [
            'judul' => $judul,
            'pesan' => $pesan,
            'interval_days' => $interval_days,
            'status' => 'active'
        ];

        $insertId = Notification::store($data);
        if ($insertId) {
            http_response_code(201);
            echo json_encode(['message' => 'Notification created', 'id' => $insertId], JSON_PRETTY_PRINT);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to create notification'], JSON_PRETTY_PRINT);
        }
    }

    // Fungsi untuk mengupdate notifikasi (edit)
    // Parameter $id diambil dari URL dan data update dari $_POST
    public function update($id)
    {
        header('Content-Type: application/json');

        $judul = isset($_POST['judul']) ? $_POST['judul'] : null;
        $pesan = isset($_POST['pesan']) ? $_POST['pesan'] : null;
        $interval_days = isset($_POST['interval_days']) ? $_POST['interval_days'] : null;

        if (!$judul || !$pesan || !$interval_days) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing required fields'], JSON_PRETTY_PRINT);
            return;
        }

        $data = [
            'judul' => $judul,
            'pesan' => $pesan,
            'interval_days' => $interval_days
        ];

        $updated = Notification::updateRecord($id, $data);
        if ($updated) {
            http_response_code(200);
            echo json_encode(['message' => 'Notification updated'], JSON_PRETTY_PRINT);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to update notification'], JSON_PRETTY_PRINT);
        }
    }

    // Fungsi untuk menghapus notifikasi
    public function delete($id)
    {
        header('Content-Type: application/json');

        $deleted = Notification::deleteRecord($id);
        if ($deleted) {
            http_response_code(200);
            echo json_encode(['message' => 'Notification deleted'], JSON_PRETTY_PRINT);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to delete notification'], JSON_PRETTY_PRINT);
        }
    }

    // Fungsi untuk mengaktifkan atau menonaktifkan notifikasi
    public function toggleStatus($id)
    {
        header('Content-Type: application/json');

        $notification = Notification::find($id);
        if (!$notification) {
            http_response_code(404);
            echo json_encode(['error' => 'Notification not found'], JSON_PRETTY_PRINT);
            return;
        }

        // Jika status saat ini 'active', maka set ke 'inactive', dan sebaliknya
        $newStatus = ($notification['status'] === 'active') ? 'inactive' : 'active';
        $updated = Notification::updateRecord($id, ['status' => $newStatus]);
        if ($updated) {
            http_response_code(200);
            echo json_encode(['message' => "Notification status updated to {$newStatus}"], JSON_PRETTY_PRINT);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to update notification status'], JSON_PRETTY_PRINT);
        }
    }
}