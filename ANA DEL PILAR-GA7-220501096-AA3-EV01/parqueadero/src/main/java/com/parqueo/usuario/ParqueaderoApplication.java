package com.parqueo.usuario;

import org.springframework.boot.SpringApplication;
import org.springframework.boot.autoconfigure.SpringBootApplication;
import org.springframework.context.annotation.ComponentScan;
import org.springframework.data.jpa.repository.config.EnableJpaRepositories;
import org.springframework.boot.autoconfigure.domain.EntityScan;

@SpringBootApplication
@ComponentScan(basePackages = {
        "com.parqueo.usuario",
        "com.parqueo.vehiculo"
})
@EnableJpaRepositories(basePackages = {
        "com.parqueo.usuario.repository",
        "com.parqueo.vehiculo.repository"
})
@EntityScan(basePackages = {
        "com.parqueo.usuario.model",
        "com.parqueo.vehiculo.model"
})
public class ParqueaderoApplication {

    public static void main(String[] args) {
        SpringApplication.run(ParqueaderoApplication.class, args);
    }
}
