<?php
class Config {
    // Database
    const DB_HOST = 'localhost';
    const DB_NAME = 'namadb';
    const DB_USER = 'namauser';
    const DB_PASS = 'password'; // Isi password database Anda

    // API & Website
    const API_URL = 'URL API';
    const API_KEY = 'API';
    const SITE_NAME = 'DramaFlix';
    const SITE_DESC = 'Nonton Drama Asia Sub Indo Gratis';
    
    // Settings
    const FREE_EPS_LIMIT = 100; // Batas episode gratis
    
    // ... config maintenance ...
    const MAINTENANCE_MODE = false; // Ubah ke 'true' jika ingin menutup web
}
