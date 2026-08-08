<?php
// ==============================================================================
// 1. INICIALIZACIÓN DE LA SESIÓN DE USUARIO
// ==============================================================================

/**
 * Función: session_start()
 * Propósito: Inicia una nueva sesión o reanuda la existente en el servidor.
 * Permite almacenar y transferir variables superglobales ($_SESSION) entre páginas.
 */
session_start();

// ==============================================================================
// 2. CONFIGURACIÓN Y CONEXIÓN A LA BASE DE DATOS MEDIANTE PDO
// ==============================================================================

// Variable $host (string): Almacena la dirección IP o nombre del servidor MySQL local.
$host = 'localhost';

// Variable $db (string): Nombre exacto de la base de datos en phpMyAdmin.
$db   = 'sistema_administrativo';

// Variable $user (string): Nombre del usuario administrador por defecto de MySQL.
$user = 'root';

// Variable $pass (string): Contraseña de acceso a la base de datos (vacía por defecto).
$pass = '';

// Variable $charset (string): Juego de caracteres UTF-8 para soporte de tildes y caracteres especiales.
$charset = 'utf8mb4';

// Variable $dsn (string - Data Source Name): Sintaxis de conexión requerida por PDO.
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

// Arreglo asociativo $options: Define comportamientos y políticas de error para la instancia PDO.
$options = [
    // Propiedad PDO::ATTR_ERRMODE: Configura el manejo de errores.
    // Valor PDO::ERRMODE_EXCEPTION: Lanza excepciones PDOException ante cualquier fallo en SQL.
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    
    // Propiedad PDO::ATTR_DEFAULT_FETCH_MODE: Define el formato de retorno de datos.
    // Valor PDO::FETCH_ASSOC: Retorna cada fila como un arreglo asociativo ['campo' => 'valor'].
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    
    // Propiedad PDO::ATTR_EMULATE_PREPARES: Desactiva la emulación interna de consultas preparadas.
    // Valor false: Obliga a MySQL a compilar las sentencias de forma nativa para mayor seguridad.
    PDO::ATTR_EMULATE_PREPARES   => false,
];

// Bloque Try-Catch: Controla los intentos de conexión a la base de datos.
try {
    // Instancia $pdo: Crea la conexión activa enviando $dsn, $user, $pass y $options.
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // Función die(): Interrumpe la ejecución del script y muestra el mensaje de error de PDO.
    die("Error de conexión a la base de datos: " . $e->getMessage());
}

// ==============================================================================
// 3. CAPTURA Y LIMPIEZA DE MENSAJES ALMACENADOS EN SESIÓN
// ==============================================================================

// Operador ternario: Verifica si existe la variable superglobal $_SESSION['mensaje_exito'].
$mensaje_exito = isset($_SESSION['mensaje_exito']) ? $_SESSION['mensaje_exito'] : ""; 

// Función unset(): Elimina la variable de sesión para no repetir la alerta tras recargar.
unset($_SESSION['mensaje_exito']);

// Operador ternario: Verifica si existe un mensaje de error o validación de stock.
$mensaje_error = isset($_SESSION['mensaje_error']) ? $_SESSION['mensaje_error'] : ""; 

// Función unset(): Limpia el mensaje de error de la sesión activa.
unset($_SESSION['mensaje_error']);

// ==============================================================================
// 4. PROCESAMIENTO DEL FORMULARIO DE FACTURACIÓN (MÉTODO POST)
// ==============================================================================

