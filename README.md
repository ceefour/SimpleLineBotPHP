# Aplikasi iBeacon-LINE untuk Inventaris Laboratorium

## Server

Server berfungsi untuk mengelola komunikasi dengan user menggunakan LINE Bot.

Konfigurasi:

1. Masukkan konfigurasi di file `.env` (lihat file `.env.dev` sebagai template)

Deploy:

    cf push labinv

## Android App

Android app berfungsi untuk mendeteksi iBeacon lalu mengirim event ke server agar dapat diolah.
