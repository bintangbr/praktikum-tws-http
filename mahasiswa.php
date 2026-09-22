<?php
header("Content-Type: application/json");
require "data.php";
$method = $_SERVER['REQUEST_METHOD'];
if ($method === "GET") {
    if (isset($_GET['id'])) {
        $id = (int) $_GET['id'];
        foreach ($students as $student) {
            if ($student['id'] === $id) {
                http_response_code(200);
                echo json_encode($student);
                exit;
            }
        }
        http_response_code(404);
        echo json_encode(["error" => "Data mahasiswa tidak ditemukan"]);
        exit;
    }
    http_response_code(200);
    echo json_encode($students);
    exit;
}
if ($method === "POST") {
    $input = json_decode(file_get_contents("php://input"), true);
    if (
        !isset($input['nim']) || !isset($input['name']) ||
        !isset($input['major'])
    ) {
        http_response_code(400);
        echo json_encode(["error" => "Field nim, name, dan major wajib diisi"]);
        exit;
    }
    $newId = count($students) + 1;
    $newStudent = [
        "id" => $newId,
        "nim" => $input['nim'],
        "name" => $input['name'],
        "major" => $input['major'],
    ];
    http_response_code(201);
    header("Location: /mahasiswa.php?id=$newId");
    echo json_encode($newStudent);
    exit;
}
if ($method === "PUT") {
    $input = json_decode(file_get_contents("php://input"), true);
    $id = isset($_GET['id']) ? (int) $_GET['id'] : (isset($input['id']) ? (int) $input['id'] : null);

    if ($id === null) {
        http_response_code(400);
        echo json_encode(["error" => "ID mahasiswa wajib disertakan"]);
        exit;
    }

    $foundIndex = null;
    foreach ($students as $index => $student) {
        if ($student['id'] === $id) {
            $foundIndex = $index;
            break;
        }
    }

    if ($foundIndex === null) {
        http_response_code(404);
        echo json_encode(["error" => "Data mahasiswa tidak ditemukan"]);
        exit;
    }

    if (
        !isset($input['nim']) || !isset($input['name']) ||
        !isset($input['major'])
    ) {
        http_response_code(400);
        echo json_encode(["error" => "Field nim, name, dan major wajib diisi"]);
        exit;
    }

    $students[$foundIndex]['nim'] = $input['nim'];
    $students[$foundIndex]['name'] = $input['name'];
    $students[$foundIndex]['major'] = $input['major'];

    http_response_code(200);
    echo json_encode($students[$foundIndex]);
    exit;
}
if ($method === "DELETE") {
    $input = json_decode(file_get_contents("php://input"), true);
    $id = isset($_GET['id']) ? (int) $_GET['id'] : (isset($input['id']) ? (int) $input['id'] : null);

    if ($id === null) {
        http_response_code(400);
        echo json_encode(["error" => "ID mahasiswa wajib disertakan"]);
        exit;
    }

    $foundIndex = null;
    foreach ($students as $index => $student) {
        if ($student['id'] === $id) {
            $foundIndex = $index;
            break;
        }
    }

    if ($foundIndex === null) {
        http_response_code(404);
        echo json_encode(["error" => "Data mahasiswa tidak ditemukan"]);
        exit;
    }

    unset($students[$foundIndex]);
    $students = array_values($students);

    http_response_code(200);
    echo json_encode(["message" => "Data mahasiswa berhasil dihapus"]);
    exit;
}
http_response_code(405);
echo json_encode(["error" => "Method tidak didukung"]);