package com.parqueo.usuario.model;
import jakarta.persistence.*;
@Entity
public class Usuario {
 @Id
 @GeneratedValue(strategy = GenerationType.IDENTITY)
 private Long id;
 private String nombre;
 private String correo;
 private String telefono;
 // Getters
 public Long getId() {
 return id;
 }
 public String getNombre() {
 return nombre;
 }
 public String getCorreo() {
 return correo;
 }
 public String getTelefono() {
 return telefono;
 }
 // Setters
 public void setId(Long id) {
 this.id = id;
 }
 public void setNombre(String nombre) {
 this.nombre = nombre;
 }
 public void setCorreo(String correo) {
 this.correo = correo;
 }
 public void setTelefono(String telefono) {
 this.telefono = telefono;
 }
}