// Variable superglobal $_SERVER["REQUEST_METHOD"]: Evalúa si la petición fue por POST.
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Función intval(): Convierte y sanitiza los datos del formulario a enteros seguros.
    $fk_cliente   = isset($_POST['fk_cliente']) ? intval($_POST['fk_cliente']) : 0;
    $fk_producto  = isset($_POST['fk_producto']) ? intval($_POST['fk_producto']) : 0;
    $cantidad     = isset($_POST['cantidad']) ? intval($_POST['cantidad']) : 0;
    
    // Variable $fk_caja (int): Asigna el ID de la caja registradora (ej: 66 según registros).
    $fk_caja      = 66; 

    // Condicional IF: Valida que las claves foráneas y la cantidad sean números enteros positivos.
    if ($fk_cliente > 0 && $fk_producto > 0 && $cantidad > 0) {
        
        try {
            // Método $pdo->beginTransaction(): Inicia una transacción atómica SQL.
            // Garantiza que la cabecera, el detalle y la actualización de stock se ejecuten juntas.
            $pdo->beginTransaction();

            // Consulta SQL de selección: Obtiene datos y existencias del producto seleccionado.
            $sql_prod = "SELECT Nombre, precio_venta, stock FROM productos WHERE id_producto = :id_producto";
            
            // Método $pdo->prepare(): Compila y prepara la consulta SQL para evitar inyección SQL.
            $stmt_p = $pdo->prepare($sql_prod);
            
            // Método $stmt->execute(): Ejecuta la sentencia asociando el parámetro `:id_producto`.
            $stmt_p->execute([':id_producto' => $fk_producto]);
            
            // Método $stmt->fetch(): Retorna la fila encontrada como arreglo asociativo.
            $producto = $stmt_p->fetch();

            // Condicional IF: Evalúa si el producto existe en la base de datos.
            if ($producto) {
                // Asignación de variables de producto extraídas de la base de datos.
                $nombre_producto = $producto['Nombre'];
                
                // Función floatval(): Convierte el precio a formato decimal flotante.
                $precio_unitario = floatval($producto['precio_venta']);
                $stock_actual    = intval($producto['stock']);

                // --------------------------------------------------------------
                // VALIDACIÓN DE STOCK DISPONIBLE (PHP SERVER-SIDE)
                // --------------------------------------------------------------
                if ($cantidad > $stock_actual) {
                    // Asigna mensaje explicativo con la cantidad requerida y disponible.
                    $_SESSION['mensaje_error'] = "❌ Stock insuficiente: Intentas vender $cantidad unidades de '$nombre_producto', pero solo quedan $stock_actual disponibles.";
                    
                    // Método $pdo->inTransaction(): Comprueba si la transacción continúa abierta.
                    if ($pdo->inTransaction()) {
                        // Método $pdo->rollBack(): Deshace y revierte cualquier cambio en la base de datos.
                        $pdo->rollBack();
                    }
                    
                    // Función header(): Redirecciona al usuario nuevamente al módulo de facturación.
                    header("Location: facturacion.php");
                    
                    // Función exit(): Detiene la ejecución posterior del script PHP.
                    exit();
                }

                // --------------------------------------------------------------
                // PASO 1: INSERTAR EN LA TABLA 'factura_cabecera'
                // Columnas reales: fecha, fk_cliente, fk_caja
                // --------------------------------------------------------------
                $sql_cabecera = "INSERT INTO factura_cabecera (fecha, fk_cliente, fk_caja) 
                                 VALUES (NOW(), :fk_cliente, :fk_caja)";
                $stmt_c = $pdo->prepare($sql_cabecera);
                $stmt_c->execute([
                    ':fk_cliente' => $fk_cliente,
                    ':fk_caja'    => $fk_caja
                ]);

                // Método $pdo->lastInsertId(): Captura la clave primaria autogenerada (`id_factura_cabecera`).
                $id_cabecera = $pdo->lastInsertId();

                // --------------------------------------------------------------
                // PASO 2: INSERTAR EN LA TABLA 'detalle_factura'
                // Columnas reales: fk_producto, fk_factura_cabecera, cantidad, precio_unitario
                // --------------------------------------------------------------
                $sql_detalle = "INSERT INTO detalle_factura (fk_producto, fk_factura_cabecera, cantidad, precio_unitario) 
                                VALUES (:fk_producto, :fk_cabecera, :cantidad, :precio_unitario)";
                $stmt_d = $pdo->prepare($sql_detalle);
                $stmt_d->execute([
                    ':fk_producto'     => $fk_producto,
                    ':fk_cabecera'     => $id_cabecera,
                    ':cantidad'        => $cantidad,
                    ':precio_unitario' => $precio_unitario
                ]);

                // --------------------------------------------------------------
                // PASO 3: DESCONTAR STOCK EN LA TABLA 'productos'
                // --------------------------------------------------------------
                $sql_update_stock = "UPDATE productos SET stock = stock - :cantidad WHERE id_producto = :id_producto";
                $stmt_s = $pdo->prepare($sql_update_stock);
                $stmt_s->execute([
                    ':cantidad'    => $cantidad,
                    ':id_producto' => $fk_producto
                ]);

                // Método $pdo->commit(): Confirma de forma permanente todas las operaciones de la transacción.
                $pdo->commit();

                // Mensaje de éxito guardado en sesión tras completarse la facturación.
                $_SESSION['mensaje_exito'] = "¡Factura #$id_cabecera procesada con éxito y stock actualizado!";
                header("Location: facturacion.php");
                exit();

            } else {
                $_SESSION['mensaje_error'] = "El producto seleccionado no existe en el sistema.";
                header("Location: facturacion.php");
                exit();
            }

        } catch (\PDOException $e) {
            // Cancela las operaciones ante un error SQL imprevisto.
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $_SESSION['mensaje_error'] = "Error del servidor al emitir factura: " . $e->getMessage();
            header("Location: facturacion.php");
            exit();
        }

    } else {
        $_SESSION['mensaje_error'] = "Por favor, seleccione un cliente, un producto y una cantidad válida.";
        header("Location: facturacion.php");
        exit();
    }
}

