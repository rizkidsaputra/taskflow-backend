<?php
/**
 * Utility untuk response JSON yang konsisten
 */

/**
 * Mengirim response sukses
 */
function sendSuccess($data = null, $message = "Success", $code = 200) {
    http_response_code($code);
    $response = ["success" => true, "message" => $message];
    if ($data !== null) {
        $response["data"] = $data;
    }
    echo json_encode($response);
    exit();
}

/**
 * Mengirim response error
 */
function sendError($message = "Error", $code = 400, $errors = null) {
    http_response_code($code);
    $response = ["success" => false, "message" => $message];
    if ($errors !== null) {
        $response["errors"] = $errors;
    }
    echo json_encode($response);
    exit();
}

/**
 * Validasi input JSON
 */
function getJsonInput() {
    $input = json_decode(file_get_contents("php://input"), true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        sendError("Invalid JSON format", 400);
    }
    return $input;
}
?>
