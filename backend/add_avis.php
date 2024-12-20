<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: http://localhost:5173');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$data = json_decode(file_get_contents('php://input'));

if (isset($data->user_id, $data->rating, $data->comment)) {
    $user_id = (int)$data->user_id;
    $rating = (int)$data->rating;
    $comment = $data->comment;

    $servername = "localhost";
    $username = "root";
    $password = "";
    $database = "parapluitdatabase";

    $conn = new mysqli($servername, $username, $password, $database);

    if ($conn->connect_error) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $conn->connect_error]);
        exit;
    }

    $checkQuery = "SELECT id FROM avis WHERE user_id = ?";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $updateQuery = "UPDATE avis SET rating = ?, comment = ? WHERE user_id = ?";
        $updateStmt = $conn->prepare($updateQuery);
        $updateStmt->bind_param("isi", $rating, $comment, $user_id);
        if ($updateStmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Review updated successfully!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update the review.']);
        }
        $updateStmt->close();
    } else {
        $insertQuery = "INSERT INTO avis (user_id, rating, comment) VALUES (?, ?, ?)";
        $insertStmt = $conn->prepare($insertQuery);
        $insertStmt->bind_param("iis", $user_id, $rating, $comment);
        if ($insertStmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Review added successfully!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to add the review.']);
        }
        $insertStmt->close();
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Required data not provided']);
}
?>
