<?php
error_log("=== TEST.PHP EXECUTED ===");
header('Content-Type: application/json');
echo json_encode(['message' => 'test works']);