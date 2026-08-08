<?php
// ==========================================
// SECCIÓN DE INICIALIZACIÓN DE SESIÓN
// ==========================================

// Función session_start(): inicia una nueva sesión de usuario o reanuda la existente para persisitir datos entre redirecciones HTTP
session_start();

// ==========================================
// SECCIÓN DE CONEXIÓN A LA BASE DE DATOS
// ==========================================

// Declaración de variable $host: almacena la dirección IP o nombre del servidor local donde corre el motor MySQL
$host = 'localhost';

// Declaración de variable $db: almacena el nombre exacto de la base de datos creada previamente en phpMyAdmin
$db   = 'sistema_administrativo';

// Declaración de variable $user: especifica el usuario principal de MySQL configurado por defecto en la suite XAMPP
$user = 'root';

// Declaración de variable $pass: define la clave de acceso para el usuario root (cadena vacía por defecto en XAMPP)
$pass = '';

// Declaración de variable $charset: especifica la codificación UTF-8 de 4 bytes para soportar tildes, carácteres especiales y emojies
$charset = 'utf8mb4';

// Declaración de variable $dsn (Data Source Name): concatena el motor (mysql), host, base de datos y charset para ser consumidos por PDO
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

// Declaración de la matriz asociativa $options: establece configuraciones de rendimiento y manejo de errores para el conector PDO
$options = [
    // Opción PDO::ATTR_ERRMODE: establece el modo de reporte de errores de la base de datos
    // Valor PDO::ERRMODE_EXCEPTION: obliga a PDO a disparar excepciones PDOException cuando ocurre un fallo o error SQL
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    
    // Opción PDO::ATTR_DEFAULT_FETCH_MODE: determina la estructura en que se retornarán los datos obtenidos de la BD
    // Valor PDO::FETCH_ASSOC: hace que cada fila se devuelva como un arreglo asociativo cuya clave es el nombre exacto de la columna
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    
    // Opción PDO::ATTR_EMULATE_PREPARES: controla la emulación nativa de sentencias preparadas en PDO
    // Valor false: desactiva la emulación para garantizar que MySQL procese de forma nativa las consultas preparadas contra inyecciones SQL
    PDO::ATTR_EMULATE_PREPARES   => false,
];

// Estructura de control try: intenta ejecutar el bloque de código encargado de la conexión a la base de datos
try {
    // Instancia de la clase PDO: crea y establece el conector pasándole la cadena $dsn, las credenciales $user y $pass, y la matriz $options
    $pdo = new PDO($dsn, $user, $pass, $options);
} 
// Estructura catch: atrapa las excepciones del tipo \PDOException si la conexión al servidor falla
catch (\PDOException $e) {
    // Función die(): interrumpe el procesamiento del script PHP y despliega el mensaje de error capturado con $e->getMessage()
    die("Error de conexión a la base de datos: " . $e->getMessage());
}

// Estructura ternaria: comprueba con isset() si existe un mensaje de éxito guardado en $_SESSION['mensaje_exito'], asignándolo a $mensaje_exito
$mensaje_exito = isset($_SESSION['mensaje_exito']) ? $_SESSION['mensaje_exito'] : ""; 

// Función unset(): elimina la variable de sesión $_SESSION['mensaje_exito'] para que el mensaje no siga apareciendo al refrescar
unset($_SESSION['mensaje_exito']);

// Estructura ternaria: verifica con isset() la presencia de $_SESSION['mensaje_error'], asignando su valor a $mensaje_error
$mensaje_error = isset($_SESSION['mensaje_error']) ? $_SESSION['mensaje_error'] : ""; 

// Función unset(): limpia la variable de sesión $_SESSION['mensaje_error'] tras haber sido recuperada
unset($_SESSION['mensaje_error']);

// ==========================================
// PROCESAMIENTO DEL FORMULARIO (MÉTODO POST)
// ==========================================

