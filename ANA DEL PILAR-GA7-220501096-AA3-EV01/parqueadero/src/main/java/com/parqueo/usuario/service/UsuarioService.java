package com.parqueo.usuario.service;
import com.parqueo.usuario.model.Usuario;
import com.parqueo.usuario.repository.UsuarioRepository;
import org.springframework.stereotype.Service;
import java.util.List;
@Service
public class UsuarioService {
 private final UsuarioRepository repositorio;
 public UsuarioService(UsuarioRepository repositorio) {
 this.repositorio = repositorio;
 }
 public List<Usuario> listar() {
 return repositorio.findAll();
 }
 public Usuario guardar(Usuario usuario) {
 return repositorio.save(usuario);
 }
}
