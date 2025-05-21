/**
 * Sistema de Estacionamiento
 * Código JavaScript organizado por módulos
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

// Módulo para gestión de usuarios
const UsuarioManager = {
  // Registrar un nuevo usuario
  registrar: function(datosUsuario) {
    const { usuario } = datosUsuario;
    
    if (localStorage.getItem(`usuario_${usuario}`)) {
      return { exito: false, mensaje: "El usuario ya existe, elige otro." };
    }
    
    localStorage.setItem(`usuario_${usuario}`, JSON.stringify(datosUsuario));
    return { exito: true, mensaje: "¡Registro exitoso!" };
  },
  
  // Verificar credenciales de login
  verificarCredenciales: function(usuario, contrasena) {
    if (usuario === "admin" && contrasena === "admin123") {
      return { exito: true, tipo: "admin" };
    }
    
    const datosGuardados = localStorage.getItem(`usuario_${usuario}`);
    if (!datosGuardados) {
      return { exito: false, mensaje: "Usuario no encontrado." };
    }
    
    const datos = JSON.parse(datosGuardados);
    if (datos.contrasena === contrasena) {
      return { exito: true, tipo: "usuario" };
    } else {
      return { exito: false, mensaje: "Contraseña incorrecta." };
    }
  },
  
  // Obtener lista de todos los usuarios registrados
  obtenerTodosLosUsuarios: function() {
    const usuarios = [];
    
    for (let i = 0; i < localStorage.length; i++) {
      const clave = localStorage.key(i);
      if (clave.startsWith("usuario_")) {
        usuarios.push(JSON.parse(localStorage.getItem(clave)));
      }
    }
    
    return usuarios;
  },
  
  // Buscar usuario por placa
  buscarPorPlaca: function(placa) {
    placa = placa.trim().toUpperCase();
    
    for (let i = 0; i < localStorage.length; i++) {
      const clave = localStorage.key(i);
      if (clave.startsWith("usuario_")) {
        const datos = JSON.parse(localStorage.getItem(clave));
        if (datos.placa.toUpperCase() === placa) {
          return datos;
        }
      }
    }
    
    return null;
  }
};

// Módulo para gestionar espacios de estacionamiento
const EspaciosManager = {
  // Datos de espacios disponibles
  espacios: [
    { id: 1, ubicacion: "Bloque A - 01", estado: "Disponible" },
    { id: 2, ubicacion: "Bloque A - 02", estado: "Ocupado" },
    { id: 3, ubicacion: "Bloque B - 03", estado: "Disponible" },
    { id: 4, ubicacion: "Bloque B - 04", estado: "Disponible" },
    { id: 5, ubicacion: "Bloque C - 05", estado: "Ocupado" }
  ],
  
  // Obtener todos los espacios
  obtenerTodos: function() {
    return this.espacios;
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
          <li><a href="index.html">Inicio</a></li>
          <li><a href="ubicacion.html">¿Dónde estamos?</a></li>
          <li><a href="espacios.html">Espacios</a></li>
          <li><a href="retiro.html">Retiro Vehículo</a></li>
          <li><a href="#" onclick="SesionManager.cerrarSesion()">Cerrar sesión</a></li>
        `;
      } else if (sesion.tipo === "admin") {
        menu.innerHTML = `
          <li><a href="index.html">Inicio</a></li>
          <li><a href="ubicacion.html">¿Dónde estamos?</a></li>
          <li><a href="admin.html">Administrador</a></li>
          <li><a href="#" onclick="SesionManager.cerrarSesion()">Cerrar sesión</a></li>
        `;
      }
    }
  },
  
  // Renderizar tabla de espacios
  renderizarTablaEspacios: function(tabla) {
    tabla.innerHTML = "";
    EspaciosManager.obtenerTodos().forEach(espacio => {
      const fila = document.createElement("tr");
      fila.innerHTML = `
        <td>${espacio.id}</td>
        <td>${espacio.ubicacion}</td>
        <td style="color: ${espacio.estado === 'Disponible' ? 'green' : 'red'};">
          ${espacio.estado}
        </td>
      `;
      tabla.appendChild(fila);
    });
  },
  
  // Renderizar tabla de usuarios para admin
  renderizarTablaUsuarios: function(tabla) {
    tabla.innerHTML = "";
    UsuarioManager.obtenerTodosLosUsuarios().forEach(datos => {
      const fila = document.createElement("tr");
      fila.innerHTML = `
        <td>${datos.usuario}</td>
        <td>${datos.nombres} ${datos.apellidos}</td>
        <td>${datos.placa}</td>
        <td>${datos.fechaNacimiento}</td>
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
  
  // Inicializar formulario de registro
  const registroForm = document.getElementById("registroForm");
  const mensajeRegistro = document.getElementById("mensajeRegistro");
  
  if (registroForm) {
    registroForm.addEventListener("submit", function(e) {
      e.preventDefault();
      
      const datosUsuario = {
        nombres: document.getElementById("nombres").value.trim(),
        apellidos: document.getElementById("apellidos").value.trim(),
        placa: document.getElementById("placa").value.trim(),
        fechaNacimiento: document.getElementById("fechaNacimiento").value,
        usuario: document.getElementById("usuario").value.trim(),
        contrasena: document.getElementById("contrasena").value
      };
      
      const resultado = UsuarioManager.registrar(datosUsuario);
      UIManager.mostrarMensaje(mensajeRegistro, resultado.mensaje, resultado.exito ? "exito" : "error");
      
      if (resultado.exito) {
        registroForm.reset();
      }
    });
  }
  
  // Inicializar formulario de login
  const loginFormGlobal = document.getElementById("loginForm");
  const mensajeLoginGlobal = document.getElementById("mensajeLogin");
  
  if (loginFormGlobal) {
    loginFormGlobal.addEventListener("submit", function(e) {
      e.preventDefault();
      
      const usuario = document.getElementById("usuario").value.trim();
      const contrasena = document.getElementById("contrasena").value;
      
      const resultado = UsuarioManager.verificarCredenciales(usuario, contrasena);
      
      if (resultado.exito) {
        SesionManager.guardarSesion(resultado.tipo, usuario);
        window.location.href = resultado.tipo === "admin" ? "admin.html" : "index.html";
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
      retiroForm.addEventListener("submit", function(e) {
        e.preventDefault();
        
        const placaInput = document.getElementById("placaRetiro");
        const placa = placaInput.value.trim().toUpperCase();
        
        if (!placa) {
          UIManager.mostrarMensaje(mensajeRetiro, "Por favor, ingrese un número de placa válido.", "error");
          return;
        }
        
        const usuarioEncontrado = UsuarioManager.buscarPorPlaca(placa);
        
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