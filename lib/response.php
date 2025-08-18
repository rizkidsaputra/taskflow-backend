<?php
// lib/response.php

/**
 * Kirim JSON response standar
 */
function json_response($data = [], $status = 200) {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(
        $data,
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT
    );
    exit;
}

/**
 * Response sukses
 */
function json_ok($data = [], $message = 'OK') {
    $payload = [
        'success' => true,
        'message' => $message,
    ];

    // pastikan data digabung dengan aman
    if (is_array($data)) {
        $payload = array_merge($payload, $data);
    } else {
        $payload['data'] = $data;
    }

    json_response($payload, 200);
}

/**
 * Response error
 */
function json_error($message = 'Error', $status = 400, $extra = []) {
    $payload = [
        'success' => false,
        'message' => $message,
    ];

    if (is_array($extra)) {
        $payload = array_merge($payload, $extra);
    }

    json_response($payload, $status);
}

/**
 * Ambil input JSON dari body request
 */
function json_input() {
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        return [];
    }

    return is_array($data) ? $data : [];
}