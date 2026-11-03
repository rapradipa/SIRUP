<?php

return [
    // Kunci API disimpan di .env (jangan di-commit). Dapatkan di https://console.groq.com
    'key' => env('GROQ_API_KEY'),
    // Nama model bisa diganti lewat .env jika Groq mengubah daftar model visinya.
    'model' => env('GROQ_VISION_MODEL', 'qwen/qwen3.8-27b'),
    // "none" = mode instruct (cepat, tanpa penalaran panjang). Kosongkan untuk memakai bawaan model.
    'effort' => env('GROQ_REASONING_EFFORT', 'none'),
    'url' => env('GROQ_URL', 'https://api.groq.com/openai/v1/chat/completions'),
    'timeout' => (int) env('GROQ_TIMEOUT', 30),
];
