// --- Registro ---
const registroForm = document.getElementById("registroForm");
const mensajeRegistro = document.getElementById("mensajeRegistro");

if (registroForm) {
  registroForm.addEventListener("submit", function (e) {
    e.preventDefault();

    const usuario = document.getElementById("usuario").value.trim();

    if (localStorage.getItem(`usuario_${usuario}`)) {
      mensajeRegistro.textContent = "El usuario ya existe, elige otro.";
      mensajeRegistro.style.color = "red";
      return;
    }

    const datosUsuario = {
      nombres: document.getElementById("nombres").value.trim(),
      apellidos: document.getElementById("apellidos").value.trim(),
      placa: document.getElementById("placa").value.trim(),
      fechaNacimiento: document.getElementById("fechaNacimiento").value,
      usuario: usuario,
      contrasena: document.getElementById("contrasena").value
    };

    localStorage.setItem(`usuario_${usuario}`, JSON.stringify(datosUsuario));
    mensajeRegistro.textContent = "¡Registro exitoso!";
    mensajeRegistro.style.color = "green";
    registroForm.reset();
  });
}

// --- Login (Usuarios y Admin) ---
const loginFormGlobal = document.getElementById("loginForm");
const mensajeLoginGlobal = document.getElementById("mensajeLogin");

if (loginFormGlobal) {
  loginFormGlobal.addEventListener("submit", function (e) {
    e.preventDefault();

    const usuario = document.getElementById("usuario").value.trim();
    const contrasena = document.getElementById("contrasena").value;

    if (usuario === "admin" && contrasena === "admin123") {
      localStorage.setItem("sesionActiva", JSON.stringify({ tipo: "admin", usuario: "admin" }));
      window.location.href = "admin.html";
      return;
    }

    const datosGuardados = localStorage.getItem(`usuario_${usuario}`);
    if (!datosGuardados) {
      mensajeLoginGlobal.textContent = "Usuario no encontrado.";
      mensajeLoginGlobal.style.color = "red";
      return;
    }

    const datos = JSON.parse(datosGuardados);
    if (datos.contrasena === contrasena) {
      localStorage.setItem("sesionActiva", JSON.stringify({ tipo: "usuario", usuario }));
      window.location.href = "index.html";
    } else {
      mensajeLoginGlobal.textContent = "Contraseña incorrecta.";
      mensajeLoginGlobal.style.color = "red";
    }
  });
}

// --- Mostrar/Ocultar menú según sesión ---
document.addEventListener("DOMContentLoaded", () => {
  const sesion = JSON.parse(localStorage.getItem("sesionActiva"));
  const menu = document.getElementById("menuLinks");

  if (menu && sesion) {
    if (sesion.tipo === "usuario") {
      menu.innerHTML = `
        <li><a href="index.html">Inicio</a></li>
        <li><a href="ubicacion.html">¿Dónde estamos?</a></li>
        <li><a href="espacios.html">Espacios</a></li>
        <li><a href="retiro.html">Retiro Vehículo</a></li>
        <li><a href="#" onclick="cerrarSesion()">Cerrar sesión</a></li>
      `;
    } else if (sesion.tipo === "admin") {
      menu.innerHTML = `
        <li><a href="index.html">Inicio</a></li>
        <li><a href="ubicacion.html">¿Dónde estamos?</a></li>
        <li><a href="admin.html">Administrador</a></li>
        <li><a href="#" onclick="cerrarSesion()">Cerrar sesión</a></li>
      `;
    }
  }
});

function cerrarSesion() {
  localStorage.removeItem("sesionActiva");
  window.location.href = "index.html";
}

// --- Mostrar espacios ---
const espacios = [
  { id: 1, ubicacion: "Bloque A - 01", estado: "Disponible" },
  { id: 2, ubicacion: "Bloque A - 02", estado: "Ocupado" },
  { id: 3, ubicacion: "Bloque B - 03", estado: "Disponible" },
  { id: 4, ubicacion: "Bloque B - 04", estado: "Disponible" },
  { id: 5, ubicacion: "Bloque C - 05", estado: "Ocupado" }
];

