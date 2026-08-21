<?php
header('Content-Type: application/json');

$servername = "localhost";
$username = "kclavedk7";
$password = "JDDZ10ICPJO";
$dbname = "clave_peluquero";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo json_encode(["status" => "success"]);
} catch(PDOException $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>

