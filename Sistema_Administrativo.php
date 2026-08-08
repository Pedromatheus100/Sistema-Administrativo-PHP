<?php
// ============================================================================
// BLOQUE 1: CONFIGURACIÓN DE ERRORES Y CONEXIÓN PDO A LA BASE DE DATOS
// ============================================================================

// Habilitar la notificación de todos los errores de PHP durante la fase de desarrollo
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Parámetros de conexión a MySQL
$host    = 'localhost';              // Nombre del servidor de base de datos local
$db      = 'sistema_administrativo'; // Nombre exacto de la base de datos en phpMyAdmin
$user    = 'root';                   // Usuario por defecto en entornos de desarrollo local (XAMPP/WAMP)
$pass    = '';                       // Contraseña vacía por defecto
$charset = 'utf8mb4';                // Codificación para soporte completo de caracteres especiales y acentos

// Definición de la cadena Data Source Name (DSN) para el driver PDO
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

// Configuración de opciones para el objeto PDO
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Configura PDO para lanzar excepciones automáticas ante errores SQL
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,     // Establece el formato de retorno como un arreglo asociativo
    PDO::ATTR_EMULATE_PREPARES   => false,                // Desactiva la emulación de sentencias preparadas para mayor seguridad
];

// Inicialización de las variables globales para almacenar las métricas de la pantalla de inicio
$total_productos = 0;
$total_clientes  = 0;

