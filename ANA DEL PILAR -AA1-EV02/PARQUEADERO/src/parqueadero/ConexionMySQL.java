/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Classes/Class.java to edit this template
 */
package parqueadero;
import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.SQLException;
/**
 *
 * @author Harrison Baldion
 */
public class ConexionMySQL {
    public static Connection conectar() {
        Connection conexion = null;
        try {
            Class.forName("com.mysql.cj.jdbc.Driver");

            String url = "jdbc:mysql://localhost:3306/parqueadero_bd?useSSL=false&serverTimezone=UTC";
            String usuario = "Harry";
            String contraseña = ""; // tu contraseña aquí, o vacío si no tiene

            conexion = DriverManager.getConnection(url, usuario, contraseña);
            System.out.println("✅ Conexión exitosa a MySQL!");

        } catch (ClassNotFoundException e) {
            System.out.println("❌ Driver JDBC no encontrado.");
            e.printStackTrace();
        } catch (SQLException e) {
            System.out.println("❌ Error al conectar a MySQL.");
            e.printStackTrace();
        }

        return conexion;
    }
}
