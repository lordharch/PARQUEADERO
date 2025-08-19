package com.parqueo.vehiculo.controller;

import com.parqueo.vehiculo.model.Vehiculo;
import com.parqueo.vehiculo.service.VehiculoService;
import org.springframework.web.bind.annotation.*;

import java.util.List;

@RestController
@RequestMapping("/vehiculos")
public class VehiculoController {

    private final VehiculoService servicio;

    public VehiculoController(VehiculoService servicio) {
        this.servicio = servicio;
    }

    @GetMapping
    public List<Vehiculo> listar() {
        return servicio.listar();
    }

    @PostMapping
    public Vehiculo guardar(@RequestBody Vehiculo vehiculo) {
        return servicio.guardar(vehiculo);
    }
}
