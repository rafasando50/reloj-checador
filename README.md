# Sistema de Control de Asistencia (EG Access - Reloj Checador)

Este es un sistema moderno, responsivo y de alto rendimiento para el control de asistencia de empleados mediante código QR dinámico. El sistema está diseñado en una arquitectura desacoplada utilizando **Astro** para el frontend y **PHP/MySQL** para el backend (API), garantizando tiempos de carga ultrarrápidos, soporte nativo para Tema Oscuro/Claro y una integración segura para evitar fraudes en el registro.

---

## 🚀 Características Principales

1. **Escáner en Modo Kiosco**:
   - Permite registrar entradas y salidas utilizando la cámara de cualquier dispositivo.
   - Cuenta con soporte de instrucciones de voz opcionales para guiar al usuario.
   - Permite predefinir la ubicación/estación desde la cual se realiza el registro (ej. Recepción, Almacén, Remoto).

2. **Seguridad contra Fraude (QR Dinámico)**:
   - El QR generado para el empleado en la interfaz cliente expira automáticamente después de **15 segundos** (`employee_id|timestamp|SALT`).
   - Evita que los empleados compartan fotos o capturas de pantalla de sus credenciales para registrar asistencia de manera remota.

3. **Panel de Administración Inteligente**:
   - Métricas en tiempo real: registros del día, retardos semanales, personal activo y más.
   - Historial de actividad reciente con actualización inmediata.

4. **Gestión de Personal**:
   - Alta, modificación, activación y desactivación de empleados.
   - Asignación de departamentos, puestos, empresas y horarios personalizados (Hora de entrada asignada individualmente).
   - Generación dinámica de credenciales digitales listas para imprimir o escanear.

5. **Módulo de Reportes Avanzados**:
   - Búsqueda y filtrado por rango de fechas, empresa y términos de texto (nombre, ID, etc.).
   - Cálculo automático de puntualidad basado en reglas de negocio específicas.
   - Exportación nativa y veloz a archivos Excel.

6. **Diseño Premium y Dark Mode**:
   - Interfaz con una estética limpia, moderna, con animaciones sutiles y transiciones fluidas.
   - Soporte nativo para Tema Oscuro persistente en el dispositivo.

---

## 🛠️ Stack Tecnológico

