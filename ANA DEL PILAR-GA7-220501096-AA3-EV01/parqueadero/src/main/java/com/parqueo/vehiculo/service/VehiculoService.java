package com.parqueo.vehiculo.service;

import com.parqueo.vehiculo.model.Vehiculo;
import com.parqueo.vehiculo.repository.VehiculoRepository;
import org.springframework.stereotype.Service;

import java.util.List;

@Service
public class VehiculoService {

    private final VehiculoRepository repositorio;

    public VehiculoService(VehiculoRepository repositorio) {
        this.repositorio = repositorio;
    }

    public List<Vehiculo> listar() {
        return repositorio.findAll();
    }

    public Vehiculo guardar(Vehiculo vehiculo) {
        return repositorio.save(vehiculo);
    }
}
