package com.parqueo.usuario;

import org.springframework.boot.SpringApplication;
import org.springframework.boot.autoconfigure.SpringBootApplication;
<<<<<<< HEAD
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
=======

@SpringBootApplication
public class ParqueaderoApplication {

	public static void main(String[] args) {
		SpringApplication.run(ParqueaderoApplication.class, args);
	}

>>>>>>> 1851662 (subiendo trabajos AA2 y AA5)
}