*   **Frontend**: [Astro v6](https://astro.build/) (Compilación estática en modo `output: 'static'`).
*   **Estilos**: CSS Vanilla con variables de diseño globales adaptativas.
*   **Backend**: PHP (Endpoints RESTful independientes) bajo `public/api/`.
*   **Base de Datos**: MySQL (Consultas preparadas para prevención de inyecciones SQL).
*   **Librerías principales**:
    *   `qrcode` para la generación de códigos QR de empleados.
    *   `exceljs` y `xlsx` para la exportación de reportes limpios a Excel.
    *   `bcryptjs` para la encriptación segura de contraseñas de administración.

---

## 📏 Reglas de Negocio para Puntualidad

El sistema calcula de manera inteligente el estatus de puntualidad de cada registro según la empresa de adscripción:

*   **La Casita**:
    *   Se calcula con base en la duración total de la jornada trabajada (diferencia entre entrada y salida):
        *   **A Tiempo**: $\ge 7.75$ horas trabajadas.
        *   **Retardo**: Entre $7.0$ y $7.75$ horas trabajadas.
        *   **Falta**: $< 7.0$ horas trabajadas (o si no registró salida al concluir el día).
*   **Otras Empresas (Einsur, Innova, Café Valentina, Prosime, etc.)**:
    *   Se calcula comparando la hora de entrada real contra la hora esperada configurada en el perfil del empleado (ej. `08:00:00`):
        *   **A Tiempo**: Hasta 6 minutos de tolerancia (ej. hasta las 08:06:00).
        *   **Retardo**: De 6 a 16 minutos tarde (ej. entre 08:06:01 y 08:16:00).
        *   **Falta**: Más de 16 minutos tarde (ej. después de las 08:16:00).

---

## 📁 Estructura del Proyecto

```text
reloj-checador/
├── MANUAL USUARIO.txt       # Enlaces rápidos de acceso y tokens del escáner
├── code/
│   ├── src/                 # Código fuente del Frontend (Astro)
│   │   ├── components/      # Componentes UI reutilizables (Navbar, etc.)
│   │   ├── layouts/         # Plantilla global y sistema de temas oscuros/claros
│   │   ├── lib/             # Módulos compartidos (conexión local de BD, etc.)
│   │   └── pages/           # Rutas del sitio (Acceso, Credencial, Admin Panel)
│   ├── public/              # Archivos estáticos y Backend PHP
│   │   ├── api/             # Endpoints PHP para base de datos y lógica del reloj
│   │   │   ├── admin/       # API de administración (empleados, empresas, auth)
│   │   │   ├── reports/     # API de generación y exportación de reportes
│   │   │   └── db.php       # Archivo de conexión centralizada a MySQL
│   │   ├── empresas/        # Logotipos de las empresas registradas
│   │   └── fotos/           # Fotografías de los empleados
│   ├── astro.config.mjs     # Configuración de compilación Astro (Modo Estático)
│   ├── package.json         # Scripts de NPM y dependencias del proyecto
│   └── .env                 # Variables de entorno de base de datos local
```

---

## 🔧 Configuración e Instalación Local

### Requisitos Previos

*   **Node.js**: Versión $\ge 22.12.0$
*   **Servidor Web Local**: XAMPP, MAMP, Laragon, o Docker con soporte para PHP 8.x y MySQL.

### Pasos de Instalación

1.  **Clonar el repositorio** y dirigirse al directorio de código:
    ```bash
    cd reloj-checador/code
    ```

2.  **Instalar las dependencias de Node**:
    ```bash
    npm install
    ```

3.  **Configurar Variables de Entorno**:
    Copia o edita el archivo `.env` en la raíz del directorio `/code` con tus credenciales de base de datos local:
    ```env
    DB_HOST=127.0.0.1
    DB_USER=root
    DB_PASSWORD=tu_contraseña
    DB_NAME=relojChecador
    JWT_SECRET=tu_clave_secreta_jwt
    ```

4.  **Configurar la base de datos de producción (PHP)**:
    Edita `/code/public/api/db.php` con los accesos correspondientes a tu base de datos MySQL local o de pruebas.

5.  **Ejecutar migraciones iniciales**:
    Inicializa las tablas necesarias (como la de empresas) ejecutando:
    ```bash
    node migrate_companies.js
    ```

6.  **Iniciar Servidor de Desarrollo**:
    ```bash
    npm run dev
    ```
    El frontend estará disponible en `http://localhost:4321`.

---

## 📦 Despliegue en Producción (Banahosting / cPanel)

Dado que el proyecto utiliza compilación estática (`output: 'static'`) en Astro junto con endpoints tradicionales en PHP, el despliegue es sumamente sencillo:

1.  **Compilar el proyecto**:
    ```bash
    npm run build
    ```
    Esto generará una carpeta `dist/` en `code/dist/` que contendrá todo el frontend compilado (HTML, CSS, JS) junto con la carpeta de APIs de PHP copiada directamente en su estructura.

2.  **Subir archivos por FTP o Administrador de Archivos**:
    *   Sube todo el contenido de la carpeta `dist/` al directorio raíz del dominio o subdominio asignado en Banahosting (usualmente `public_html`).
    *   Asegúrate de que la carpeta `api/` se ubique correctamente en la raíz del servidor para que los endpoints PHP respondan en `tudominio.com/api/...`.

3.  **Configurar Base de Datos en Producción**:
    *   Crea una base de datos MySQL mediante el cPanel de Banahosting.
    *   Importa el esquema de base de datos necesario.
    *   Edita el archivo `api/db.php` directamente en el servidor con los accesos del usuario y contraseña creados en tu cPanel.
