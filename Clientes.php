<?php
// ============================================================================
// 1. CONFIGURACIÓN Y CONEXIÓN A LA BASE DE DATOS MEDIANTE PDO
// ============================================================================

// Definición de las credenciales de la base de datos
$host    = 'localhost';              // Servidor local de MySQL
$db      = 'sistema_administrativo'; // Nombre exacto de la base de datos
$user    = 'root';                   // Usuario por defecto en XAMPP/WAMP
$pass    = '';                       // Contraseña por defecto (vacía)
$charset = 'utf8mb4';                // Codificación para soporte completo de caracteres y acentos

// Data Source Name (DSN): Cadena de conexión para PDO
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

// Opciones de configuración para el manejo de excepciones y formato de datos
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Lanza excepciones en caso de errores SQL
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,     // Retorna los datos como un array asociativo
    PDO::ATTR_EMULATE_PREPARES   => false,                // Usa preparaciones nativas del motor
];

try {
    // Instancia principal de la conexión PDO
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // Si la conexión falla, se detiene el script y muestra el mensaje de error
    die("Error crítico de conexión a la base de datos: " . $e->getMessage());
}

// Variables globales para el manejo de mensajes/alertas en la interfaz
$mensaje_exito = "";
$mensaje_error = "";


// ============================================================================
// 2. PROCESAMIENTO DEL FORMULARIO DE REGISTRO (MÉTODO POST)
// ============================================================================

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Captura y limpieza de espacios en blanco de los datos enviados desde el formulario
    $cedula   = isset($_POST['cedula']) ? trim($_POST['cedula']) : '';
    $nombre   = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
    $telefono = isset($_POST['telefono']) ? trim($_POST['telefono']) : '';

    // Validación de campos obligatorios antes de procesar la inserción
    if (!empty($cedula) && !empty($nombre)) {
        try {
            // Sentencia SQL alineada con las columnas reales en phpMyAdmin:
            // (cedula, nombre_cliente, telefono)
            $sql = "INSERT INTO clientes (cedula, nombre_cliente, telefono) 
                    VALUES (:cedula, :nombre, :telefono)";
            
            // Preparación de la consulta SQL para prevenir inyecciones SQL
            $stmt = $pdo->prepare($sql);
            
            // Ejecución vinculando los parámetros limpios
            $stmt->execute([
                ':cedula'   => $cedula,
                ':nombre'   => $nombre,
                ':telefono' => $telefono
            ]);

            // Mensaje de éxito al completar el registro
            $mensaje_exito = "¡Cliente registrado con éxito en el sistema!";
            
        } catch (\PDOException $e) {
            // Captura de errores al ejecutar la consulta en MySQL (ej. Cédula duplicada)
            $mensaje_error = "Error al guardar el cliente: " . $e->getMessage();
        }
    } else {
        // Advertencia si falta alguno de los campos requeridos
        $mensaje_error = "Por favor, complete los campos obligatorios (Cédula/RIF y Nombre).";
    }
}


// ============================================================================
// 3. CONSULTA Y OBTENCIÓN DEL DIRECTORIO DE CLIENTES
// ============================================================================

