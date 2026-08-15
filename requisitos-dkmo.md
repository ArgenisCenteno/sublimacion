El objetivo es implementar un ERP/DMS (Dealer Management System) Centralizado que elimine la dispersión operativa actual (Excel, WhatsApp, Fotos sueltas). La plataforma actuará como la "Fuente Única de Verdad" para todas las empresas del grupo, automatizando la ingesta de vehículos, garantizando la trazabilidad física y financiera.
PANTALLA 1: DASHBOARD EJECUTIVO ("Torre de Control")
Tipo: Web Desktop.
Usuarios: Gerencia, Dirección Financiera, Jefes de Taller.
1.1. Cabecera y Filtros Globales
Selector de Entidad (Dropdown):
Opciones: Vista Consolidada (Todo el Grupo), DKM0, By Renting, DAB.
Lógica: Al cambiar, recarga todos los widgets inferiores filtrando por el campo EMPRESA de la base de datos.
Selector de Fecha: Mes Actual / Trimestre / Año / Personalizado.
1.2. Área de KPIs Financieros (Tarjetas Superiores)
Stock Valorado (€): Sumatorio del campo Coste Total (PVI + Gastos) de todos los vehículos en estado Disponible o Taller.
Unidades en Stock: Conteo total de vehículos activos (excluyendo Vendidos/Entregados).
Rotación: Ratio de Ventas Realizadas / Stock Medio.
Margen Medio (€ y %): Promedio de rentabilidad de las ventas del periodo seleccionado.
1.3. Gráfico "Embudo de Operaciones" (Visualización de Estado Logístico)
Tipo: Gráfico de barras horizontales o Pipeline.
Datos a mostrar (Agrupación por Ubicación/Estado Físico):
En Tránsito (Cargados por Excel, no recepcionados).
En Campa (Recepcionados, pendientes de taller).
En Taller (Suma de: Chapa + Mecánica + DAB).
Limpieza / Pre-entrega.
Disponibles para Venta.
Interacción: Al hacer clic en una barra (ej: "En Taller: 15 coches"), navega al Listado de Vehículos filtrando por ese estado.
1.4. Centro de Alertas y Tareas (Task Manager)
Tipo: Tabla listada con prioridad (Alta/Media).
Lógica de Generación Automática (Triggers):
Alerta Roja: "Vehículo Vendido con Seguro Activo" (Acción: Botón "Dar de Baja").
Alerta Roja: "Vehículo Vendido con 1 Llave" (Acción: Botón "Solicitar 2ª Llave").
Alerta Naranja: "Cuello de Botella" -> Vehículo con Fecha_Entrada_Taller > 15 días.
Alerta Verde: "Salida Taller" -> Vehículo finalizó Limpieza, pendiente recogida logística.

PANTALLA 2: MÓDULO DE INGESTA Y RECEPCIÓN
Tipo: Web (Administración) + App Móvil web (Operario de Campa).
Usuarios: Administrativos de Compras y Operarios de Logística.
Este módulo se divide en dos sub-pantallas funcionales:
2.1. PANTALLA WEB: IMPORTADOR MASIVO (Pre-Carga)
Funcionalidad: Área de "Drag & Drop" para subir archivos .xlsx o .csv.
Mapeo de Datos (Parser): El sistema leerá las columnas del Excel del proveedor y las guardará en la BD temporal:
Matrícula
Bastidor (VIN)
Modelo / Versión
PVI Inicial (Coste de compra - Dato Financiero Crítico)
Proveedor
Empresa Asignada (DKM, DAB, etc.)
Estado Resultante: Los coches se crean con Estado Físico = "EN TRÁNSITO / PENDIENTE RECEPCIÓN".
2.2. VISTA MÓVIL: RECEPCIÓN "SMART INGEST" (WEB RESPONSIVE)
Tecnología: Web App optimizada para móvil (Chrome/Safari).
Acceso: URL privada (ej: operaciones.km0.com).
Funcionalidad de Cámara: Uso de la API del navegador (HTML5 Media Capture) para activar la cámara del móvil directamente desde la web para escanear QRs y tomar fotos.
Geolocalización: Uso de Navigator.geolocation del navegador para obtener coordenadas GPS al validar la recepción.
El resto de la funcionalidad (Fotos obligatorias, Check Seguro, etc.) 
Buscador: Campo para escribir matrícula o escanear un código de barras provisional si lo hubiera. Localiza el expediente creado en el paso 2.1.
Acción 1: Vinculación QR (El "Bautizo" Digital):
Botón "Escanear Nuevo QR".
El operario escanea la pegatina física.
Lógica: El sistema asocia ese ID de QR único al Bastidor/ matricula en la base de datos.
Acción 2: Auditoría Visual (Obligatoria):
Carrusel de 5 fotos obligatorias (Frontal, Trasera, Lateral Izq, Lateral Der, Interior/KM).
Botón "Añadir Daño": Permite tocar sobre una silueta del coche y añadir foto detalle + nota (ej: "Arañazo aleta derecha").
Acción 3: Datos Críticos (Checkboxes):
Toggle "ACTIVAR SEGURO FLOTA":
Backend: Guarda Fecha_Hora_Alta_Seguro = NOW(). Cambia flag a TRUE.
Selector "Nº Llaves": 1 Llave / 2 Llaves.
Documentación Física: Checkbox "¿Trae Ficha Técnica?" / "¿Trae Permiso Circulación?".
Botón Finalizar Recepción:
Validación: No deja guardar si faltan las 5 fotos.
Acción GPS: Captura Latitud/Longitud del dispositivo.
Cambio de Estado: El coche pasa de "En Tránsito" a "EN CAMPA / DISPONIBLE PARA TALLER".


