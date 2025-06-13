/**
 * Sistema de Estacionamiento - Adaptado para BD real
 * Compatible con PHP backend y BD MySQL
 */

// Módulo para gestión de sesiones
const SesionManager = {
  // Obtener información de la sesión actual
  obtenerSesion: function() {
    return JSON.parse(localStorage.getItem("sesionActiva"));
  },
  
  // Guardar sesión en localStorage
  guardarSesion: function(tipo, usuario) {
    localStorage.setItem("sesionActiva", JSON.stringify({ tipo, usuario }));
  },
  
  // Cerrar sesión
  cerrarSesion: function() {
    localStorage.removeItem("sesionActiva");
    window.location.href = "index.html";
  },
  
  // Verificar si hay una sesión activa válida
  verificarSesion: function(tipoRequerido) {
    const sesion = this.obtenerSesion();
    if (!sesion || sesion.tipo !== tipoRequerido) {
      alert("Acceso no autorizado. Inicia sesión primero.");
      window.location.href = "login.html";
      return false;
    }
    return true;
  }
};

// Módulo para gestión de usuarios - Adaptado para BD
const UsuarioManager = {
  // Registrar un nuevo usuario
  registrar: async function(datosUsuario) {
    try {
      const response = await fetch('backend/registro.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(datosUsuario)
      });
      
      const resultado = await response.json();
      return resultado;
    } catch (error) {
      console.error('Error al registrar usuario:', error);
      return { exito: false, mensaje: "Error de conexión al servidor." };
    }
  },
  
  // Verificar credenciales de login - SIN CONTRASEÑA
  verificarCredenciales: async function(identificacion, correo) {
    try {
      // Admin hardcodeado
      if (identificacion === "admin" && correo === "admin@parkinpb.com") {
        return { exito: true, tipo: "admin", usuario: { identificacion: "admin", nombre_completo: "Administrador" } };
      }
      
      const response = await fetch('backend/login.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({ identificacion, correo })
      });
      
      const resultado = await response.json();
      return resultado;
    } catch (error) {
      console.error('Error al verificar credenciales:', error);
      return { exito: false, mensaje: "Error de conexión al servidor." };
    }
  },
  
  // Obtener lista de todos los usuarios registrados
  obtenerTodosLosUsuarios: async function() {
    try {
      const response = await fetch('backend/obtener_usuarios.php');
      const usuarios = await response.json();
      return usuarios;
    } catch (error) {
      console.error('Error al obtener usuarios:', error);
      return [];
    }
  },
  
  // Buscar usuario por placa
  buscarPorPlaca: async function(placa) {
    try {
      const response = await fetch(`backend/buscar_por_placa.php?placa=${encodeURIComponent(placa)}`);
      const resultado = await response.json();
      return resultado.usuario || null;
    } catch (error) {
      console.error('Error al buscar por placa:', error);
      return null;
    }
  }
};

// Módulo para gestionar espacios de estacionamiento - Adaptado para BD
const EspaciosManager = {
  // Obtener todos los espacios
  obtenerTodos: async function() {
    try {
      const response = await fetch('backend/obtener_espacios.php');
      const espacios = await response.json();
      return espacios;
    } catch (error) {
      console.error('Error al obtener espacios:', error);
      // Fallback con datos de ejemplo
      return [
        { id_espacio: 1, codigo_espacio: "A-01", zona: "Bloque A", estado: "disponible" },
        { id_espacio: 2, codigo_espacio: "A-02", zona: "Bloque A", estado: "ocupado" },
        { id_espacio: 3, codigo_espacio: "B-03", zona: "Bloque B", estado: "disponible" }
      ];
    }
  }
};

