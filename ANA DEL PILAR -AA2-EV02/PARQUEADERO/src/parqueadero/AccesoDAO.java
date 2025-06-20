/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Classes/Class.java to edit this template
 */
package parqueadero;
import java.sql.Connection;
import java.sql.ResultSet;
import java.sql.Statement;
/**
 *
 * @author Harrison Baldion
 */
public class AccesoDAO {
     public static void listarAccesos() {
        Connection conexion = ConexionMySQL.conectar();

        if (conexion != null) {
            try {
                String sql = "SELECT * FROM acceso";
                Statement st = conexion.createStatement();
                ResultSet rs = st.executeQuery(sql);

                System.out.println("📋 Accesos registrados:");

                while (rs.next()) {
                    int id = rs.getInt("id_acceso");
                    String placa = rs.getString("placa_vehiculo");
                    int espacio = rs.getInt("id_espacio");
                    String estado = rs.getString("estado");
                    String entrada = rs.getString("fecha_entrada");
                    String salida = rs.getString("fecha_salida");

                    System.out.println(id + " | " + placa + " | Espacio: " + espacio + 
                                       " | Entrada: " + entrada + " | Salida: " + salida + 
                                       " | Estado: " + estado);
                }

                rs.close();
                st.close();
                conexion.close();

            } catch (Exception e) {
                System.out.println("❌ Error al listar accesos.");
                e.printStackTrace();
            }
        } else {
            System.out.println("❌ Conexión fallida.");
        }
    }
    
}