// ==============================================================================
// 5. CONSULTAS SQL DE CARGA DE DATOS (MÉTODO GET)
// ==============================================================================

// Consulta 1: Carga la lista de clientes registrados en la tabla 'clientes'.
try {
    $sql_clientes = "SELECT id_cliente, nombre_cliente, cedula FROM clientes ORDER BY nombre_cliente ASC";
    $stmt_cli = $pdo->query($sql_clientes);
    // Método fetchAll(): Retorna todas las filas encontradas.
    $lista_clientes = $stmt_cli->fetchAll();
} catch (\PDOException $e) {
    $lista_clientes = [];
}

// Consulta 2: Carga únicamente los productos con existencias disponibles en inventario (stock > 0).
try {
    $sql_prod_list = "SELECT id_producto, Nombre, cod_barra, precio_venta, stock FROM productos WHERE stock > 0 ORDER BY Nombre ASC";
    $stmt_pro_l = $pdo->query($sql_prod_list);
    $lista_productos = $stmt_pro_l->fetchAll();
} catch (\PDOException $e) {
    $lista_productos = [];
}

// Consulta 3: Une las tablas con AGRUPAMIENTO (GROUP BY) para consolidar facturas multi-ítem en una sola fila.
try {
    $sql_facturas_list = "SELECT c.id_factura_cabecera, c.fecha,
                                 cli.nombre_cliente, 
                                 GROUP_CONCAT(p.Nombre SEPARATOR ', ') AS producto_nombre, 
                                 SUM(d.cantidad) AS cantidad_total, 
                                 SUM(d.cantidad * d.precio_unitario) AS total_calculado
                          FROM factura_cabecera c
                          INNER JOIN clientes cli ON c.fk_cliente = cli.id_cliente
                          INNER JOIN detalle_factura d ON d.fk_factura_cabecera = c.id_factura_cabecera
                          INNER JOIN productos p ON d.fk_producto = p.id_producto
                          GROUP BY c.id_factura_cabecera, c.fecha, cli.nombre_cliente
                          ORDER BY c.id_factura_cabecera DESC";
    $stmt_fac_l = $pdo->query($sql_facturas_list);
    $lista_facturas = $stmt_fac_l->fetchAll();
} catch (\PDOException $e) {
    $lista_facturas = [];
}
?>
<!DOCTYPE html>
<!-- Tag html: Define el idioma del documento como español (es) -->
<html lang="es">
<head>
    <!-- Tag meta charset: Establece la codificación UTF-8 para evitar caracteres extraños -->
    <meta charset="UTF-8">
    <!-- Tag meta viewport: Asegura la adaptabilidad técnica en pantallas de dispositivos móviles -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Tag title: Título de la pestaña dentro del navegador web -->
    <title>Módulo de Facturación - Sistema Administrativo</title>
    <!-- Tag link rel="stylesheet": Vincula la hoja de estilos externa S.A.css -->
    <link rel="stylesheet" href="S.A.css">
