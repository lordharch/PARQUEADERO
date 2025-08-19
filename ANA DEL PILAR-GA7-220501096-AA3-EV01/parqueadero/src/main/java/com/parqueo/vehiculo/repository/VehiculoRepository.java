package com.parqueo.vehiculo.repository;

import com.parqueo.vehiculo.model.Vehiculo;
import org.springframework.data.jpa.repository.JpaRepository;

public interface VehiculoRepository extends JpaRepository<Vehiculo, Long> {}
