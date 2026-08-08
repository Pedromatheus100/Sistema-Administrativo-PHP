<?php // Abre el bloque de código del lenguaje PHP

// Define las variables de acceso al servidor local MySQL
$host = 'localhost'; // Servidor local donde corre MySQL (XAMPP/WAMP)
$db   = 'sistema_administrativo'; // Nombre exacto de tu base de datos en phpMyAdmin
$user = 'root'; // Usuario por defecto de MySQL en servidores locales
$pass = ''; // Contraseña por defecto (en XAMPP suele ir vacía '')
$charset = 'utf8mb4'; // Codificación para que reconozca acentos, Ñs y símbolos correctamente

// Configura la cadena de conexión DSN (Data Source Name)
$dsn = "mysql:host=$host;dbname=$db;charset=$charset"; // Une los datos de servidor, BD y codificación

// Opciones de configuración para la conexión PDO
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Fuerza a PHP a lanzar un error visible si la conexión falla
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // Hace que los resultados de MySQL se devuelvan como arreglos asociativos fáciles de usar
    PDO::ATTR_EMULATE_PREPARES   => false, // Desactiva la emulación de consultas para mejorar la seguridad contra Inyección SQL
];

// Intenta realizar la conexión a la base de datos
try {
    $conexion = new PDO($dsn, $user, $pass, $options); // Crea y guarda la conexión activa en la variable $conexion
    // echo "¡Conexión exitosa a la base de datos!"; // Línea de prueba
} catch (\PDOException $e) { // Captura cualquier error si la conexión falla
    throw new \PDOException($e->getMessage(), (int)$e->getCode()); // Muestra en pantalla el mensaje exacto del error
}

?> <!-- Cierra el bloque de código PHP -->