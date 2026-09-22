<?php

header("Content-Type: application/json");

$case = $_GET['case'] ?? 'ok';

switch ($case) {
    case 'bad-request':
        http_response_code(400);
        echo json_encode(["error" => "Permintaan tidak valid"]);
        break;

    case 'not-found':
        http_response_code(404);
        echo json_encode(["error" => "Data tidak ditemukan"]);
        break;

    case 'server-error':
        http_response_code(500);
        echo json_encode(["error" => "Terjadi kesalahan pada server"]);
        break;

    default:
        http_response_code(200);
        echo json_encode(["message" => "Semua baik-baik saja"]);
        break;
}