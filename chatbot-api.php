<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'config.php';

header('Content-Type: application/json');

$response = ['status' => 'error', 'message' => 'Acción no válida'];

try {
    $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $action = $_POST['action'] ?? '';
    
    switch($action) {
        case 'login':
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            $securityLevel = $_POST['security_level'] ?? SECURITY_PUBLIC;
            
            // Validación básica - en producción usaría prepared statements y password_hash()
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND security_level = ?");
            $stmt->execute([$username, $securityLevel]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['security_level'] = $user['security_level'];
                $_SESSION['username'] = $user['username'];
                
                // Crear nueva conversación
                $sessionId = session_id();
                $stmt = $pdo->prepare("INSERT INTO chatbot_conversations (user_id, session_id, security_level) VALUES (?, ?, ?)");
                $stmt->execute([$user['id'], $sessionId, $user['security_level']]);
                $_SESSION['conversation_id'] = $pdo->lastInsertId();
                
                $response = [
                    'status' => 'success',
                    'message' => 'Autenticación exitosa',
                    'security_level' => $user['security_level'],
                    'username' => $user['username']
                ];
            } else {
                $response = ['status' => 'error', 'message' => 'Credenciales inválidas'];
            }
            break;
            
        case 'send_message':
            if (!isset($_SESSION['conversation_id'])) {
                throw new Exception('No hay conversación activa');
            }
            
            $message = $_POST['message'] ?? '';
            $securityLevel = $_SESSION['security_level'] ?? SECURITY_PUBLIC;
            
            // Guardar mensaje del usuario
            $stmt = $pdo->prepare("INSERT INTO chatbot_messages (conversation_id, message, sender, security_level_required) VALUES (?, ?, 'user', ?)");
            $stmt->execute([$_SESSION['conversation_id'], $message, $securityLevel]);
            
            // Procesar mensaje y generar respuesta (simplificado)
            $botResponse = processMessage($message, $securityLevel, $pdo);
            
            // Guardar respuesta del bot
            $stmt = $pdo->prepare("INSERT INTO chatbot_messages (conversation_id, message, sender, security_level_required) VALUES (?, ?, 'bot', ?)");
            $stmt->execute([$_SESSION['conversation_id'], $botResponse, $securityLevel]);
            
            $response = [
                'status' => 'success',
                'message' => $botResponse,
                'security_level' => $securityLevel
            ];
            break;
            
        case 'get_conversation':
            if (!isset($_SESSION['conversation_id'])) {
                throw new Exception('No hay conversación activa');
            }
            
            $stmt = $pdo->prepare("SELECT * FROM chatbot_messages WHERE conversation_id = ? ORDER BY timestamp ASC");
            $stmt->execute([$_SESSION['conversation_id']]);
            $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $response = [
                'status' => 'success',
                'messages' => $messages,
                'security_level' => $_SESSION['security_level'] ?? SECURITY_PUBLIC
            ];
            break;
            
        case 'logout':
            session_destroy();
            $response = ['status' => 'success', 'message' => 'Sesión cerrada'];
            break;
    }
} catch(Exception $e) {
    $response = ['status' => 'error', 'message' => $e->getMessage()];
}

echo json_encode($response);

function processMessage($message, $securityLevel, $pdo) {
    $message = strtolower(trim($message));
    
    // Respuestas según nivel de seguridad
    $responses = [
        SECURITY_PUBLIC => [
            'hola' => '¡Hola! Bienvenido a Clave del Peluquero. ¿En qué puedo ayudarte?',
            'productos' => 'Ofrecemos shampoos, acondicionadores, tratamientos y herramientas profesionales.',
            'horarios' => 'Nuestros horarios son de Lunes a Sábado de 9am a 7pm.'
        ],
        SECURITY_CLIENT => [
            'pedido' => 'Puedes ver tus pedidos en https://clavedelpeluquero.venndelo.com/',
            'precios' => 'Como cliente registrado tienes 5% de descuento en todos los productos.'
        ],
        SECURITY_PROFESSIONAL => [
            'tecnica' => 'Tenemos guías técnicas avanzadas para aplicación de productos profesionales.',
            'mayorista' => 'Los precios mayoristas aplican para compras superiores a $500,000.'
        ],
        SECURITY_ADMIN => [
            'clientes' => 'Puedes gestionar clientes desde el panel de administración.',
            'ventas' => 'El reporte de ventas está disponible en el dashboard.'
        ]
    ];
    
    // Buscar respuesta según nivel de seguridad
    foreach ($responses[$securityLevel] as $keyword => $response) {
        if (strpos($message, $keyword) !== false) {
            return $response;
        }
    }
    
    // Respuesta por defecto según nivel
    $defaultResponses = [
        SECURITY_PUBLIC => 'Para más información, por favor regístrate en nuestro sistema.',
        SECURITY_CLIENT => '¿En qué más puedo ayudarte, '.($_SESSION['username'] ?? 'cliente').'?',
        SECURITY_PROFESSIONAL => '¿Necesitas información técnica o sobre pedidos profesionales?',
        SECURITY_ADMIN => 'Comando no reconocido. ¿Deseas acceder al panel de control?'
    ];
    
    return $defaultResponses[$securityLevel];
}
?>