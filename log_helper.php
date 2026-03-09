<?php
// log_helper.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

function log_user_action($message) {
    $log_dir = __DIR__ . '/logs';
    if (!file_exists($log_dir)) {
        mkdir($log_dir, 0755, true);
    }

    $log_file = $log_dir . '/user_logs.txt';
    $timestamp = date("Y-m-d H:i:s");
    $entry = "[$timestamp] $message\n";
    file_put_contents($log_file, $entry, FILE_APPEND);
}

function log_page_view($page, $user = 'guest') {
    $log_dir = __DIR__ . '/logs';
    if (!file_exists($log_dir)) {
        mkdir($log_dir, 0755, true);
    }

    $log_file = $log_dir . '/page_logs.txt';
    $timestamp = date("Y-m-d H:i:s");
    $entry = "[$timestamp] ผู้ใช้: $user เปิดหน้า: $page\n";
    file_put_contents($log_file, $entry, FILE_APPEND);
}

function log_file_upload($filename, $user = 'guest', $size = 0) {
    $log_dir = __DIR__ . '/logs';
    if (!file_exists($log_dir)) {
        mkdir($log_dir, 0755, true);
    }

    $log_file = $log_dir . '/upload_logs.txt';
    $timestamp = date("Y-m-d H:i:s");
    $entry = "[$timestamp] ผู้ใช้: $user อัปโหลดไฟล์: $filename ขนาด: $size bytes\n";
    file_put_contents($log_file, $entry, FILE_APPEND);
}

function log_action($message, $user = 'guest') {
    $log = "[" . date('Y-m-d H:i:s') . "] $user - $message\n";
    file_put_contents("../logs/action_logs.txt", $log, FILE_APPEND);
}