try {
    // Creación de la instancia de conexión PDO
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    // Consulta SQL 1: Conteo total de registros dentro de la tabla "productos"
    $stmt_prod = $pdo->query("SELECT COUNT(*) AS total FROM productos");
    $resultado_prod = $stmt_prod->fetch();
    if ($resultado_prod) {
        $total_productos = $resultado_prod['total'];
    }

    // Consulta SQL 2: Conteo total de registros dentro de la tabla "clientes"
    $stmt_cli = $pdo->query("SELECT COUNT(*) AS total FROM clientes");
    $resultado_cli = $stmt_cli->fetch();
    if ($resultado_cli) {
        $total_clientes = $resultado_cli['total'];
    }

} catch (\PDOException $e) {
    // En caso de que ocurra una falla en la conexión o consulta, capturamos la excepción
    // Mantendremos los contadores en cero para evitar que la interfaz falle por completo
    $total_productos = 0;
    $total_clientes  = 0;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <!-- ====================================================================== -->
    <!-- BLOQUE 2: METADATOS Y ENLACE A HOJA DE ESTILOS CSS                      -->
    <!-- ====================================================================== -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio - MegaMarket Infinity</title>
    
    <!-- Enlace directo al archivo de estilos S.A.css que da la apariencia azul al AdminPanel -->
    <link rel="stylesheet" href="S.A.css">
</head>
<body>

    <!-- ====================================================================== -->
    <!-- BLOQUE 3: BARRA LATERAL DE NAVEGACIÓN (SIDEBAR)                        -->
    <!-- ====================================================================== -->
    <aside class="sidebar">
        <div>
            <!-- Encabezado con la marca comercial general de la tienda -->
            <div class="sidebar-brand">
                <span class="brand-icon">🛍️</span> MegaMarket
            </div>
            
            <!-- Menú interactivo de opciones del sistema administrativo -->
            <ul class="sidebar-nav">
                <!-- Enlace activo a la vista actual (Inicio) -->
                <li>
                    <a href="Sistema_Administrativo.php" class="active">
                        <span class="nav-icon">🏠</span> Inicio
                    </a>
                </li>
                <!-- Enlace hacia el catálogo de gestión de inventario -->
                <li>
                    <a href="productos.php">
                        <span class="nav-icon">📦</span> Productos
                    </a>
                </li>
                <!-- Enlace hacia el directorio de gestión de clientes -->
                <li>
                    <a href="clientes.php">
                        <span class="nav-icon">👥</span> Clientes
                    </a>
                </li>
                <!-- Enlace hacia el módulo de punto de venta y emisión de facturas -->
                <li>
                    <a href="facturacion.php">
                        <span class="nav-icon">📄</span> Facturación
                    </a>
                </li>
            </ul>
        </div>

        <!-- Módulo de identificación del usuario con sesión activa en el sistema -->
        <div class="user-profile">
            <div class="avatar">P</div>
            <div class="user-info">
                <span class="user-name">Pedro Matheus</span>
                <span class="user-role">Administrador</span>
            </div>
        </div>
    </aside>

    <!-- ====================================================================== -->
    <!-- BLOQUE 4: CONTENEDOR PRINCIPAL DEL DASHBOARD (MAIN CONTENT)            -->
    <!-- ====================================================================== -->
    <main class="main-content">

        <!-- Campo de búsqueda rápido ubicado en la parte superior -->
        <div class="search-container">
            <span class="search-icon">🔍</span>
            <input type="text" class="search-input" placeholder="Buscar víveres, juguetes, hogar, limpieza o clientes...">
        </div>
        
        <!-- Encabezado con el nombre de la tienda y bajada explicativa -->
        <h1 class="page-title">¡Bienvenido a MegaMarket Infinity!</h1>
        <p class="page-subtitle">Panel de control general: Víveres, Juguetes, Limpieza y Artículos para el Hogar</p>

        <!-- ================================================================== -->
        <!-- BLOQUE 5: TARJETAS DE MÉTRICAS O INDICADORES (KPIs)                -->
        <!-- ================================================================== -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 20px; margin-top: 25px;">
            
            <!-- Tarjeta KPI N° 1: Contador total de artículos en inventario -->
            <section class="card">
                <div class="card-header">
                    <span class="card-icon">📦</span> Total Productos
                </div>
                <!-- Impresión del conteo dinámico obtenido desde la base de datos PHP -->
                <div style="font-size: 2.5rem; font-weight: 700; color: #0f172a; margin: 10px 0;">
                    <?php echo htmlspecialchars((string)$total_productos, ENT_QUOTES, 'UTF-8'); ?>
                </div>
                <p style="font-size: 0.85rem; color: #64748b; margin-bottom: 16px;">
                    Ítems registrados en inventario
                </p>
                <!-- Acceso directo al módulo de productos -->
                <a href="productos.php" style="color: #0077ff; text-decoration: none; font-weight: 600; font-size: 0.9rem;">
                    Ver inventario completo &rsaquo;
                </a>
            </section>

            <!-- Tarjeta KPI N° 2: Conteo de clientes registrados en el sistema -->
            <section class="card">
                <div class="card-header">
                    <span class="card-icon">👥</span> Directorio Clientes
                </div>
                <!-- Impresión del total de clientes leídos mediante PHP PDO -->
                <div style="font-size: 2.5rem; font-weight: 700; color: #0f172a; margin: 10px 0;">
                    <?php echo htmlspecialchars((string)$total_clientes, ENT_QUOTES, 'UTF-8'); ?>
                </div>
                <p style="font-size: 0.85rem; color: #64748b; margin-bottom: 16px;">
                    Clientes registrados en el sistema
                </p>
                <!-- Acceso directo al módulo de clientes -->
                <a href="clientes.php" style="color: #0077ff; text-decoration: none; font-weight: 600; font-size: 0.9rem;">
                    Gestionar clientes &rsaquo;
                </a>
            </section>

            <!-- Tarjeta KPI N° 3: Estado del punto de venta y facturación -->
            <section class="card">
                <div class="card-header">
                    <span class="card-icon">📄</span> Facturación
                </div>
                <div style="font-size: 2.5rem; font-weight: 700; color: #166534; margin: 10px 0;">
                    Activo
                </div>
                <p style="font-size: 0.85rem; color: #64748b; margin-bottom: 16px;">
                    Punto de venta y registro de compras
                </p>
                <!-- Acceso directo a la pantalla de facturación -->
                <a href="facturacion.php" style="color: #0077ff; text-decoration: none; font-weight: 600; font-size: 0.9rem;">
                    Ir a punto de venta &rsaquo;
                </a>
            </section>

        </div>

        <!-- ================================================================== -->
        <!-- BLOQUE 6: SECCIÓN VISUAL DE CATEGORÍAS SEGÚN BASE DE DATOS         -->
        <!-- ================================================================== -->
        <h2 style="font-size: 1.3rem; font-weight: 700; color: #0f172a; margin: 35px 0 15px 0;">
            Categorías de Productos
        </h2>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 15px;">
            
            <!-- Categoría ID 60: Víveres -->
            <div class="card" style="padding: 18px; text-align: center;">
                <div style="font-size: 2rem; margin-bottom: 8px;">🛒</div>
                <h3 style="font-size: 1rem; color: #0f172a; margin-bottom: 5px;">Víveres</h3>
                <p style="font-size: 0.8rem; color: #64748b;">ID 60 • Alimentos y canasta básica</p>
            </div>

            <!-- Categoría ID 61: Juguetes -->
            <div class="card" style="padding: 18px; text-align: center;">
                <div style="font-size: 2rem; margin-bottom: 8px;">🧸</div>
                <h3 style="font-size: 1rem; color: #0f172a; margin-bottom: 5px;">Juguetes</h3>
                <p style="font-size: 0.8rem; color: #64748b;">ID 61 • Entretenimiento y juegos</p>
            </div>

            <!-- Categoría ID 62: Limpieza -->
            <div class="card" style="padding: 18px; text-align: center;">
                <div style="font-size: 2rem; margin-bottom: 8px;">🧹</div>
                <h3 style="font-size: 1rem; color: #0f172a; margin-bottom: 5px;">Limpieza</h3>
                <p style="font-size: 0.8rem; color: #64748b;">ID 62 • Artículos de aseo e higiene</p>
            </div>

            <!-- Categoría ID 63: Artículos para el Hogar -->
            <div class="card" style="padding: 18px; text-align: center;">
                <div style="font-size: 2rem; margin-bottom: 8px;">🏠</div>
                <h3 style="font-size: 1rem; color: #0f172a; margin-bottom: 5px;">Artículos para el Hogar</h3>
                <p style="font-size: 0.8rem; color: #64748b;">ID 63 • Utensilios, decoración y más</p>
            </div>

        </div>

    </main>

</body>
</html>