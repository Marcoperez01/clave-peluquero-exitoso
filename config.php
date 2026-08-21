<?php
// config.php
define('DB_HOST', 'localhost');
define('DB_NAME', 'clavepel_chatbot'); // Reemplaza con tu nombre de BD
define('DB_USER', 'kclavedk7_clavepel_chatbot');    // Reemplaza con tu usuario
define('DB_PASS', 'Ma3006774156.'); // Reemplaza con tu contraseña

// Niveles de seguridad
define('SECURITY_PUBLIC', 0);
define('SECURITY_CLIENT', 1);
define('SECURITY_PROFESSIONAL', 2);
define('SECURITY_ADMIN', 3);

// Iniciar sesión PHP
session_start();
?>