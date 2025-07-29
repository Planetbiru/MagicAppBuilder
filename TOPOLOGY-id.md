### Topologi MagicAppBuilder dan Aplikasi

![](https://github.com/Planetbiru/MagicAppBuilder/blob/main/MagicAppBuilder.svg)

MagicAppBuilder bekerja dengan **minimal dua database** secara bersamaan:

1. **Database platform** – digunakan oleh MagicAppBuilder untuk menyimpan data internal seperti:

   * Data pengguna dan autentikasi
   * Workspace
   * Metadata aplikasi dan konfigurasi sistem
2. **Database aplikasi** – digunakan oleh aplikasi yang sedang dikembangkan, berisi:

   * Data entitas aplikasi
   * Data yang akan digunakan dan dimodifikasi oleh pengguna akhir saat aplikasi berjalan

MagicAppBuilder dan aplikasi hasil buatannya dapat dijalankan pada **server web yang sama maupun berbeda**. Namun, untuk kemudahan pengelolaan dan instalasi, **disarankan untuk menempatkannya di server yang sama**. Untuk melakukannya, pastikan direktori MagicAppBuilder dan direktori aplikasi berada dalam **document root** yang sama dari web server.


### Peran dan Fungsi MagicAppBuilder

MagicAppBuilder memiliki beberapa tanggung jawab penting dalam pengembangan aplikasi, antara lain:

* **Membuat dan memodifikasi file modul** dan file entitas berdasarkan konfigurasi pengguna.
* **Menghasilkan file konfigurasi aplikasi** yang sesuai dengan lingkungan pengembangan.
* **Membuat dan memperbarui struktur database aplikasi** secara otomatis berdasarkan entitas.
* **Mengelola data penting dalam aplikasi**, seperti:

  * Membuat akun pengguna awal
  * Membuat struktur menu
  * Mengatur hak akses dan peran pengguna


### Akses ke Workspace dan Parsing Entitas

MagicAppBuilder memiliki akses penuh terhadap seluruh **workspace**, termasuk:

* Source code aplikasi
* Aset statis (gambar, skrip, stylesheet, dll.)
* File-file entitas yang didefinisikan pengguna

File entitas tersebut akan **diparsing menjadi objek** yang digunakan untuk berbagai tujuan, seperti:

* Membuat dan menyinkronkan struktur database
* Membuat diagram relasi antar entitas
* Menghasilkan dokumentasi otomatis
* Menyediakan dasar untuk validasi, filter, dan operasi CRUD otomatis