try {
    // Se consultan todos los registros ordenados desde el más reciente al más antiguo
    $stmt_cli = $pdo->query("SELECT * FROM clientes ORDER BY id_cliente DESC");
    
    // Se almacenan los registros en un array asociativo
    $lista_clientes = $stmt_cli->fetchAll();
    
} catch (\PDOException $e) {
    // En caso de falla en la consulta, se inicializa el array vacío para evitar romper el foreach
    $lista_clientes = [];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <!-- Configuración básica de codificación y diseño responsivo -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Clientes - AdminPanel</title>
    
    <!-- Enlace al archivo CSS estandarizado de la aplicación -->
    <link rel="stylesheet" href="S.A.css">
</head>
<body>

    <!-- ====================================================================== -->
    <!-- BARRA LATERAL (SIDEBAR) UNIFORME Y NAVEGABLE                           -->
    <!-- ====================================================================== -->
    <aside class="sidebar">
        <div>
            <!-- Logotipo o Marca del sistema -->
            <div class="sidebar-brand">
                <span class="brand-icon">⊞</span> AdminPanel
            </div>
            
            <!-- Menú de navegación principal con enlaces cruzados hacia todos los módulos -->
            <ul class="sidebar-nav">
                <li><a href="Sistema_Administrativo.php"><span class="nav-icon">🏠</span> Inicio</a></li>
                <li><a href="productos.php"><span class="nav-icon">📦</span> Productos</a></li>
                <li><a href="clientes.php" class="active"><span class="nav-icon">👥</span> Clientes</a></li>
                <li><a href="facturacion.php"><span class="nav-icon">📄</span> Facturación</a></li>
            </ul>
        </div>

        <!-- Tarjeta de Perfil de Usuario Logueado -->
        <div class="user-profile">
            <div class="avatar">P</div>
            <div class="user-info">
                <span class="user-name">Pedro Matheus</span>
                <span class="user-role">Administrador</span>
            </div>
        </div>
    </aside>

    <!-- ====================================================================== -->
    <!-- ÁREA DE CONTENIDO PRINCIPAL DE LA VISTA                                -->
    <!-- ====================================================================== -->
    <main class="main-content">

        <!-- Campo de Búsqueda superior estilizado tipo Cápsula -->
        <div class="search-container">
            <span class="search-icon">🔍</span>
            <input type="text" class="search-input" placeholder="Buscar clientes por cédula o nombre...">
        </div>
        
        <!-- Títulos descriptivos de la sección actual -->
        <h1 class="page-title">Gestión de Clientes</h1>
        <p class="page-subtitle">Administra el directorio de compradores del sistema</p>

        <!-- Bloque condicional de renderizado para Alertas de Éxito -->
        <?php if (!empty($mensaje_exito)): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($mensaje_exito); ?></div>
        <?php endif; ?>

        <!-- Bloque condicional de renderizado para Alertas de Error -->
        <?php if (!empty($mensaje_error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($mensaje_error); ?></div>
        <?php endif; ?>

        <!-- ================================================================== -->
        <!-- TARJETA 1: FORMULARIO DE REGISTRO DE CLIENTE                       -->
        <!-- ================================================================== -->
        <section class="card">
            <div class="card-header">
                <span class="card-icon">👤+</span> Registrar Nuevo Cliente
            </div>

            <!-- Formulario enviando datos mediante POST al mismo archivo -->
            <form action="clientes.php" method="POST">
                <div class="form-grid">
                    
                    <!-- Campo 1: Cédula o RIF -->
                    <div class="form-group">
                        <label for="cedula">Cédula / RIF:</label>
                        <input type="text" id="cedula" name="cedula" placeholder="Ej: V-12345678" required>
                    </div>

                    <!-- Campo 2: Nombre Completo -->
                    <div class="form-group">
                        <label for="nombre">Nombre Completo:</label>
                        <input type="text" id="nombre" name="nombre" placeholder="Ej: Juan Pérez" required>
                    </div>

                    <!-- Campo 3: Número Telefónico -->
                    <div class="form-group">
                        <label for="telefono">Teléfono:</label>
                        <input type="text" id="telefono" name="telefono" placeholder="Ej: 0414-1234567">
                    </div>

                </div>

                <!-- Botón de guardado -->
                <button type="submit" class="btn-submit">💾 Guardar Cliente</button>
            </form>
        </section>

        <!-- ================================================================== -->
        <!-- TARJETA 2: TABLA DEL DIRECTORIO DE CLIENTES                        -->
        <!-- ================================================================== -->
        <section class="card">
            <div class="card-header">
                <span class="card-icon">👥</span> Directorio de Clientes
            </div>

            <!-- Contenedor adaptativo para tablas responsive -->
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>CÉDULA / RIF</th>
                            <th>NOMBRE</th>
                            <th>TELÉFONO</th>
                            <th>ESTADO</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Verificación si existen registros devueltos desde MySQL -->
                        <?php if (!empty($lista_clientes)): ?>
                            
                            <!-- Bucle de iteración para renderizar cada fila de la base de datos -->
                            <?php foreach ($lista_clientes as $cli): ?>
                                <tr>
                                    <!-- Extracción segura de la columna 'cedula' -->
                                    <td>
                                        <?php echo htmlspecialchars($cli['cedula'] ?? ''); ?>
                                    </td>
                                    
                                    <!-- Extracción segura de la columna 'nombre_cliente' -->
                                    <td>
                                        <strong><?php echo htmlspecialchars($cli['nombre_cliente'] ?? ''); ?></strong>
                                    </td>
                                    
                                    <!-- Extracción segura de la columna 'telefono' -->
                                    <td>
                                        <?php echo htmlspecialchars($cli['telefono'] ?? ''); ?>
                                    </td>
                                    
                                    <!-- Indicador de estado fijo -->
                                    <td>
                                        <span style="color: #166534; font-weight: 600;">Activo</span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>

                        <?php else: ?>
                            <!-- Fila por defecto si la base de datos está totalmente vacía -->
                            <tr>
                                <td colspan="4" class="empty-table">
                                    No hay clientes registrados en la base de datos aún.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

    </main>

</body>
</html>