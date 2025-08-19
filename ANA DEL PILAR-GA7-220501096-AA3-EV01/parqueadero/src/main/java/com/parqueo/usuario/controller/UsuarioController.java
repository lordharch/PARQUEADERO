package com.parqueo.usuario.controller;
import com.parqueo.usuario.model.Usuario;
import com.parqueo.usuario.service.UsuarioService;
import org.springframework.web.bind.annotation.*;
import java.util.List;
@RestController
@RequestMapping("/usuarios")
public class UsuarioController {
 private final UsuarioService servicio;
 public UsuarioController(UsuarioService servicio) {
 this.servicio = servicio;
 }
 @GetMapping
 public List<Usuario> listar() {
 return servicio.listar();
 }
 @PostMapping
 public Usuario guardar(@RequestBody Usuario usuario) {
 return servicio.guardar(usuario);
 }
}