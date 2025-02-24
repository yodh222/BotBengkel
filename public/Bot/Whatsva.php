<?php

class Whatsva
{
    /**
     * Mengembalikan URL dasar untuk layanan Whatsva.
     *
     * @return string
     */
    public function wsUrl(): string
    {
        return "https://whatsva.id";
    }

    /**
     * Mengirim pesan teks.
     *
     * @param string $instanceKey
     * @param string $jid
     * @param string $message
     * @return string Response dari server.
     * @throws RuntimeException jika terjadi error saat request.
     */
    public function sendMessageText(string $instanceKey, string $jid, string $message): string
    {
        $data = [
            "instance_key" => $instanceKey,
            "jid"          => $jid,
            "message"      => $message,
        ];

        return $this->curlData($this->wsUrl() . "/api/sendMessageText", $data);
    }

    /**
     * Melakukan request POST ke endpoint yang ditentukan dengan data yang diberikan.
     *
     * @param string $url Endpoint URL.
     * @param array  $data Data yang akan dikirim.
     * @return string Response dari server.
     * @throws RuntimeException jika terjadi error pada cURL.
     */
    private function curlData(string $url, array $data): string
    {
        $payload = json_encode($data, JSON_THROW_ON_ERROR);

        $ch = curl_init();
        if ($ch === false) {
            throw new RuntimeException("Gagal menginisialisasi cURL.");
        }

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Referer: http://localhost:8088'
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $result = curl_exec($ch);
        if ($result === false) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new RuntimeException("cURL error: " . $error);
        }

        curl_close($ch);
        return $result;
    }
}