</head>
<body>

    <!-- Tag aside: Barra de navegación lateral del sistema administrativo -->
    <aside class="sidebar">
        <div>
            <!-- Tag ul/li: Lista de opciones del menú principal -->
            <ul class="sidebar-nav">
                <li><a href="Sistema_Administrativo.php">🏠 Inicio</a></li>
                <li><a href="productos.php">📦 Productos</a></li>
                <li><a href="clientes.php">👥 Clientes</a></li>
                <!-- Atributo class="active": Resalta visualmente el módulo donde se encuentra el usuario -->
                <li><a href="facturacion.php" class="active">📄 Facturación</a></li>
            </ul>
        </div>
        <!-- Contenedor del perfil de usuario -->
        <div class="user-profile">
            <div class="avatar">P</div>
            <div class="user-info">
                <span class="user-name">Pedro Matheus</span>
                <span class="user-role">Administrador</span>
            </div>
        </div>
    </aside>

    <!-- Tag main: Contenedor principal de la interfaz del usuario -->
    <main class="main-content">
        <!-- Encabezados de la sección principal -->
        <h1 class="page-title">Módulo de Facturación</h1>
        <p class="page-subtitle">Genera facturas de venta y descuenta automáticamente del inventario</p>

        <!-- BLOQUE CONDICIONAL PHP: Renderiza alerta verde de confirmación cuando $mensaje_exito no está vacío -->
        <?php if (!empty($mensaje_exito)): ?>
            <div class="alert alert-success" style="background-color: #d4edda; color: #155724; padding: 12px; border-radius: 5px; margin-bottom: 15px; border: 1px solid #c3e6cb;">
                <!-- Función htmlspecialchars(): Escapa caracteres especiales para evitar ataques XSS -->
                <?php echo htmlspecialchars($mensaje_exito); ?>
            </div>
        <?php endif; ?>

        <!-- BLOQUE CONDICIONAL PHP: Renderiza alerta roja de error si ocurrió alguna falla -->
        <?php if (!empty($mensaje_error)): ?>
            <div class="alert alert-danger" style="background-color: #f8d7da; color: #721c24; padding: 12px; border-radius: 5px; margin-bottom: 15px; border: 1px solid #f5c6cb;">
                <?php echo htmlspecialchars($mensaje_error); ?>
            </div>
        <?php endif; ?>

        <!-- SECCIÓN: FORMULARIO DE REGISTRO DE VENTA -->
        <section class="card">
            <div class="card-header">
                <span>📄</span> Emitir Nueva Factura
            </div>

            <!-- Tag form: Formulario con atributos action (envía a la misma página) y method (POST) -->
            <form id="formFacturacion" action="facturacion.php" method="POST">
                <div class="form-grid">
                    
                    <!-- Campo 1: Selección de Cliente -->
                    <div class="form-group">
                        <label for="fk_cliente">Seleccionar Cliente:</label>
                        <!-- Tag select: Lista desplegable obligatoria (required) -->
                        <select id="fk_cliente" name="fk_cliente" required>
                            <option value="">-- Seleccionar Cliente --</option>
                            <!-- Bucle foreach: Renderiza dinámicamente las opciones con los clientes cargados -->
                            <?php foreach ($lista_clientes as $cli): ?>
                                <option value="<?php echo htmlspecialchars($cli['id_cliente']); ?>">
                                    <?php echo htmlspecialchars($cli['nombre_cliente']) . " (" . htmlspecialchars($cli['cedula']) . ")"; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Campo 2: Selección de Producto -->
                    <div class="form-group">
                        <label for="fk_producto">Seleccionar Producto:</label>
                        <select id="fk_producto" name="fk_producto" required>
                            <option value="" data-stock="0" data-nombre="">-- Seleccionar Producto --</option>
                            <?php foreach ($lista_productos as $p): ?>
                                <!-- Atributos personalizables HTML5 (data-stock y data-nombre): Permiten a JS leer datos sin recargar -->
                                <option value="<?php echo htmlspecialchars($p['id_producto']); ?>" 
                                        data-stock="<?php echo htmlspecialchars($p['stock']); ?>" 
                                        data-nombre="<?php echo htmlspecialchars($p['Nombre']); ?>">
                                    <!-- Función number_format(): Formatea el precio con 2 decimales -->
                                    <?php echo htmlspecialchars($p['Nombre']) . " - $" . number_format($p['precio_venta'], 2) . " (Stock: " . htmlspecialchars($p['stock']) . ")"; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Campo 3: Cantidad a Vender -->
                    <div class="form-group">
                        <label for="cantidad">Cantidad a Vender:</label>
                        <!-- Tag input type="number": Restringe el ingreso a números enteros (min="1") -->
                        <input type="number" id="cantidad" name="cantidad" min="1" placeholder="Ej: 2" required>
                    </div>

                </div>

                <!-- Tag button: Botón de envío del formulario -->
                <button type="submit" class="btn-submit">💳 Procesar Venta / Emitir Factura</button>
            </form>
        </section>

        <!-- SECCIÓN: TABLA DE HISTORIAL DE FACTURAS -->
        <section class="card">
            <div class="card-header">
                <span>≡</span> Historial de Facturas Emitidas
            </div>

            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th># FACTURA</th>
                            <th>CLIENTE</th>
                            <th>PRODUCTO(S)</th>
                            <th>CANT. TOTAL</th>
                            <th>TOTAL VENTA</th>
                            <th>FECHA</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Condicional IF: Verifica si existen facturas en la base de datos -->
                        <?php if (!empty($lista_facturas)): ?>
                            <?php foreach ($lista_facturas as $fac): ?>
                                <tr>
                                    <!-- Función str_pad(): Rellena con ceros a la izquierda para formatear el ID (Ej: #00336) -->
                                    <td>#<?php echo str_pad($fac['id_factura_cabecera'], 5, "0", STR_PAD_LEFT); ?></td>
                                    <td><strong><?php echo htmlspecialchars($fac['nombre_cliente']); ?></strong></td>
                                    <!-- Muestra los nombres de los productos concatenados si la factura tiene más de un producto -->
                                    <td><?php echo htmlspecialchars($fac['producto_nombre']); ?></td>
                                    <td><?php echo htmlspecialchars($fac['cantidad_total']); ?></td>
                                    <td><strong>$<?php echo number_format($fac['total_calculado'], 2); ?></strong></td>
                                    <!-- Funciones date() y strtotime(): Dan formato legible a la fecha registrada (Día/Mes/Año Hora:Minuto) -->
                                    <td><?php echo date('d/m/Y H:i', strtotime($fac['fecha'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="empty-table">No se han emitido facturas en el sistema aún.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

    </main>

    <!-- LÓGICA DE VALIDACIÓN EN EL CLIENTE (JAVASCRIPT) -->
    <script>
        // Método addEventListener(): Escucha el evento 'submit' (envío) del formulario de facturación
        document.getElementById('formFacturacion').addEventListener('submit', function(e) {
            // Captura los elementos HTML del formulario por su atributo id
            var selectProducto = document.getElementById('fk_producto');
            var optionSeleccionada = selectProducto.options[selectProducto.selectedIndex];
            
            // Función parseInt(): Lee los atributos personalizados 'data-stock' y 'data-nombre' de la opción elegida
            var stockDisponible = parseInt(optionSeleccionada.getAttribute('data-stock')) || 0;
            var nombreProducto = optionSeleccionada.getAttribute('data-nombre') || 'Producto';
            var cantidadIngresada = parseInt(document.getElementById('cantidad').value) || 0;

            // Condicional de validación: Compara la cantidad ingresada contra el stock real en almacén
            if (cantidadIngresada > stockDisponible) {
                // Método e.preventDefault(): Bloquea el envío del formulario al servidor
                e.preventDefault();
                
                // Función alert(): Muestra una ventana de advertencia instantánea al usuario
                alert("❌ ATENCIÓN: STOCK INSUFICIENTE\n\n" +
                      "Has ingresado " + cantidadIngresada + " unidades para el producto '" + nombreProducto + "', " +
                      "pero actualmente solo quedan " + stockDisponible + " disponibles en el inventario.");
            }
        });
    </script>

</body>
</html>