// Módulo para gestionar UI
const UIManager = {
  // Mostrar mensaje temporal
  mostrarMensaje: function(elemento, texto, tipo) {
    elemento.textContent = texto;
    
    if (tipo === "exito") {
      elemento.style.color = "green";
    } else if (tipo === "error") {
      elemento.style.color = "red";
    }
    
    if (elemento.classList) {
      elemento.className = tipo;
      elemento.classList.add("visible");
      
      setTimeout(() => {
        elemento.classList.remove("visible");
      }, 5000);
    }
  },
  
  // Actualizar menú según el tipo de sesión
  actualizarMenu: function() {
    const menu = document.getElementById("menuLinks");
    const sesion = SesionManager.obtenerSesion();
    
    if (menu && sesion) {
      if (sesion.tipo === "usuario") {
        menu.innerHTML = `
          <li><a href="usuario.html">Panel Usuario</a></li>
          <li><a href="#" onclick="SesionManager.cerrarSesion()">Cerrar sesión</a></li>
        `;
      } else if (sesion.tipo === "admin") {
        menu.innerHTML = `
          <li><a href="admin.html">Panel Admin</a></li>
          <li><a href="#" onclick="SesionManager.cerrarSesion()">Cerrar sesión</a></li>
        `;
      }
    }
  },
  
  // Renderizar tabla de espacios - Adaptado para estructura BD
  renderizarTablaEspacios: async function(tabla) {
    tabla.innerHTML = "<tr><td colspan='3'>Cargando espacios...</td></tr>";
    
    const espacios = await EspaciosManager.obtenerTodos();
    tabla.innerHTML = "";
    
    espacios.forEach(espacio => {
      const fila = document.createElement("tr");
      const estadoColor = {
        'disponible': 'green',
        'ocupado': 'red',
        'reservado': 'orange',
        'mantenimiento': 'gray'
      };
      
      fila.innerHTML = `
        <td>${espacio.id_espacio}</td>
        <td>${espacio.codigo_espacio} - ${espacio.zona || 'N/A'}</td>
        <td style="color: ${estadoColor[espacio.estado] || 'black'};">
          ${espacio.estado.charAt(0).toUpperCase() + espacio.estado.slice(1)}
        </td>
      `;
      tabla.appendChild(fila);
    });
  },
  
  // Renderizar tabla de usuarios para admin - Adaptado para BD
  renderizarTablaUsuarios: async function(tabla) {
    tabla.innerHTML = "<tr><td colspan='4'>Cargando usuarios...</td></tr>";
    
    const usuarios = await UsuarioManager.obtenerTodosLosUsuarios();
    tabla.innerHTML = "";
    
    usuarios.forEach(usuario => {
      const fila = document.createElement("tr");
      fila.innerHTML = `
        <td>${usuario.identificacion}</td>
        <td>${usuario.nombre_completo}</td>
        <td>${usuario.correo}</td>
        <td>${usuario.telefono}</td>
      `;
      tabla.appendChild(fila);
    });
  }
};

