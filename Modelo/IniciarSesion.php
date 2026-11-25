<?php

session_start();

header("Content-Type: application/json; charset=utf-8");

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        throw new Exception("Método HTTP no permitido.");
    }

    $datos = json_decode(file_get_contents('php://input'), true);

    if (!is_array($datos) || json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        throw new Exception("Datos inválidos.");
    }

    // Acepta distintas variantes por si el JS envía mayúsculas o 'correo'
    $email = trim($datos['email'] ?? $datos['Email'] ?? $datos['correo'] ?? "");
    $contraseña = $datos['contrasena'] ?? $datos['Contraseña'] ?? $datos['contraseña'] ?? $datos['password'] ?? "";

    if ($email === "" || $contraseña === "") {
        http_response_code(400);
        throw new Exception("Debes ingresar tu correo y contraseña.");
    }

    require_once __DIR__ . '/../Config/conexion.php';
    $conexion = (new Conexion())->conectar();

    $consulta = $conexion->prepare("SELECT * FROM asociados WHERE email = :email LIMIT 1");
    $consulta->execute([':email' => $email]);
    $asociado = $consulta->fetch(PDO::FETCH_ASSOC);

    if (!$asociado || empty($asociado['contrasena']) || !password_verify($contraseña, $asociado['contrasena'])) {
        http_response_code(401);
        throw new Exception("Email o contraseña incorrectos.");
    }

    $_SESSION["rol"] = strtolower($asociado['rol']);
    $_SESSION["documento"] = $asociado['documento'];

    echo json_encode([
        "success" => true,
        "rol" => $_SESSION["rol"],
        "nombre" => $asociado['nombres'] . " " . $asociado['apellidos'],
    ]);

} catch (PDOException $e) {
    if (http_response_code() === 200) {
        http_response_code(500);
    }
    echo json_encode([
        "success" => false,
        "mensaje" => "Error: " . $e->getMessage()
    ]);

} catch (Exception $e) {
    if (http_response_code() === 200) {
        http_response_code(400);
    }
    echo json_encode([
        "success" => false,
        "mensaje" => $e->getMessage()
    ]);
}