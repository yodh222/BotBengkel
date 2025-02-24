<?php
// core/Controller.php

class Controller
{
    /**
     * Memanggil file view dan meneruskan data ke view.
     *
     * @param string $view Nama file view (tanpa ekstensi .php)
     * @param array $data Data yang ingin dikirim ke view
     */
    public function view($view, $data = [])
    {
        // Ekstrak array data agar variabel dapat langsung digunakan di view
        extract($data);
        // Memanggil file view yang terletak di folder app/views
        require_once __DIR__ . '/../app/views/' . $view . '.php';
    }
}
