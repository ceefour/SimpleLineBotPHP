# Aplikasi iBeacon-LINE untuk Inventaris Laboratorium

## Server

Server berfungsi untuk mengelola komunikasi dengan user menggunakan LINE Bot.

Konfigurasi:

1. Masukkan konfigurasi di file `.env` (lihat file `.env.dev` sebagai template)

Deploy:

    cd server
    cf push labinv

Struktur webhook message yang dikirim oleh LINE bila di-chat oleh Hendy:

    Array\n(\n    [type] => message\n    [replyToken] => e77b8352844...\n    [source] => Array\n        (\n            [userId] => U11d4438ecbcd135f2f85c7faf4cb7a5d\n            [type] => user\n        )\n\n    [timestamp] => 1488795992612\n    [message] => Array\n        (\n            [type] => text\n            [id] => 5741347249705\n            [text] => hai\n        )\n\n)\n\n

Bila di-chat oleh Nurul:

    {"events":[{"type":"message","replyToken":"b26b9bc816d6...","source":{"userId":"U651ad6a7b141fb5517e3e2f0ae2deae9","type":"user"},"timestamp":1488796362960,"message":{"type":"text","id":"5741376043806","text":"Nurul"}}]}

### Perintah

**/pushadmin**

Mengirim pesan custom ke para admin. Perintah ini digunakan untuk uji coba pengiriman pesan dari server ke akun LINE para admin.

    http://labinv.mybluemix.net/pushadmin?message=coba+push

## Android App

Android app berfungsi untuk mendeteksi iBeacon lalu mengirim event ke server agar dapat diolah.
