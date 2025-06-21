function openTab(tabName, event) {
  document.querySelectorAll('.tab-content').forEach(tab => tab.classList.remove('active'));
  document.querySelectorAll('.tab').forEach(tab => tab.classList.remove('active'));

  document.getElementById(tabName).classList.add('active');
  event.currentTarget.classList.add('active');
}

document.getElementById('registerForm').addEventListener('submit', function (e) {
  e.preventDefault();

  const password = document.getElementById('password').value;
  const confirmPassword = document.getElementById('confirm_password').value;

  if (password !== confirmPassword) {
    alert('Las contraseñas no coinciden');
    return;
  }

  const data = {
    usuario: {
      identificacion: document.getElementById('identificacion').value.trim(),
      nombre_completo: document.getElementById('nombre_completo').value.trim(),
      correo: document.getElementById('correo').value.trim(),
      telefono: document.getElementById('telefono').value.trim(),
      id_tipo_usuario: parseInt(document.getElementById('tipo_usuario').value),
      password: password
    },
    vehiculo: {
      placa: document.getElementById('placa').value.trim().toUpperCase(),
      id_tipo_vehiculo: parseInt(document.getElementById('tipo_vehiculo').value),
      marca: document.getElementById('marca').value.trim(),
      modelo: document.getElementById('modelo').value.trim(),
      color: document.getElementById('color').value.trim(),
      autorizado: true
    }
  };

  fetch('php/auth/register.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify(data)
  })
    .then(response => response.json())
    .then(result => {
      if (result.success) {
        alert('Registro exitoso. ID Usuario: ' + result.user_id);
        document.getElementById('registerForm').reset();
        openTab('login', { currentTarget: document.querySelector('.tab:first-child') });
      } else {
        alert('Error: ' + result.error);
      }
    })
    .catch(error => {
      console.error('Error de red:', error);
      alert('Error de red al enviar los datos.');
    });
});


document.getElementById('loginForm').addEventListener('submit', function (e) {
  e.preventDefault();

  const correo = document.getElementById('login_email').value.trim();
  const password = document.getElementById('login_password').value;

  fetch('php/auth/login.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ correo, password })
  })
    .then(response => response.json())
    .then(result => {
  if (result.success) {
    // Guardamos los datos en localStorage
    localStorage.setItem('id_usuario', result.id_usuario);
    localStorage.setItem('nombre_usuario', result.nombre);

    window.location.href = 'panel.html';
  }
 else {
        alert('Error: ' + result.error);
      }
    })
    .catch(error => {
      console.error('Error de red:', error);
      alert('Error de red al iniciar sesión.');
    });
});




// Tab functionality
function openTab(tabName, event) {
    const tabContents = document.querySelectorAll('.tab-content');
    const tabs = document.querySelectorAll('.tab');

    tabContents.forEach(content => content.classList.remove('active'));
    tabs.forEach(tab => tab.classList.remove('active'));

    document.getElementById(tabName).classList.add('active');
    event.currentTarget.classList.add('active');
}


function cargarDatosUsuario(idUsuario) {
  fetch('php/auth/datos_usuario.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ id_usuario: idUsuario })
  })
    .then(response => response.json())
    .then(result => {
      if (result.success) {
        const usuario = result.usuario;
        document.getElementById('userName').textContent = usuario.nombre_completo;
        document.getElementById('welcomeUserName').textContent = usuario.nombre_completo;

        // Si quieres mostrar más info, como tipo de usuario, puedes crear más elementos
        console.log("Usuario cargado:", usuario);
      } else {
        alert('Error al cargar usuario: ' + result.error);
      }
    })
    .catch(error => {
      console.error('Error de red:', error);
      alert('No se pudo cargar la información del usuario.');
    });
}
function cargarUsuarios() {
  fetch('php/usuarios/admin_usuarios.php')
    .then(res => res.json())
    .then(usuarios => {
      const tbody = document.getElementById('tablaUsuarios');
      tbody.innerHTML = '';
      usuarios.forEach(user => {
        const estado = user.activo == 1 ? 'Activo' : 'Inactivo';
        const badge = user.activo == 1 ? 'status-active' : 'status-inactive';
        tbody.innerHTML += `
          <tr>
            <td>${user.id_usuario}</td>
            <td>${user.identificacion}</td>
            <td>${user.nombre_completo}</td>
            <td>${user.correo}</td>
            <td>${user.telefono}</td>
            <td>${user.tipo}</td>
            <td><span class="status-badge ${badge}">${estado}</span></td>
            <td><button class="btn btn-danger" onclick="eliminarUsuario(${user.id_usuario})">Eliminar</button></td>
          </tr>`;
      });
    });
}