PANTALLA 3: GESTIÓN DE TALLERES Y LOGÍSTICA
Tipo: Web Responsive (Móvil para mecánicos / Desktop para Jefes de Taller). Usuarios: Proveedores Externos (Chapa), Taller Interno (DAB), Limpieza.
3.1. Acceso Universal Seguro (QR + PIN)
Flujo de Entrada:
El mecánico escanea el QR del vehículo con su móvil (sin loguearse).
Se abre una Landing Page de Seguridad.
Solicitud de Credencial: "Introduzca Código de Operador".
Enrutado de Roles:
PIN 1XXX -> Redirige a Vista Chapa.
PIN 2XXX -> Redirige a Vista DAB/Revisión.
PIN 3XXX -> Redirige a Vista Limpieza.
PIN 4XXX -> Redirige a Recepción del vehículo.
Geofencing (Anti-Fraude):
Al introducir el PIN, el navegador solicita permiso de ubicación.
Regla: Si las coordenadas del móvil distan > 750m de la ubicación registrada del taller, se bloquea el acceso y se genera una alerta de seguridad ("Intento de acceso no autorizado").
3.2. Vistas Específicas por Rol (Workflow)
Vista Chapa/Pintura:
Botón Grande: "Confirmar Recepción Vehículo" (Cambia estado logístico a En Taller Chapa).
Botón "Reportar Incidencia": Permite subir foto si hay un daño no previsto.
Botón "Finalizar Reparación": Obliga a subir FOTO DE SALIDA (Evidencia del trabajo).
Vista DAB (Revisión Técnica):
Checklist Digital: Lista de puntos a revisar (Niveles, Luces, Neumáticos...).
Si se marca un fallo, obliga a abrir una "Incidencia de Taller".
Vista Limpieza (Último paso):
Obligatoriedad de Fotos Finales de Calidad (para uso comercial posterior).
Trigger de Salida: Al pulsar "Finalizar Limpieza", el estado cambia automáticamente a "PENDIENTE RECOGIDA" y notifica a Logística.
3.3. Control de Ubicaciones (El Radar)
Mapa de Calor: Vista en Google Maps integrada en el Dashboard que muestra pines de colores según el estado:
🔵 Campa
🟠 Taller Externo
🟢 Tienda/Entrega
Navegación: Al hacer clic en un coche -> Botón "Cómo llegar" (abre Waze/Maps con las coordenadas lat/long exactas donde se hizo el último escaneo).
PANTALLA 4: EXPEDIENTE DIGITAL Y COSTING (CONTABILIDAD)
Tipo: Web Desktop. Usuarios: Administración, Financiero, Gestoría.
4.1. Bóveda Documental (Gestión de Archivos)
Slots Fijos (Obligatorios para Venta):
Ficha Técnica (PDF/JPG).
Permiso de Circulación.
Mandato de Gestoría.
Reserva
DNI comprador
Contrato de compraventa
Certificado de confirmación
Hoja de liquidación
Garantía del vehículo
Póliza de seguro (SI/NO)
Estado Documental: Semáforo visual en el listado de coches.
🔴 Falta Documentación Legal (Bloquea la exportación a Motorflash).
🟢 Documentación Completa.
4.2. Calculadora de Margen Real (Costing por Bastidor)
Estructura de Costes (Waterfall):
(+) PVI Inicial: Importado del Excel de Compra.
(+) Costes Directos: Sumatorio de facturas vinculadas a ese bastidor (Factura Taller Chapa + Factura Piezas).
(+) Fee Gestión: % o Fijo imputado por la empresa.
(=) COSTE TOTAL REAL.
Análisis de Rentabilidad:
Margen Bruto (€) = PVP Venta - Coste Total Real.
Margen (%) = (Margen Bruto / PVP Venta) * 100.
4.3. Portal del Gestor (Vista Limitada)
Acceso Restringido: El gestor entra con su usuario y solo ve esta pantalla.
Buzón de Facturas:
Pestaña "Pendientes": Facturas subidas por KM0 que el gestor aún no ha procesado.
Pestaña "Procesadas": Histórico.
Acción de Descarga: Botón "Descargar Todo (ZIP)" o descarga individual. Al descargar, el sistema marca automáticamente el documento como "DESCARGADO" (Check verde) para que KM0 sepa que la gestoría ya lo tiene.
Reconocimiento Inteligente de Facturas (OCR):
El sistema permite subir facturas (PDF/JPG) en bloque a una "Bandeja de Entrada".
Algoritmo de Asignación: El sistema lee el documento, busca cadenas de texto que coincidan con Matrículas existentes en la base de datos y asigna el gasto automáticamente al vehículo correspondiente.
Ejemplo: Subes la factura de "Talleres Pepe" con 10 matrículas -> El sistema reparte el coste en los 10 coches sin intervención humana.
4.4. Módulo de Facturación Recurrente (Exclusivo "By Renting")
Objetivo: Automatización total de la emisión de recibos y facturas para vehículos cedidos en modalidad de renting o suscripción.
Motor de Contratos (Plantillas):
Dentro de la ficha del vehículo (cuando la entidad sea "By Renting"), se habilita la pestaña "Contrato Renting".
Campos Clave: Fecha de Inicio, Fecha de Fin, Cuota Mensual (€), Cliente/NIF y Conceptos Fijos (ej: Cuota Renting, Seguro Incluido, Mantenimiento).
Generación Automática (Cron Job):
El sistema escanea diariamente los contratos en estado "Activo".
El día estipulado de cada mes, el sistema crea la factura automáticamente en PDF utilizando la serie de facturación específica de la empresa "By Renting".
La factura generada se auto-asigna al expediente del vehículo y queda lista en la Bandeja de Salida (o portal del gestor) para su contabilización y cobro.
Control de Ciclo de Vida (Triggers):
Alerta Pre-Vencimiento: El sistema envía una alerta al Dashboard 30 días antes de la Fecha de Fin indicando: "Fin de contrato próximo - Gestionar retorno o renovación (Matrícula XXX)".




