<?php
/**
 * Endpoint untuk logout member
 * POST /api/logout
 */

require_once '../../config/cors.php';
require_once '../../utils/response.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendError("Method not allowed", 405);
}

session_start();
session_destroy();

sendSuccess(null, "Logout berhasil");
?>
