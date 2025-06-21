/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/JSP_Servlet/Servlet.java to edit this template
 */
package com.ejemplo.controlador;

import javax.servlet.*;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.*;
import java.io.IOException;
import java.util.*;

@WebServlet("/usuario")
public class UsuarioServlet extends HttpServlet {
    
    // Lista para almacenar usuarios en memoria
    private List<Usuario> usuarios;
    
    @Override
    public void init() throws ServletException {
        super.init();
        usuarios = new ArrayList<>();
        
        // Agregar algunos usuarios de ejemplo
        usuarios.add(new Usuario("Juan Pérez", "juan.perez@ejemplo.com"));
        usuarios.add(new Usuario("María García", "maria.garcia@ejemplo.com"));
    }
    
    @Override
    protected void doGet(HttpServletRequest req, HttpServletResponse resp) 
            throws ServletException, IOException {
        // Manejo de peticiones GET para mostrar la lista de usuarios
        req.setAttribute("usuarios", usuarios);
        RequestDispatcher dispatcher = req.getRequestDispatcher("listarUsuarios.jsp");
        dispatcher.forward(req, resp);
    }
    
    @Override
    protected void doPost(HttpServletRequest req, HttpServletResponse resp) 
            throws ServletException, IOException {
        
        // Obtener parámetros del formulario
        String nombre = req.getParameter("nombre");
        String correo = req.getParameter("correo");
        
        // Validación básica
        if (nombre != null && !nombre.trim().isEmpty() && 
            correo != null && !correo.trim().isEmpty()) {
            
            // Crear y agregar el nuevo usuario
            Usuario nuevoUsuario = new Usuario(nombre.trim(), correo.trim());
            usuarios.add(nuevoUsuario);
            
            // Mensaje de éxito
            req.setAttribute("mensaje", "Usuario registrado exitosamente");
        } else {
            // Mensaje de error
            req.setAttribute("error", "Por favor, complete todos los campos");
        }
        
        // Enviar la lista actualizada a la vista
        req.setAttribute("usuarios", usuarios);
        
        // Redirigir a la página de listado
        RequestDispatcher dispatcher = req.getRequestDispatcher("listarUsuarios.jsp");
        dispatcher.forward(req, resp);
    }
    
    // Clase interna para representar un Usuario
    public static class Usuario {
        private String nombre;
        private String correo;
        private Date fechaRegistro;
        
        public Usuario(String nombre, String correo) {
            this.nombre = nombre;
            this.correo = correo;
            this.fechaRegistro = new Date();
        }
        
        // Getters
        public String getNombre() {
            return nombre;
        }
        
        public String getCorreo() {
            return correo;
        }
        
        public Date getFechaRegistro() {
            return fechaRegistro;
        }
        
        // Setters
        public void setNombre(String nombre) {
            this.nombre = nombre;
        }
        
        public void setCorreo(String correo) {
            this.correo = correo;
        }
        
        @Override
        public String toString() {
            return "Usuario{" +
                    "nombre='" + nombre + '\'' +
                    ", correo='" + correo + '\'' +
                    ", fechaRegistro=" + fechaRegistro +
                    '}';
        }
    }
}