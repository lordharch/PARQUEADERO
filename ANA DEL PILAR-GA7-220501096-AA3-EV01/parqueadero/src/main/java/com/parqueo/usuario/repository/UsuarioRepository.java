package com.parqueo.usuario.repository;
import com.parqueo.usuario.model.Usuario;
import org.springframework.data.jpa.repository.JpaRepository;
public interface UsuarioRepository extends JpaRepository<Usuario, Long> {}

