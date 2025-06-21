# parqueadero.java
Librería Web App
Sistema web para registro y gestión de usuarios utilizando Java Servlets, JSP y Maven.
📋 Descripción
Este proyecto implementa un sistema básico de gestión de usuarios con las siguientes funcionalidades:

Registro de usuarios mediante formulario HTML
Almacenamiento temporal en memoria
Visualización de usuarios registrados
Interfaz web responsive

🏗️ Estructura del Proyecto
LibreriaWebApp/
├── src/main/java/
│   └── com/ejemplo/controlador/
│       └── UsuarioServlet.java
├── src/main/webapp/
│   ├── index.html
│   ├── menu.html
│   └── listarUsuarios.jsp
├── pom.xml
└── README.md
🚀 Tecnologías Utilizadas

Java 8+
Maven - Gestión de dependencias
Jakarta EE 8 - Servlets y JSP
Apache Tomcat - Servidor de aplicaciones
HTML5/CSS3 - Frontend

📦 Instalación y Ejecución
Prerrequisitos

Java 8 o superior
Maven 3.6+
Apache Tomcat 9.0+

Pasos para ejecutar

Clonar el proyecto
bashgit clone [url-del-repositorio]
cd LibreriaWebApp

Compilar el proyecto
bashmvn clean compile

Generar el WAR
bashmvn clean package

Desplegar en Tomcat
bash# Copiar el WAR generado a la carpeta webapps de Tomcat
cp target/LibreriaWebApp.war $TOMCAT_HOME/webapps/

Iniciar Tomcat
bash$TOMCAT_HOME/bin/startup.sh  # Linux/Mac
# o
%TOMCAT_HOME%\bin\startup.bat  # Windows

Acceder a la aplicación

Abrir navegador en: http://localhost:8080/LibreriaWebApp/menu.html



🔧 Funcionalidades
Registro de Usuarios

Formulario con validación de campos
Campos: Nombre y Correo Electrónico
Validación tanto en cliente como en servidor

Gestión de Usuarios

Visualización de todos los usuarios registrados
Información mostrada: Nombre, Correo y Fecha de Registro
Contador total de usuarios

Interfaz de Usuario

Diseño responsive y moderno
Navegación intuitiva entre páginas
Mensajes de confirmación y error

📂 Archivos Principales
UsuarioServlet.java
Controlador principal que maneja:

Peticiones GET para mostrar usuarios
Peticiones POST para registrar nuevos usuarios
Validación de datos
Almacenamiento en memoria

index.html
Formulario de registro con:

Campos de entrada validados
Estilos CSS modernos
Navegación hacia otras páginas

listarUsuarios.jsp
Vista que muestra:

Tabla de usuarios registrados
Mensajes de estado
Enlaces de navegación

🎯 Objetivos Cumplidos

✅ Formulario HTML funcional
✅ Procesamiento con Servlet
✅ Almacenamiento en memoria
✅ Vista JSP con listado
✅ Validación de datos
✅ Interfaz responsive
✅ Navegación completa