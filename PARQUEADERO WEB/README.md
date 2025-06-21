
# 🚗 Parkin P.B. - Sistema de Gestión de Parqueadero

**Parkin P.B.** es un sistema web para la administración de un parqueadero. Permite gestionar usuarios, vehículos, zonas, espacios, accesos, cámaras, eventos y estadísticas.  
Este repositorio contiene tanto el frontend como el backend estructurado de forma modular.

---

## 📁 Estructura del Proyecto

```bash
php/
├── auth/             # Login, registro y sesión de usuario
├── db/               # Conexión a base de datos
├── usuarios/         # CRUD y administración de usuarios
├── vehiculos/        # CRUD de vehículos y gestión de autorizaciones
├── zonas/            # Gestión de zonas de parqueo
├── espacios/         # Administración de espacios individuales
├── camaras/          # Gestión de cámaras de seguridad
├── accesos/          # Control de entradas y salidas
├── transacciones/    # Historial y pagos
├── eventos/          # Registro de eventos e incidencias
└── estadisticas/     # Indicadores y datos agregados
```

---

## ✅ Funcionalidades Agregadas / Mejoradas

- ✅ **Reorganización modular de archivos PHP** por responsabilidad.
- ✅ **Actualización de rutas en `fetch()`** en JavaScript para seguir la nueva estructura.
- ✅ **Validación y seguridad en registro de usuarios**: se impide que un usuario se registre como administrador.
- ✅ **Manejo de sesión con `localStorage`** en login y cierre de sesión.
- ✅ **Carga dinámica del dashboard** con información de usuario, vehículos, historial y zonas.
- ✅ **Redirección automática** si el usuario no ha iniciado sesión.
- ✅ **Mensajes de error y depuración mejorados** en login y registro.

---

## 📲 Tecnologías Utilizadas

- **Frontend**: HTML, CSS, JavaScript
- **Backend**: PHP + PDO
- **Base de datos**: MySQL
- **Estilo visual**: Dark UI con badges, tablas y cards modernas

---

## 🚀 Cómo iniciar el proyecto

1. Clona el repositorio.
2. Coloca los archivos en tu servidor local (ej: `htdocs/PARQUEADERO2` en XAMPP).
3. Configura tu base de datos y edita `php/db/db_connect.php` con tus credenciales.
4. Abre `registro.html` para registrar usuarios y `panel.html` para acceder al dashboard.

---

## 🛡️ Nota de seguridad

> Los usuarios que se registren desde el frontend **solo podrán elegir tipos de usuario no administradores** como "Regular", "Premium" o "Empleado".  
> La gestión de administradores está reservada para el backend o control interno.

---

## 👨‍💻 Autor

**Ana del Pilar Bermeo Lozano**  
Proyecto para la asignatura de desarrollo de software.  
Versión: Junio 2025
