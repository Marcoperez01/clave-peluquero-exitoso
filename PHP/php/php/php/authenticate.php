<?php
header('Content-Type: application/json');

require_once 'db_connect.php';

$data = json_decode(file_get_contents('php://input'), true);
$email = $data['email'];
$password = $data['password'];

try {
    $stmt = $conn->prepare("SELECT id, name, security_level FROM users WHERE email = ? AND password = ?");
    $stmt->execute([$email, md5($password)]);
    
    if ($stmt->rowCount() > 0) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        echo json_encode([
            "authenticated" => true,
            "id" => $user['id'],
            "name" => $user['name'],
            "level" => $user['security_level']
        ]);
    } else {
        echo json_encode(["authenticated" => false]);
    }
} catch(PDOException $e) {
    echo json_encode(["authenticated" => false, "error" => $e->getMessage()]);
}
?>