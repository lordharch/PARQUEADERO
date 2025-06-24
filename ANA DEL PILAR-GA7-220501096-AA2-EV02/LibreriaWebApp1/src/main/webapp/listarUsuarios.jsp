<%-- 
    Document   : listarUsuarios
    Created on : 20 jun 2025, 21:05:53
    Author     : Harrison Baldion
--%>
<%@ page import="java.util.List" %>
<%@ page import="com.ejemplo.controlador.UsuarioServlet.Usuario" %>
<%@ page import="java.text.SimpleDateFormat" %>
<%@page contentType="text/html" pageEncoding="UTF-8"%>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Usuarios - Librería Web</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h2 {
            color: #333;
            text-align: center;
            margin-bottom: 30px;
        }
        .mensaje {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 4px;
            text-align: center;
        }
        .exito {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #4CAF50;
            color: white;
        }
        tr:hover {
            background-color: #f5f5f5;
        }
        .no-usuarios {
            text-align: center;
            color: #666;
            font-style: italic;
            padding: 40px;
        }
        .botones {
            text-align: center;
            margin-top: 30px;
        }
        .boton {
            display: inline-block;
            padding: 10px 20px;
            margin: 0 10px;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            border: none;
            cursor: pointer;
        }
        .boton:hover {
            background-color: #45a049;
        }
        .boton-secondary {
            background-color: #6c757d;
        }
        .boton-secondary:hover {
            background-color: #5a6268;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Lista de Usuarios Registrados</h2>
        
        <!-- Mostrar mensajes de éxito o error -->
        <% 
        String mensaje = (String) request.getAttribute("mensaje");
        String error = (String) request.getAttribute("error");
        if (mensaje != null) { 
        %>
            <div class="mensaje exito"><%= mensaje %></div>
        <% } 
        if (error != null) { 
        %>
            <div class="mensaje error"><%= error %></div>
        <% } %>
        
        <!-- Lista de usuarios -->
        <%
        List<Usuario> usuarios = (List<Usuario>) request.getAttribute("usuarios");
        SimpleDateFormat sdf = new SimpleDateFormat("dd/MM/yyyy HH:mm");
        
        if (usuarios != null && !usuarios.isEmpty()) {
        %>
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nombre</th>
                        <th>Correo Electrónico</th>
                        <th>Fecha de Registro</th>
                    </tr>
                </thead>
                <tbody>
                    <% 
                    int contador = 1;
                    for (Usuario usuario : usuarios) { 
                    %>
                        <tr>
                            <td><%= contador++ %></td>
                            <td><%= usuario.getNombre() %></td>
                            <td><%= usuario.getCorreo() %></td>
                            <td><%= sdf.format(usuario.getFechaRegistro()) %></td>
                        </tr>
                    <% } %>
                </tbody>
            </table>
            
            <p><strong>Total de usuarios registrados:</strong> <%= usuarios.size() %></p>
            
        <% } else { %>
            <div class="no-usuarios">
                <p>No hay usuarios registrados aún.</p>
            </div>
        <% } %>
        
        <div class="botones">
            <a href="index.html" class="boton">Registrar Nuevo Usuario</a>
            <a href="menu.html" class="boton boton-secondary">Volver al Menú</a>
        </div>
    </div>
</body>
</html>
