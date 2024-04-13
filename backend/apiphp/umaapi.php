<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, DELETE");
header("Access-Control-Allow-Headers: Content-Type");
define('DB_NAME', 'wp_extgb');
define('DB_USER', 'wp_l7aro');
define('DB_PASSWORD', 'i3ZaO2xP_EQ#3e#A');
define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_CHARSET', 'utf8');
define('DB_COLLATE', '');
$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME, DB_PORT);
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}
$createTableQuery = "CREATE TABLE IF NOT EXISTS umaapi (
                        id INT(11) AUTO_INCREMENT PRIMARY KEY,
                        email VARCHAR(255) NOT NULL UNIQUE,
                        message TEXT
                    )";

$mysqli->query($createTableQuery);
function addData($mysqli, $email, $message = null) {
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['status' => 'error', 'message' => 'Invalid email format'];
    }
    $checkQuery = "SELECT id FROM umaapi WHERE email = ?";
    $stmtCheck = $mysqli->prepare($checkQuery);
    $stmtCheck->bind_param('s', $email);
    $stmtCheck->execute();
    $stmtCheck->store_result();

    if ($stmtCheck->num_rows > 0) {
        $stmtCheck->close();
        return ['status' => 'error', 'message' => 'Email already exists'];
    }
    $insertQuery = "INSERT INTO umaapi (email, message) VALUES (?, ?)";
    $stmt = $mysqli->prepare($insertQuery);
    $stmt->bind_param('ss', $email, $message);
    
    $result = $stmt->execute();
    $stmt->close();

    if ($result) {
        return ['status' => 'success', 'message' => 'Data inserted successfully'];
    } else {
        return ['status' => 'error', 'message' => 'Failed to insert data'];
    }
}

function getData($mysqli, $page, $limit) {
    $offset = ($page - 1) * $limit;
    $result = $mysqli->query("SELECT * FROM umaapi ORDER BY id DESC LIMIT $limit OFFSET $offset");
    $data = [];

    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    $hasNextPage = $mysqli->query("SELECT COUNT(*) as total FROM umaapi LIMIT 1 OFFSET " . ($offset + $limit))->fetch_assoc()['total'] > 0;

    return ['records' => $data, 'hasNextPage' => $hasNextPage];
}
function deleteData($mysqli, $id) {
    if (!is_numeric($id)) {
        return ['status' => 'error', 'message' => 'Invalid ID format'];
    }
    $deleteQuery = "DELETE FROM umaapi WHERE id = ?";
    $stmt = $mysqli->prepare($deleteQuery);
    $stmt->bind_param('i', $id);
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            $stmt->close();
            return ['status' => 'success', 'message' => 'Data deleted successfully'];
        } else {
            $stmt->close();
            return ['status' => 'error', 'message' => 'ID not found'];
        }
    } else {
        $stmt->close();
        return ['status' => 'error', 'message' => 'Failed to delete data'];
    }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? null;
    $message = $_POST['message'] ?? null;

    if ($email) {
        $response = addData($mysqli, $email, $message);
        echo json_encode($response);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Email is required']);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $page = $_GET['page'] ?? 1;
    $limit = $_GET['limit'] ?? 10;
    
    echo json_encode(getData($mysqli, $page, $limit));
}
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $id = $_GET['id'] ?? null;

    if ($id) {
        $response = deleteData($mysqli, $id);
        echo json_encode($response);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'ID is required']);
    }
}
$mysqli->close();
?>
