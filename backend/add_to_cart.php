<?php
$allowedOrigins = ['http://localhost:5173', 'http://localhost:5174', 'http://localhost:5175', 'http://localhost:5176'];

if (isset($_SERVER['HTTP_ORIGIN']) && in_array($_SERVER['HTTP_ORIGIN'], $allowedOrigins)) {
    header("Access-Control-Allow-Origin: " . $_SERVER['HTTP_ORIGIN']);
} else {
    header("Access-Control-Allow-Origin: null");
}

// CORS headers
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

// Handle preflight (OPTIONS) requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "parapluitdatabase";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["error" => "Connection failed: " . $conn->connect_error]);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['userId'], $data['Id'], $data['quantity'], $data['price'])) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid input: Missing required fields"]);
    exit;
}

$userId = $conn->real_escape_string($data['userId']);
$itemId = $conn->real_escape_string($data['Id']); 
$quantity = $conn->real_escape_string($data['quantity']);
$itemPrice = $conn->real_escape_string($data['price']);

$createdAt = date('Y-m-d H:i:s');
$updatedAt = date('Y-m-d H:i:s');

$sql = "INSERT INTO cart (user_id, item_id, quantity, price, created_at, updated_at) 
        VALUES ('$userId', '$itemId', '$quantity', '$itemPrice', '$createdAt', '$updatedAt')";

if ($conn->query($sql) === TRUE) {
    echo json_encode(["message" => "Item added to cart successfully"]);
} else {
    http_response_code(500);
    echo json_encode(["error" => "Database error: " . $conn->error]);
}

$conn->close();
?>