PANTALLA 6: GESTIÓN COMERCIAL Y PUBLICACIÓN
Tipo: Web Desktop.
Usuarios: Equipo Comercial, Responsable de Ventas, Marketing.
Esta pantalla es el "Puente de Mando" para vender. Aquí se aplica la Regla de Oro: Venta Paralela (Vender mientras se repara).
6.1. Inventario Maestro (La "Sábana" Digital)
Vista: Tabla avanzada tipo DataGrid con filtros dinámicos.
Columnas Críticas (Cruce de Estados):
Coche: Marca, Modelo, Versión, Matrícula.
Ubicación (Logística): "Taller Chapa" / "Campa" / "Tienda".
Estado Comercial: DISPONIBLE / RESERVADO / VENDIDO / NO PUBLICABLE.
Scrappear de la web para ver estado de vehículos.
Precio: PVP Contado / PVP Financiado.
Semáforos:
 Icono Cámara: Verde (Tiene 5+ fotos) / Rojo (Faltan fotos).
 Icono Precio: Verde (Precio validado) / Rojo (Falta precio).
Funcionalidad Clave: Permite cambiar el Estado Comercial a "DISPONIBLE" manualmente, incluso si la Ubicación es "Taller".
6.2. Conector Motorflash (Salida / Push)
Automatización: Proceso nocturno (o bajo demanda con botón "Sincronizar Ahora") que genera el fichero CSV para Motorflash/Coches.net.
Reglas de Negocio (El Filtro de Calidad):
El sistema escanea todos los coches con Estado Comercial = "DISPONIBLE".
Validación Bloqueante: Si Fotos < 5 O Precio = 0 -> NO se incluye en el fichero y genera una alerta al comercial ("Coche X no se ha publicado por falta de datos").
Log de Exportación: Histórico de ficheros enviados para saber qué día se publicó cada coche.
6.3. Sincronización Inversa CRM4You (Entrada / Pull)
Objetivo: Cerrar el ciclo de venta automáticamente.
Método: Importación de CSV diario (o conexión API si la facilitan).
Lógica de Actualización:
El sistema lee el fichero de CRM4You.
Busca la matrícula en nuestra base de datos.
Si CRM dice "VENDIDO":
Cambia Estado Comercial a "VENDIDO".
Trigger Crítico: Dispara la alerta "DAR DE BAJA SEGURO FLOTA", a los 15 días.
Libera la ubicación logística (para que sepamos que ese hueco en la campa/tienda se va a quedar libre pronto).
Si CRM dice "RESERVADO":
Cambia Estado Comercial a "RESERVADO".
Bloquea la modificación de precio y fotos (para no tocar un coche que ya tiene novio).
6.4. INGESTA Y CONECTIVIDAD ASEGURADORA
Paso 1: Carga Masiva: Importación Excel de proveedores (creación en "Tránsito").
Paso 2: Recepción App (Campa): Vinculación QR + Auditoría de 5 fotos + Daños.
Paso 3: CONECTOR API SEGUROS OCCIDENT (AUTOMATIZACIÓN):
Integración Directa: El sistema se conecta vía API REST con los servicios de Seguros Occident.
Trigger de Alta: Al marcar el check "Activar Seguro" en la recepción, el sistema envía la petición de ALTA de póliza en tiempo real.
Respuesta: Recibe y guarda el Nº de Póliza Provisional automáticamente en la ficha del coche.
6.5. GESTIÓN DE REVERSIÓN (ROLLBACK DE VENTAS):
Problemática: Una operación se cae después de firmar o el cliente no consigue financiación.
Solución: Botón de "Reabrir Expediente" (Acceso restringido a Gerencia/Jefes de Ventas).
Lógica del Sistema:
El coche vuelve a estado "Disponible".
Se reactiva la publicación en Motorflash.
API Occident: El sistema evalúa si debe reactivar el seguro de flota automáticamente.
Trazabilidad: Queda registro en el historial de que el coche fue "Vendido y Reabierto".
ARQUITECTURA VISUAL (DESIGN SYSTEM)
Objetivo: Usabilidad y consistencia de marca.
Centralización UI/UX: Implementación de un Design System Global.
Definición única de tipografías, paleta de colores corporativa y componentes (botones, tarjetas, inputs).
Permite cambiar el "Look & Feel" de toda la aplicación desde un único archivo de configuración (CSS Variables / Theme Provider), facilitando futuras marcas blancas o cambios de branding del grupo.


ANEXO FINAL: GESTIÓN DE ALERTAS Y USUARIOS
(Para cerrar el documento técnico con lo que me pediste de las alertas del Excel)
A. Panel de Administración
Gestión de Usuarios: Alta/Baja de empleados y asignación de Roles (Ver solo su empresa o ver todo el grupo).
Gestión de Talleres: Aquí se generan los PINES DE SEGURIDAD para los proveedores (ej: "Talleres Pepe -> PIN 1234").
B. Matriz de Alertas Automáticas (Backend)
El sistema vigilará estos casos 24/7 y mandará avisos al Dashboard:
DISPARADOR
CONDICIÓN
ACCIÓN / AVISO
Seguridad Financiera
Estado = "Vendido" AND Seguro = "Activo"
🔴 ALERTA CRÍTICA: "BAJA SEGURO URGENTE"
Logística
Estado = "Vendido" AND Llaves = "1"
🟠 TAREA: "Reclamar 2ª Llave a Proveedor/Cliente"
Operativa
Días en Taller > 15
🟠 AVISO: "Retraso en reparación (Matrícula XXX)"
Documental
Estado = "Disponible" AND Faltan Papeles
🟡 AVISO: "Coche sin Ficha Técnica escaneada"

