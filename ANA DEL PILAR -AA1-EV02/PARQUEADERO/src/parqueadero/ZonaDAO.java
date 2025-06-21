/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Classes/Class.java to edit this template
 */
package parqueadero;

import java.sql.Connection;
import java.sql.ResultSet;
import java.sql.Statement;

public class ZonaDAO {

    public static void listarZonas() {
        Connection conexion = ConexionMySQL.conectar();

        if (conexion != null) {
            try {
                String sql = "SELECT * FROM zonaestacionamiento";
                Statement st = conexion.createStatement();
                ResultSet rs = st.executeQuery(sql);

                System.out.println("📋 Zonas de estacionamiento:");

                while (rs.next()) {
                    int idZona = rs.getInt("id_zona");
                    String nombre = rs.getString("nombre");
                    int capacidad = rs.getInt("capacidad_maxima");

                    System.out.println("Zona " + idZona + ": " + nombre + " | Capacidad: " + capacidad);
                }

                rs.close();
                st.close();
                conexion.close();

            } catch (Exception e) {
                System.out.println("❌ Error al listar zonas.");
                e.printStackTrace();
            }
        } else {
            System.out.println("❌ Conexión fallida.");
        }
    }
}