// Variable superglobal $_SERVER con clave "REQUEST_METHOD": valida si la solicitud HTTP que recibe la página se hizo mediante el método POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Función isset(): valida si viene la clave $_POST['nombre'], la limpia de espacios iniciales y finales con trim(), o retorna ''
    $nombre        = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';

    // Función isset(): evalúa la presencia de $_POST['cod_barra'], remueve espacios en blanco con trim(), o retorna ''
    $cod_barra     = isset($_POST['cod_barra']) ? trim($_POST['cod_barra']) : '';

    // Función floatval(): convierte el valor enviado en $_POST['precio_compra'] a un número decimal flotante, o retorna 0.00
    $precio_compra = isset($_POST['precio_compra']) ? floatval($_POST['precio_compra']) : 0.00;

    // Función floatval(): convierte la cadena de $_POST['precio_venta'] a un valor numérico flotante, o retorna 0.00
    $precio_venta  = isset($_POST['precio_venta']) ? floatval($_POST['precio_venta']) : 0.00;

    // Función intval(): parsea el dato enviado en $_POST['stock'] a un entero limpio, o asigna 0 por defecto
    $stock         = isset($_POST['stock']) ? intval($_POST['stock']) : 0;
    
    // Función !empty(): comprueba si $_POST['fk_categoria'] contiene una opción seleccionada válida, la convierte a entero, o asigna NULL
    $fk_categoria  = !empty($_POST['fk_categoria']) ? intval($_POST['fk_categoria']) : NULL;

    // Condicional de validación: comprueba con !empty() que los campos obligatorios $nombre y $cod_barra no vengan vacíos
    if (!empty($nombre) && !empty($cod_barra)) {
        
        // Bloque try: intenta realizar el procedimiento de inserción del nuevo registro en MySQL
        try {
            // Variable $sql_insert: almacena la sentencia de inserción SQL usando marcadores de parámetros nombrados (:nombre, :cod_barra, etc.)
            $sql_insert = "INSERT INTO productos (Nombre, cod_barra, precio_compra, precio_venta, stock, fk_categoria) 
                           VALUES (:nombre, :cod_barra, :precio_compra, :precio_venta, :stock, :fk_categoria)";

            // Método prepare(): compila la consulta SQL en el objeto de conexión $pdo y la deja lista para recibir datos de forma segura
            $stmt = $pdo->prepare($sql_insert);

            // Método execute(): ejecuta la sentencia preparada asociando los valores validados de PHP a cada marcador de posición SQL
            $stmt->execute([
                ':nombre'        => $nombre,        // Enlaza la variable $nombre con la columna Nombre
                ':cod_barra'     => $cod_barra,     // Enlaza la variable $cod_barra con la columna cod_barra
                ':precio_compra' => $precio_compra, // Enlaza la variable $precio_compra con la columna precio_compra
                ':precio_venta'  => $precio_venta,  // Enlaza la variable $precio_venta con la columna precio_venta
                ':stock'         => $stock,         // Enlaza la variable $stock con la columna stock
                ':fk_categoria'  => $fk_categoria   // Enlaza la variable $fk_categoria con la columna fk_categoria
            ]);

            // Asigna un texto descriptivo a la variable superglobal $_SESSION['mensaje_exito'] para que sobreviva a la redirección
            $_SESSION['mensaje_exito'] = "¡Producto registrado correctamente!";

            // Función header(): envía una cabecera HTTP de redirección al navegador enviándolo a productos.php por una petición GET limpia
            header("Location: productos.php");

            // Función exit(): finaliza inmediatamente el script evadiendo cualquier renderizado previo a ejecutar la redirección
            exit();

        } 
        // Bloque catch: ataja cualquier fallo durante la inserción en la base de datos mediante la excepción \PDOException
        catch (\PDOException $e) {
            // Guarda el mensaje de excepción en $_SESSION['mensaje_error']
            $_SESSION['mensaje_error'] = "Error al guardar el producto: " . $e->getMessage();

            // Redirige al navegador mediante header() para limpiar la petición POST duplicada
            header("Location: productos.php");

            // Cancela el resto del código con exit()
            exit();
        }
    } else {
        // Guarda la alerta de validación en la sesión
        $_SESSION['mensaje_error'] = "Por favor complete los campos obligatorios (Nombre y Código de Barra).";

        // Aplica la redirección HTTP GET
        header("Location: productos.php");

        // Corta el flujo de ejecución con exit()
        exit();
    }
}

// ==========================================
// CONSULTAS PARA RENDERIZAR LA INTERFAZ
// ==========================================