const tablaEspacios = document.getElementById("tablaEspacios");

if (tablaEspacios) {
  // Seguridad: solo usuario puede ver esta página
  const sesion = JSON.parse(localStorage.getItem("sesionActiva"));
  if (!sesion || sesion.tipo !== "usuario") {
    alert("Acceso no autorizado. Inicia sesión primero.");
    window.location.href = "login.html";
  } else {
    espacios.forEach(espacio => {
      const fila = document.createElement("tr");
      fila.innerHTML = `
        <td>${espacio.id}</td>
        <td>${espacio.ubicacion}</td>
        <td style="color: ${espacio.estado === 'Disponible' ? 'green' : 'red'};">
          ${espacio.estado}
        </td>
      `;
      tablaEspacios.appendChild(fila);
    });
  }
}

// --- Retiro de vehículo ---
const retiroForm = document.getElementById("retiroForm");
const mensajeRetiro = document.getElementById("mensajeRetiro");

if (retiroForm) {
  // Seguridad: solo usuario puede ver esta página
  const sesion = JSON.parse(localStorage.getItem("sesionActiva"));
  if (!sesion || sesion.tipo !== "usuario") {
    alert("Acceso no autorizado. Inicia sesión primero.");
    window.location.href = "login.html";
  } else {
    retiroForm.addEventListener("submit", function (e) {
      e.preventDefault();

      const placaInput = document.getElementById("placaRetiro");
      const placa = placaInput.value.trim().toUpperCase();

      if (!placa) {
        mostrarMensaje("Por favor, ingrese un número de placa válido.", "error");
        return;
      }

      let encontrado = false;

      for (let i = 0; i < localStorage.length; i++) {
        const clave = localStorage.key(i);
        if (clave.startsWith("usuario_")) {
          const datos = JSON.parse(localStorage.getItem(clave));
          if (datos.placa.toUpperCase() === placa) {
            encontrado = true;
            break;
          }
        }
      }

      if (encontrado) {
        mostrarMensaje(`Vehículo con placa ${placa} ha sido retirado exitosamente.`, "exito");
      } else {
        mostrarMensaje(`No se encontró ningún vehículo con esa placa.`, "error");
      }

      retiroForm.reset();
    });
  }

  function mostrarMensaje(texto, tipo) {
    mensajeRetiro.textContent = texto;
    mensajeRetiro.className = tipo;
    mensajeRetiro.classList.add("visible");

    setTimeout(() => {
      mensajeRetiro.classList.remove("visible");
    }, 5000);
  }
}

// --- Contacto ---
const contactoForm = document.getElementById("contactoForm");
const mensajeContacto = document.getElementById("mensajeContacto");

if (contactoForm) {
  contactoForm.addEventListener("submit", function (e) {
    e.preventDefault();

    mensajeContacto.textContent = "¡Mensaje enviado correctamente!";
    mensajeContacto.style.color = "green";
    contactoForm.reset();
  });
}

// --- Panel admin ---
const tablaUsuarios = document.getElementById("tablaUsuarios");

if (tablaUsuarios) {
  // Seguridad: solo admin puede ver esta página
  const sesion = JSON.parse(localStorage.getItem("sesionActiva"));
  if (!sesion || sesion.tipo !== "admin") {
    alert("Acceso no autorizado. Inicia sesión primero.");
    window.location.href = "login.html";
  } else {
    tablaUsuarios.innerHTML = "";
    for (let i = 0; i < localStorage.length; i++) {
      const clave = localStorage.key(i);
      if (clave.startsWith("usuario_")) {
        const datos = JSON.parse(localStorage.getItem(clave));
        const fila = document.createElement("tr");
        fila.innerHTML = `
          <td>${datos.usuario}</td>
          <td>${datos.nombres} ${datos.apellidos}</td>
          <td>${datos.placa}</td>
          <td>${datos.fechaNacimiento}</td>
        `;
        tablaUsuarios.appendChild(fila);
      }
    }
  }
}


