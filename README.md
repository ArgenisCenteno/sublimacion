# Dkm0-core
Proyecto de desarrollo Dkm0 Core

## 2. REGLAS DE CÓDIGO Naimitech S.L.

> **⚠️ IMPORTANTE PARA TODOS LOS DESARROLLADORES:**
> El cumplimiento de estas reglas no es opcional. Cualquier código que no respete estos estándares será rechazado automáticamente en la revisión (Code Review) por el Tech Lead y la tarea no se dará por completada en Notion.

### 2.1 FLUJO DE TRABAJO (Git Workflow)
* **La rama `main` es sagrada:** Representa lo que está 100% estable y en Producción.
* **La rama `develop` es integración:** Representa el entorno de Staging/Pruebas.
* **Prohibido el Push Directo:** Nadie, bajo ninguna circunstancia, sube código directamente a `main` o a `develop`.
* **Nomenclatura de Ramas:** Toda nueva funcionalidad o corrección debe hacerse en una rama separada **que nazca de `develop`** (NUNCA de `main`). Usa estos prefijos obligatorios:
  * `feature/nombre-de-la-tarea` (Ej: `feature/Desarrollo`)
  * `bugfix/nombre-del-error` (Ej: `bugfix/crash-formulario-coches`)
* **Commits Semánticos:** Los mensajes de commit deben ser claros y explicar qué hace el cambio.
  * ✅ *Bien:* `feat: añade endpoint para listar vehículos`
  * ❌ *Mal:* `arreglos varios` o `cambios en el index`

### 2.2 PULL REQUESTS (PR) Y REVISIÓN (Code Review)
Toda tarea terminada, en Notion debe acompañarse de un Pull Request en GitHub dirigido a la rama `develop`.

* **Link a la tarea:** En la descripción del PR, debes pegar el enlace a la tarjeta de Notion correspondiente.
* **Auto-Merge Prohibido:** El desarrollador crea el PR, pero **solo el Tech Lead (Reinaldo) o el CEO pueden aprobarlo** (Merge) tras revisarlo.
* **Regla del "Pasa en mi máquina":** Antes de abrir un PR, el código debe funcionar sin errores en tu entorno local. No se revisan PRs con código a medias.

### 2.3 ESTÁNDARES Y LIMPIEZA DE CÓDIGO (Clean Code)
Naimitech desarrolla software escalable de alto valor. Escribe código pensando en que mañana otra persona tendrá que leerlo.

* **Idioma del Código:** Variables, funciones, clases y base de datos van SIEMPRE EN INGLÉS (Ej: `getUser()`, no `obtenerUsuario()`).
* **Idioma de la Documentación:** Los comentarios explicativos y los README pueden ir en ESPAÑOL.
* **Formateo Automático:** Es obligatorio usar Linter y Formateador (según el estándar de Laravel/Vue). Si el código no está formateado, se rechaza.
* **No reinventes la rueda:** Si hay una librería segura y estándar que resuelve un problema complejo, úsala. Prioriza la velocidad de entrega sin perder seguridad ni la experiencia SPA fluida.

### 2.4 SEGURIDAD ABSOLUTA (Tolerancia Cero)
* **Secretos y `.env`:** NUNCA, bajo ningún concepto, se hace un commit de contraseñas, tokens de API o cadenas de conexión a la base de datos.
* El archivo `.env` debe estar siempre en el `.gitignore`.
* Sube únicamente un archivo `.env.example` con las variables vacías para saber qué claves se necesitan en el servidor.

### 2.5 ARQUITECTURA Y BASE DE DATOS
* **Migraciones:** Todo cambio en la estructura de la base de datos debe hacerse mediante archivos de migración de Laravel. Prohibido modificar las tablas a mano en la base de datos.
* **Modularidad:** Evita los "archivos espagueti". Respeta la arquitectura MVC separando la lógica en Controladores, Modelos y Rutas..