// Módulo para gestión de gráficos
const GraficosManager = {
  // Inicializar gráficos
  inicializar: function() {
    this.inicializarGraficoOcupacion();
    this.inicializarGraficoTiposVehiculos();
  },
  
  // Gráfico de ocupación
  inicializarGraficoOcupacion: function() {
    const occupationCtx = document.getElementById('occupationChart');
    if (!occupationCtx) return;
    
    const ctx = occupationCtx.getContext('2d');
    const chart = new Chart(ctx, {
      type: 'line',
      data: {
        labels: ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'],
        datasets: [{
          label: 'Ocupación',
          data: [75, 65, 80, 90, 85, 60, 40],
          fill: true,
          backgroundColor: 'rgba(101, 88, 245, 0.2)',
          borderColor: 'rgba(101, 88, 245, 1)',
          tension: 0.4
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
          y: {
            beginAtZero: true,
            max: 100,
            ticks: {
              callback: function(value) {
                return value + '%';
              }
            }
          }
        }
      }
    });
  },
  
  // Gráfico de tipos de vehículos
  inicializarGraficoTiposVehiculos: function() {
    const vehicleTypesCtx = document.getElementById('vehicleTypesChart');
    if (!vehicleTypesCtx) return;
    
    const ctx = vehicleTypesCtx.getContext('2d');
    const chart = new Chart(ctx, {
      type: 'pie',
      data: {
        labels: ['Automóviles', 'Motocicletas', 'Camionetas', 'Bicicletas'],
        datasets: [{
          data: [60, 25, 10, 5],
          backgroundColor: [
            'rgba(101, 88, 245, 0.8)',
            'rgba(46, 204, 113, 0.8)',
            'rgba(52, 152, 219, 0.8)',
            'rgba(231, 76, 60, 0.8)'
          ],
          borderWidth: 1
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false
      }
    });
  }
};

// Módulo para gestión de modales
const ModalManager = {
  // Inicializar eventos de modal
  inicializar: function() {
    const addUserBtn = document.querySelector('.btn-primary');
    const modalBackdrop = document.getElementById('userModal');
    const closeModalBtn = document.getElementById('closeModal');
    const cancelModalBtn = document.getElementById('cancelModal');
    
    if (!addUserBtn || !modalBackdrop || !closeModalBtn || !cancelModalBtn) return;
    
    addUserBtn.addEventListener('click', function() {
      modalBackdrop.style.display = 'flex';
    });
    
    closeModalBtn.addEventListener('click', function() {
      modalBackdrop.style.display = 'none';
    });
    
    cancelModalBtn.addEventListener('click', function() {
      modalBackdrop.style.display = 'none';
    });
  }
};

// Inicialización de eventos cuando el DOM está listo
document.addEventListener("DOMContentLoaded", function() {
  // Actualizar el menú según la sesión
  UIManager.actualizarMenu();
  
  // Inicializar formulario de registro - ADAPTADO PARA BD
  const registroForm = document.getElementById("registroForm");
  const mensajeRegistro = document.getElementById("mensajeRegistro");
  
  if (registroForm) {
    registroForm.addEventListener("submit", async function(e) {
      e.preventDefault();
      
      // Validar contraseñas coincidan
      const contrasena = document.getElementById("contrasena").value;
      const confirmarContrasena = document.getElementById("confirmarContrasena").value;
      
      if (contrasena !== confirmarContrasena) {
        UIManager.mostrarMensaje(mensajeRegistro, "Las contraseñas no coinciden.", "error");
        return;
      }
      
      const datosUsuario = {
        identificacion: document.getElementById("identificacion").value.trim(),
        nombre_completo: document.getElementById("nombre_completo").value.trim(),
        correo: document.getElementById("correo").value.trim(),
        telefono: document.getElementById("telefono").value.trim(),
        id_tipo_usuario: document.getElementById("id_tipo_usuario").value,
        placa: document.getElementById("placa").value.trim().toUpperCase()
      };
      
      // Mostrar mensaje de carga
      UIManager.mostrarMensaje(mensajeRegistro, "Registrando usuario...", "info");
      
      const resultado = await UsuarioManager.registrar(datosUsuario);
      UIManager.mostrarMensaje(mensajeRegistro, resultado.mensaje, resultado.exito ? "exito" : "error");
      
      if (resultado.exito) {
        setTimeout(() => {
          window.location.href = "login.html";
        }, 2000);
      }
    });
  }
  
  // Inicializar formulario de login - SIN CONTRASEÑA
  const loginFormGlobal = document.getElementById("loginForm");
  const mensajeLoginGlobal = document.getElementById("mensajeLogin");
  
  if (loginFormGlobal) {
    loginFormGlobal.addEventListener("submit", async function(e) {
      e.preventDefault();
      
      const identificacion = document.getElementById("identificacion").value.trim();
      const correo = document.getElementById("correo").value.trim();
      
      if (!identificacion || !correo) {
        UIManager.mostrarMensaje(mensajeLoginGlobal, "Por favor ingrese identificación y correo.", "error");
        return;
      }
      
      // Mostrar mensaje de carga
      UIManager.mostrarMensaje(mensajeLoginGlobal, "Verificando credenciales...", "info");
      
      const resultado = await UsuarioManager.verificarCredenciales(identificacion, correo);
      
      if (resultado.exito) {
        SesionManager.guardarSesion(resultado.tipo, resultado.usuario);
        window.location.href = resultado.tipo === "admin" ? "admin.html" : "usuario.html";
      } else {
        UIManager.mostrarMensaje(mensajeLoginGlobal, resultado.mensaje, "error");
      }
    });
  }
  
  // Inicializar tabla de espacios
  const tablaEspacios = document.getElementById("tablaEspacios");
  if (tablaEspacios) {
    if (SesionManager.verificarSesion("usuario")) {
      UIManager.renderizarTablaEspacios(tablaEspacios);
    }
  }
  
  // Inicializar formulario de retiro
  const retiroForm = document.getElementById("retiroForm");
  const mensajeRetiro = document.getElementById("mensajeRetiro");
  
  if (retiroForm) {
    if (SesionManager.verificarSesion("usuario")) {
      retiroForm.addEventListener("submit", async function(e) {
        e.preventDefault();
        
        const placaInput = document.getElementById("placaRetiro");
        const placa = placaInput.value.trim().toUpperCase();
        
        if (!placa) {
          UIManager.mostrarMensaje(mensajeRetiro, "Por favor, ingrese un número de placa válido.", "error");
          return;
        }
        
        UIManager.mostrarMensaje(mensajeRetiro, "Buscando vehículo...", "info");
        
        const usuarioEncontrado = await UsuarioManager.buscarPorPlaca(placa);
        
        if (usuarioEncontrado) {
          UIManager.mostrarMensaje(mensajeRetiro, `Vehículo con placa ${placa} ha sido retirado exitosamente.`, "exito");
        } else {
          UIManager.mostrarMensaje(mensajeRetiro, `No se encontró ningún vehículo con esa placa.`, "error");
        }
        
        retiroForm.reset();
      });
    }
  }
  
  // Inicializar formulario de contacto
  const contactoForm = document.getElementById("contactoForm");
  const mensajeContacto = document.getElementById("mensajeContacto");
  
  if (contactoForm) {
    contactoForm.addEventListener("submit", function(e) {
      e.preventDefault();
      
      UIManager.mostrarMensaje(mensajeContacto, "¡Mensaje enviado correctamente!", "exito");
      contactoForm.reset();
    });
  }
  
  // Inicializar tabla de usuarios para admin
  const tablaUsuarios = document.getElementById("tablaUsuarios");
  if (tablaUsuarios) {
    if (SesionManager.verificarSesion("admin")) {
      UIManager.renderizarTablaUsuarios(tablaUsuarios);
    }
  }
  
  // Inicializar gráficos
  GraficosManager.inicializar();
  
  // Inicializar modales
  ModalManager.inicializar();
});

// Exponer la función cerrarSesion globalmente para los enlaces del menú
window.cerrarSesion = SesionManager.cerrarSesion;