// Bloque try: ejecuta la lectura de la lista de categorías registradas para armar el menú desplegable
try {
    // Variable $sql_categorias: consulta SELECT que pide el id y el nombre exacto de la tabla categorías ordenados alfabéticamente
    $sql_categorias = "SELECT id_categoria, nombre_categoria FROM categorias ORDER BY nombre_categoria ASC";
    
    // Método query(): efectúa la instrucción de lectura sobre el conector $pdo y devuelve un objeto de tipo PDOStatement
    $stmt_cat = $pdo->query($sql_categorias);
    
    // Método fetchAll(): recupera todas las filas resultantes como una matriz asociativa y las asigna a la variable $lista_categorias
    $lista_categorias = $stmt_cat->fetchAll();
} 
// Bloque catch: captura excepciones en caso de que ocurra una falla consultando las categorías
catch (\PDOException $e) {
    // Asigna un arreglo vacío a $lista_categorias para no romper la iteración del formulario HTML
    $lista_categorias = [];
}

// Bloque try: ejecuta la lectura general del inventario conectando productos con sus categorías
try {
    // Variable $sql_productos: sentencia SQL con LEFT JOIN que relaciona p.fk_categoria con c.id_categoria trayendo el alias categoria_nombre
    $sql_productos = "SELECT p.id_producto, p.Nombre, p.cod_barra, p.precio_compra, p.precio_venta, p.stock, c.nombre_categoria AS categoria_nombre 
                      FROM productos p 
                      LEFT JOIN categorias c ON p.fk_categoria = c.id_categoria 
                      ORDER BY p.id_producto DESC";
    
    // Método query(): ejecuta la instrucción SELECT sobre la base de datos mediante el objeto $pdo
    $stmt_prod = $pdo->query($sql_productos);
    
    // Método fetchAll(): extrae la totalidad de los datos encontrados y los convierte en un arreglo de registros asociativos en $lista_productos
    $lista_productos = $stmt_prod->fetchAll();
} 
// Bloque catch: maneja excepciones durante el SELECT de productos
catch (\PDOException $e) {
    // Define $lista_productos como una matriz vacía si la consulta llega a fallar
    $lista_productos = [];
}
?>
<!-- Declaración DOCTYPE html: le informa al navegador que la estructura del documento se basa en el estándar HTML5 -->
<!DOCTYPE html>

<!-- Etiqueta de apertura html con el atributo de lenguaje lang="es" indicando que el contenido del sitio web está en español -->
<html lang="es">

<!-- Etiqueta head: almacena cabeceras técnicas, enlaces CSS externos, juegos de caracteres y configuraciones del navegador -->
<head>
    <!-- Etiqueta meta con atributo charset="UTF-8": garantiza el soporte de caracteres especiales, tildes y símbolos en la vista HTML -->
    <meta charset="UTF-8">
    
    <!-- Etiqueta meta viewport con los atributos content="width=device-width, initial-scale=1.0": asegura la adaptabilidad a dispositivos móviles -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- Etiqueta title: establece la frase descriptiva que el usuario verá en la solapa o pestaña de su navegador -->
    <title>Gestión de Productos - Admin</title>
    
    <!-- Etiqueta link con atributos rel="stylesheet" y href="S.A.css": importa y aplica la hoja de estilo CSS externa al archivo actual -->
    <link rel="stylesheet" href="S.A.css">
<!-- Etiqueta de cierre head: concluye el bloque de metadatos e importaciones de la página -->
</head>

