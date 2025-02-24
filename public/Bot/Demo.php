<?php
// Pastikan file Whatsva.php sudah tersedia dan dimuat jika diperlukan.
require_once 'Whatsva.php'; // Uncomment atau sesuaikan path-nya jika belum dimasukkan.

function demoMessage($args)
{
    $noPolisi      = isset($args['noPolisi']) ? $args['noPolisi'] : '';
    $namaPelanggan = isset($args['nama']) ? $args['nama'] : '';
    $nomorHp      = isset($args['nomor_hp']) ? $args['nomor_hp'] : '';
    $pesanTemplate = isset($args['pesan']) ? $args['pesan'] : '';
    $tokenBot      = isset($args['token']) ? $args['token'] : '';

    try {
        $pesan = processMessage($noPolisi, $namaPelanggan, $pesanTemplate);

        // Buat instance Whatsva dan kirim pesan
        $whatsva = new Whatsva();
        $sendResponse = json_decode($whatsva->sendMessageText($tokenBot, $nomorHp, $pesan), true);
        // Cek respons pengiriman
        if (isset($sendResponse['success']) && $sendResponse['success'] === true) {
            echo "Pesan berhasil dikirim." . PHP_EOL;
        } else {
            $errorMsg = isset($sendResponse['message']) ? $sendResponse['message'] : 'Unknown error';
            echo "Pesan gagal dikirim ke {$nomorHp}. Error: {$errorMsg}" . PHP_EOL;
        }
    } catch (Exception $e) {
        echo "Terjadi kesalahan: " . $e->getMessage() . PHP_EOL;
    }
}

function processMessage($noPolisi, $namaPelanggan, $pesanTemplate)
{
    $current_date = date("d F Y");
    // Ganti placeholder dalam template pesan
    $pesan = str_replace("{NoPolisi}", $noPolisi, $pesanTemplate);
    $pesan = str_replace("{NmPemilik}", $namaPelanggan, $pesan);
    $pesan = str_replace("{Tanggal}", $current_date, $pesan);
    return $pesan;
}

// Ambil parameter dari command line
$options = getopt("", ["noPolisi:", "nama:", "nomor_hp:", "pesan:", "token:"]);

if (!empty($options)) {
    demoMessage($options);
} else {
    echo "Parameter tidak lengkap. Gunakan: \n";
    echo "php script.php --noPolisi=AD1234XY --nama='John Doe' --nomor_hp=628123456789 --pesan='Halo {NamaPelanggan}, kendaraan Anda dengan nomor polisi {NoPolisi} sudah waktunya diservis.' --token=BOT_TOKEN_123\n";
}
