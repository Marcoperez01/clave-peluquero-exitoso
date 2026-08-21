<?php
header('Content-Type: application/json');

require_once 'db_connect.php';

$data = json_decode(file_get_contents('php://input'), true);
$message = $data['message'];
$userId = $data['userId'];
$userLevel = $data['userLevel'];

try {
    // Consultar la base de datos para respuestas
    $stmt = $conn->prepare("SELECT * FROM chatbot_responses WHERE keyword LIKE ? AND security_level <= ?");
    $keyword = "%$message%";
    $stmt->execute([$keyword, $userLevel]);
    
    if ($stmt->rowCount() > 0) {
        $response = $stmt->fetch(PDO::FETCH_ASSOC);
        echo json_encode([
            "message" => $response['response'],
            "securityLevel" => $response['security_level'],
            "requiresAuth" => $response['requires_auth']
        ]);
    } else {
        echo json_encode([
            "message" => "Lo siento, no entendí tu pregunta. ¿Podrías reformularla?",
            "securityLevel" => 0,
            "requiresAuth" => false
        ]);
    }
} catch(PDOException $e) {
    echo json_encode([
        "message" => "Error en el sistema. Por favor intenta más tarde.",
        "securityLevel" => 0,
        "requiresAuth" => false
    ]);
}
?>