<!-- Etiqueta body: delimita el cuerpo principal visible y la estructura interactiva accesible para el usuario -->
<body>

    <!-- Etiqueta aside con clase CSS class="sidebar": representa el contenedor del panel o menú de navegación lateral -->
    <aside class="sidebar">
        <!-- Etiqueta div: agrupador en bloque genérico que contiene la lista de elementos del menú -->
        <div>
            <!-- Etiqueta ul con clase CSS class="sidebar-nav": representa la lista desordenada para las opciones del sistema -->
            <ul class="sidebar-nav">
                <!-- Etiqueta li que encierra una etiqueta a con atributo href="Sistema_Administrativo.php": enlace directo al panel de inicio -->
                <li><a href="Sistema_Administrativo.php">🏠 Inicio</a></li>
                
                <!-- Etiqueta li con etiqueta a que posee atributo href="productos.php" y class="active": resalta la opción activa del módulo actual -->
                <li><a href="productos.php" class="active">📦 Productos</a></li>
                
                <!-- Etiqueta li con etiqueta a y atributo href="clientes.php": enlace para acceder al módulo de gestión de clientes -->
                <li><a href="clientes.php">👥 Clientes</a></li>
                
                <!-- Etiqueta li con etiqueta a y atributo href="facturacion.php": enlace para ingresar al módulo de generación de facturas -->
                <li><a href="facturacion.php">📄 Facturación</a></li>
            <!-- Etiqueta de cierre ul: finaliza el menú desordenado de enlaces laterales -->
            </ul>
        <!-- Etiqueta de cierre div: concluye el bloque del contenedor superior -->
        </div>

        <!-- Etiqueta div con clase CSS class="user-profile": contenedor del bloque informativo del usuario autenticado en sesión -->
        <div class="user-profile">
            <!-- Etiqueta div con clase CSS class="avatar": muestra un círculo gráfico con la inicial o ícono del perfil de usuario -->
            <div class="avatar">P</div>
            
            <!-- Etiqueta div con clase CSS class="user-info": agrupa la tipografía descriptiva de los datos del usuario -->
            <div class="user-info">
                <!-- Etiqueta span con clase CSS class="user-name": renderiza en pantalla el nombre completo del usuario conectado -->
                <span class="user-name">Pedro Matheus</span>
                
                <!-- Etiqueta span con clase CSS class="user-role": renderiza la etiqueta del rol o nivel de privilegios asignado -->
                <span class="user-role">Administrador</span>
            <!-- Etiqueta de cierre div: finaliza el bloque user-info -->
            </div>
        <!-- Etiqueta de cierre div: finaliza el perfil de usuario user-profile -->
        </div>
    <!-- Etiqueta de cierre aside: concluye el menú lateral del sistema -->
    </aside>

    <!-- Etiqueta main con clase CSS class="main-content": delimita la zona de contenido de datos principal del documento -->
    <main class="main-content">
        
        <!-- Etiqueta h1 con clase CSS class="page-title": encabezado de nivel superior con el título visual del módulo -->
        <h1 class="page-title">Gestión de Productos</h1>
        
        <!-- Etiqueta p con clase CSS class="page-subtitle": subtítulo aclaratorio de las operaciones disponibles en el área -->
        <p class="page-subtitle">Administra el inventario del sistema</p>

        <!-- Bloque PHP condicional: evalúa mediante la función !empty() si la variable $mensaje_exito contiene un mensaje almacenado -->
        <?php if (!empty($mensaje_exito)): ?>
            <!-- Etiqueta div con clases CSS class="alert alert-success": contenedor visual verde para desplegar confirmaciones de éxito -->
            <div class="alert alert-success">
                <!-- Función htmlspecialchars(): imprime el texto de $mensaje_exito convirtiendo caracteres sensibles en entidades HTML para evitar vulnerabilidades XSS -->
                <?php echo htmlspecialchars($mensaje_exito); ?>
            <!-- Etiqueta de cierre div: cierra el bloque de alerta de éxito -->
            </div>
        <!-- Sintaxis endif: cierra el condicional de verificación de mensaje de éxito -->
        <?php endif; ?>

        <!-- Bloque PHP condicional: evalúa mediante !empty() si la variable $mensaje_error posee algún contenido de error capturado -->
        <?php if (!empty($mensaje_error)): ?>
            <!-- Etiqueta div con clases CSS class="alert alert-danger": contenedor visual de color rojo indicando notificaciones de error -->
            <div class="alert alert-danger">
                <!-- Función htmlspecialchars(): imprime la descripción almacenada en $mensaje_error garantizando un renderizado seguro -->
                <?php echo htmlspecialchars($mensaje_error); ?>
            <!-- Etiqueta de cierre div: concluye el bloque de la alerta de error -->
            </div>
        <!-- Sintaxis endif: cierra el condicional de verificación de alerta de error -->
        <?php endif; ?>

        <!-- Etiqueta section con clase CSS class="card": define la tarjeta contenedora que encierra el formulario de registro -->
        <section class="card">
            <!-- Etiqueta div con clase CSS class="card-header": representa la barra de cabecera distintiva de la tarjeta HTML -->
            <div class="card-header">
                <!-- Etiqueta span: contenedor simple para el símbolo gráfico "+" -->
                <span>⊕</span> Registrar Nuevo Producto
            <!-- Etiqueta de cierre div: cierra la cabecera de la tarjeta -->
            </div>

            <!-- Etiqueta form con atributos action="productos.php" y method="POST": envía los campos rellenos mediante POST al mismo archivo -->
            <form action="productos.php" method="POST">
                <!-- Etiqueta div con clase CSS class="form-grid": aplica la estructura de rejilla en columnas para posicionar las casillas -->
                <div class="form-grid">
                    
                    <!-- Etiqueta div con clase CSS class="form-group": envuelve la etiqueta y el campo de texto del código de barra -->
                    <div class="form-group">
                        <!-- Etiqueta label con atributo for="cod_barra": asocia el texto de la etiqueta al control con id="cod_barra" -->
                        <label for="cod_barra">Código de Barra:</label>
                        <!-- Etiqueta input con type="text", id/name "cod_barra", placeholder indicativo y atributo required para exigir su ingreso -->
                        <input type="text" id="cod_barra" name="cod_barra" placeholder="Ej: Z002X" required>
                    <!-- Etiqueta de cierre div: concluye la casilla del código de barra -->
                    </div>

                    <!-- Etiqueta div con clase CSS class="form-group": agrupa el control y texto descriptivo del nombre del producto -->
                    <div class="form-group">
                        <!-- Etiqueta label conectada mediante el atributo for="nombre" -->
                        <label for="nombre">Nombre del Producto:</label>
                        <!-- Etiqueta input de tipo text con id="nombre", name="nombre", placeholder y atributo de obligatoriedad required -->
                        <input type="text" id="nombre" name="nombre" placeholder="Ej: Hotwheels Camión" required>
                    <!-- Etiqueta de cierre div: cierra la casilla de nombre -->
                    </div>

                    <!-- Etiqueta div con clase CSS class="form-group": encapsula el campo numérico para ingresar el precio de compra -->
                    <div class="form-group">
                        <!-- Etiqueta label enlazada con for="precio_compra" -->
                        <label for="precio_compra">Precio Compra ($):</label>
                        <!-- Etiqueta input de type="number" con atributo step="0.01" para permitir céntimos decimales, id/name "precio_compra" y atributo required -->
                        <input type="number" step="0.01" id="precio_compra" name="precio_compra" placeholder="Ej: 2.00" required>
                    <!-- Etiqueta de cierre div: concluye la casilla de precio de compra -->
                    </div>

                    <!-- Etiqueta div con clase CSS class="form-group": agrupa la casilla referente al precio de venta del producto -->
                    <div class="form-group">
                        <!-- Etiqueta label vinculada con for="precio_venta" -->
                        <label for="precio_venta">Precio Venta ($):</label>
                        <!-- Etiqueta input con type="number", step="0.01" para montos con decimales, id/name "precio_venta" y propiedad required -->
                        <input type="number" step="0.01" id="precio_venta" name="precio_venta" placeholder="Ej: 3.00" required>
                    <!-- Etiqueta de cierre div: cierra la casilla de precio de venta -->
                    </div>

                    <!-- Etiqueta div con clase CSS class="form-group": envuelve la casilla de entrada para la existencia inicial en stock -->
                    <div class="form-group">
                        <!-- Etiqueta label ligada a for="stock" -->
                        <label for="stock">Stock Inicial:</label>
                        <!-- Etiqueta input con type="number" para enteros sin decimales, id/name "stock", placeholder descriptivo y validación required -->
                        <input type="number" id="stock" name="stock" placeholder="Ej: 5" required>
                    <!-- Etiqueta de cierre div: concluye la casilla del stock inicial -->
                    </div>

                    <!-- Etiqueta div con clase CSS class="form-group": envuelve la lista desplegable de selección de categorías -->
                    <div class="form-group">
                        <!-- Etiqueta label asociada mediante el atributo for="fk_categoria" -->
                        <label for="fk_categoria">Categoría:</label>
                        <!-- Etiqueta select con id="fk_categoria" y name="fk_categoria": define la lista desplegable de selección interactiva -->
                        <select id="fk_categoria" name="fk_categoria">
                            <!-- Etiqueta option con atributo value="": representa la opción por defecto cuando el producto no posee categoría -->
                            <option value="">-- Seleccionar Categoría --</option>
                            
                            <!-- Bucle foreach PHP: itera secuencialmente cada registro del arreglo $lista_categorias asignándolos a la variable $cat -->
                            <?php foreach ($lista_categorias as $cat): ?>
                                <!-- Etiqueta option dinamizada: pasa la id de categoría en el atributo value escapándola con htmlspecialchars() -->
                                <option value="<?php echo htmlspecialchars($cat['id_categoria']); ?>">
                                    <!-- Imprime el nombre visible de la categoría aplicando htmlspecialchars() sobre $cat['nombre_categoria'] -->
                                    <?php echo htmlspecialchars($cat['nombre_categoria']); ?>
                                <!-- Etiqueta de cierre option: concluye el elemento individual del desplegable -->
                                </option>
                            <!-- Sintaxis endforeach: finaliza el bucle de recorrido de las categorías -->
                            <?php endforeach; ?>
                        <!-- Etiqueta de cierre select: concluye la lista desplegable HTML -->
                        </select>
                    <!-- Etiqueta de cierre div: cierra el contenedor de la categoría -->
                    </div>

                <!-- Etiqueta de cierre div: concluye la rejilla en cuadrícula form-grid -->
                </div>

                <!-- Etiqueta button con atributos type="submit" y class="btn-submit": botón de acción para ejecutar el envío del formulario -->
                <button type="submit" class="btn-submit">💾 Guardar Producto</button>
            <!-- Etiqueta de cierre form: concluye el elemento del formulario -->
            </form>
        <!-- Etiqueta de cierre section: cierra la tarjeta del formulario -->
        </section>

        <!-- Etiqueta section con clase CSS class="card": define la tarjeta dedicada a presentar la tabla del inventario -->
        <section class="card">
            <!-- Etiqueta div con clase CSS class="card-header": barra de cabecera de la sección de la tabla -->
            <div class="card-header">
                <!-- Etiqueta span: muestra el símbolo gráfico de menú de opciones ≡ -->
                <span>≡</span> Inventario Actual
            <!-- Etiqueta de cierre div: cierra la cabecera de la tarjeta del inventario -->
            </div>

            <!-- Etiqueta div con clase CSS class="table-responsive": habilita el desplazamiento o scroll horizontal en pantallas ajustadas -->
            <div class="table-responsive">
                <!-- Etiqueta table: define la estructura en filas y columnas de la tabla informativa de inventario -->
                <table>
                    <!-- Etiqueta thead: agrupa el conjunto de filas y celdas correspondientes al encabezado de títulos -->
                    <thead>
                        <!-- Etiqueta tr: define la fila del encabezado de la tabla -->
                        <tr>
                            <!-- Etiqueta th: celda de título para la columna del código de barra -->
                            <th>CÓDIGO BARRA</th>
                            <!-- Etiqueta th: celda de título para el nombre descriptivo del producto -->
                            <th>PRODUCTO</th>
                            <!-- Etiqueta th: celda de título para el costo del precio de compra -->
                            <th>P. COMPRA</th>
                            <!-- Etiqueta th: celda de título para el precio asignado de venta -->
                            <th>P. VENTA</th>
                            <!-- Etiqueta th: celda de título para el número total de unidades en stock -->
                            <th>STOCK</th>
                            <!-- Etiqueta th: celda de título para indicar la categoría asignada -->
                            <th>CATEGORÍA</th>
                        <!-- Etiqueta de cierre tr: concluye la fila de títulos de la tabla -->
                        </tr>
                    <!-- Etiqueta de cierre thead: finaliza la sección del encabezado -->
                    </thead>
                    
                    <!-- Etiqueta tbody: agrupa las filas dinámicas con los registros traídos desde la base de datos de MySQL -->
                    <tbody>
                        <!-- Condicional PHP: valida con !empty() si el arreglo $lista_productos contiene información consultada -->
                        <?php if (!empty($lista_productos)): ?>
                            <!-- Bucle foreach PHP: itera fila por fila la matriz de productos guardando cada registro en $prod -->
                            <?php foreach ($lista_productos as $prod): ?>
                                <!-- Etiqueta tr: crea una fila de datos para renderizar cada registro de producto -->
                                <tr>
                                    <!-- Etiqueta td: celda que despliega el código de barra procesado de manera limpia con htmlspecialchars() -->
                                    <td><?php echo htmlspecialchars($prod['cod_barra']); ?></td>
                                    
                                    <!-- Etiqueta td que encierra una etiqueta strong: resalta en negrita el nombre del producto extraído de $prod['Nombre'] -->
                                    <td><strong><?php echo htmlspecialchars($prod['Nombre']); ?></strong></td>
                                    
                                    <!-- Etiqueta td: da formato al precio de compra a 2 decimales mediante la función number_format() con el prefijo $ -->
                                    <td>$<?php echo number_format($prod['precio_compra'], 2); ?></td>
                                    
                                    <!-- Etiqueta td: aplica la función number_format() a 2 dígitos sobre $prod['precio_venta'] anteponiendo el símbolo $ -->
                                    <td>$<?php echo number_format($prod['precio_venta'], 2); ?></td>
                                    
                                    <!-- Etiqueta td: muestra la cantidad en existencia contenida en la variable $prod['stock'] -->
                                    <td><?php echo htmlspecialchars($prod['stock']); ?></td>
                                    
                                    <!-- Etiqueta td: celda destinada a renderizar el nombre de la categoría o el estado por defecto -->
                                    <td>
                                        <!-- Condicional PHP: comprueba con !empty() si el JOIN trajo una categoría asignada en $prod['categoria_nombre'] -->
                                        <?php if (!empty($prod['categoria_nombre'])): ?>
                                            <!-- Despliega el nombre de la categoría aplicando htmlspecialchars() -->
                                            <?php echo htmlspecialchars($prod['categoria_nombre']); ?>
                                        <!-- Bloque else: se activa si el valor de la categoría ligada es NULL -->
                                        <?php else: ?>
                                            <!-- Etiqueta span con clase CSS class="text-null": aplica un tono visual secundario para indicar Sin categoría -->
                                            <span class="text-null">Sin categoría</span>
                                        <!-- Sintaxis endif: concluye la comprobación interna de categoría -->
                                        <?php endif; ?>
                                    <!-- Etiqueta de cierre td: cierra la celda de categoría -->
                                    </td>
                                <!-- Etiqueta de cierre tr: concluye la fila entera del producto actual -->
                                </tr>
                            <!-- Sintaxis endforeach: finaliza el bucle de renderizado de inventario -->
                            <?php endforeach; ?>
                        <!-- Bloque else: se activa si no existen productos guardados en la tabla de la base de datos -->
                        <?php else: ?>
                            <!-- Etiqueta tr: genera una fila única aclaratoria de estado de tabla vacía -->
                            <tr>
                                <!-- Etiqueta td con atributos colspan="6" y class="empty-table": abarca el ancho completo de las 6 columnas de la tabla -->
                                <td colspan="6" class="empty-table">
                                    No hay productos registrados en la base de datos aún.
                                <!-- Etiqueta de cierre td: cierra la celda informativa -->
                                </td>
                            <!-- Etiqueta de cierre tr: concluye la fila vacía de la tabla -->
                            </tr>
                        <!-- Sintaxis endif: finaliza el bloque condicional principal de la tabla de productos -->
                        <?php endif; ?>
                    <!-- Etiqueta de cierre tbody: concluye el cuerpo contenedor de filas de la tabla -->
                    </tbody>
                <!-- Etiqueta de cierre table: cierra el elemento de la tabla HTML -->
                </table>
            <!-- Etiqueta de cierre div: concluye el contenedor table-responsive -->
            </div>
        <!-- Etiqueta de cierre section: finaliza la tarjeta del inventario actual -->
        </section>

    <!-- Etiqueta de cierre main: concluye la sección principal de contenido -->