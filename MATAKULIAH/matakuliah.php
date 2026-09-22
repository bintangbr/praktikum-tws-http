<?php
header("Content-Type: application/json; charset=utf-8");
header("X-API-Version: 1.0");
header("X-Resource: matakuliah");

require "data_matakuliah.php";

function respond(int $code, array $body): void
{
    http_response_code($code);
    echo json_encode($body, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

function findIndex(array $list, int $id): int|false
{
    foreach ($list as $i => $item) {
        if ($item['id'] === $id) {
            return $i;
        }
    }
    return false;
}

function validatePayload(?array $input): ?string
{
    if ($input === null) {
        return "Body request tidak valid atau bukan JSON.";
    }
    $required = ['kode', 'nama', 'sks', 'semester'];
    foreach ($required as $field) {
        if (!isset($input[$field]) || $input[$field] === '') {
            return "Field '$field' wajib diisi.";
        }
    }
    if (!is_int($input['sks']) || $input['sks'] < 1 || $input['sks'] > 6) {
        return "Field 'sks' harus berupa bilangan bulat antara 1 dan 6.";
    }
    if (!is_int($input['semester']) || $input['semester'] < 1 || $input['semester'] > 8) {
        return "Field 'semester' harus berupa bilangan bulat antara 1 dan 8.";
    }
    return null;
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    if (isset($_GET['id'])) {
        $id  = (int) $_GET['id'];
        $idx = findIndex($matakuliah, $id);
        if ($idx === false) {
            respond(404, ["error" => "Mata kuliah dengan id=$id tidak ditemukan."]);
        }
        respond(200, $matakuliah[$idx]);
    }

    if (isset($_GET['semester'])) {
        $sem     = (int) $_GET['semester'];
        $results = array_values(
            array_filter($matakuliah, fn($mk) => $mk['semester'] === $sem)
        );
        respond(200, [
            "semester" => $sem,
            "total"    => count($results),
            "data"     => $results,
        ]);
    }

    respond(200, [
        "total" => count($matakuliah),
        "data"  => $matakuliah,
    ]);
}

if ($method === 'POST') {
    $input = json_decode(file_get_contents("php://input"), true);
    $err   = validatePayload($input);
    if ($err !== null) {
        respond(400, ["error" => $err]);
    }

    foreach ($matakuliah as $mk) {
        if (strtoupper($mk['kode']) === strtoupper($input['kode'])) {
            respond(409, [
                "error" => "Kode mata kuliah '{$input['kode']}' sudah digunakan."
            ]);
        }
    }

    $newId = count($matakuliah) > 0
        ? max(array_column($matakuliah, 'id')) + 1
        : 1;

    $newMK = [
        "id"       => $newId,
        "kode"     => strtoupper(trim($input['kode'])),
        "nama"     => trim($input['nama']),
        "sks"      => (int) $input['sks'],
        "semester" => (int) $input['semester'],
    ];

    header("Location: /matakuliah.php?id=$newId");
    respond(201, [
        "message" => "Mata kuliah berhasil ditambahkan.",
        "data"    => $newMK,
    ]);
}

if ($method === 'PUT') {
    $input = json_decode(file_get_contents("php://input"), true);
    $id    = isset($_GET['id'])    ? (int) $_GET['id']
           : (isset($input['id']) ? (int) $input['id']
           : null);

    if ($id === null) {
        respond(400, ["error" => "Parameter 'id' wajib disertakan melalui query string atau body."]);
    }

    $idx = findIndex($matakuliah, $id);
    if ($idx === false) {
        respond(404, ["error" => "Mata kuliah dengan id=$id tidak ditemukan."]);
    }

    $err = validatePayload($input);
    if ($err !== null) {
        respond(400, ["error" => $err]);
    }

    foreach ($matakuliah as $i => $mk) {
        if ($i !== $idx && strtoupper($mk['kode']) === strtoupper($input['kode'])) {
            respond(409, [
                "error" => "Kode mata kuliah '{$input['kode']}' sudah digunakan oleh id={$mk['id']}."
            ]);
        }
    }

    $matakuliah[$idx] = array_merge($matakuliah[$idx], [
        "kode"     => strtoupper(trim($input['kode'])),
        "nama"     => trim($input['nama']),
        "sks"      => (int) $input['sks'],
        "semester" => (int) $input['semester'],
    ]);

    respond(200, [
        "message" => "Mata kuliah berhasil diperbarui.",
        "data"    => $matakuliah[$idx],
    ]);
}

if ($method === 'DELETE') {
    $input = json_decode(file_get_contents("php://input"), true);
    $id    = isset($_GET['id'])    ? (int) $_GET['id']
           : (isset($input['id']) ? (int) $input['id']
           : null);

    if ($id === null) {
        respond(400, ["error" => "Parameter 'id' wajib disertakan melalui query string atau body."]);
    }

    $idx = findIndex($matakuliah, $id);
    if ($idx === false) {
        respond(404, ["error" => "Mata kuliah dengan id=$id tidak ditemukan."]);
    }

    $deleted = $matakuliah[$idx];
    unset($matakuliah[$idx]);

    respond(200, [
        "message" => "Mata kuliah berhasil dihapus.",
        "deleted" => $deleted,
    ]);
}

header("Allow: GET, POST, PUT, DELETE");
respond(405, ["error" => "HTTP method '$method' tidak didukung untuk resource ini."]);
