<?php

require "data.php";

$accept = $_SERVER['HTTP_ACCEPT'] ?? 'application/json';

if (strpos($accept, 'application/xml') !== false) {
    header("Content-Type: application/xml");
    echo "<mahasiswa_list>";
    foreach ($students as $s) {
        echo "<mahasiswa>";
        echo "<nim>{$s['nim']}</nim>";
        echo "<name>{$s['name']}</name>";
        echo "<major>{$s['major']}</major>";
        echo "</mahasiswa>";
    }
    echo "</mahasiswa_list>";
} else {
    header("Content-Type: application/json");
    echo json_encode($students);
}