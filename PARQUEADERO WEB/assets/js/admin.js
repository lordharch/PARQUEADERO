       // Variables globales
        let currentTab = 'usuarios';
        let currentData = {};

        // Función para cambiar de tab
        function openTab(tabName) {
            // Ocultar todos los contenidos
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.remove('active');
                });

            // Ocultar todos los tabs
            document.querySelectorAll('.tab').forEach(tab => {
                tab.classList.remove('active');
            });

            // Mostrar el contenido seleccionado
            document.getElementById(tabName).classList.add('active');
            
            // Activar el tab seleccionado
            event.target.classList.add('active');
            
            currentTab = tabName;
            cargarDatosTab(tabName);
        }
        // Función para cargar contenido según la pestaña activa
        function cargarDatosTab(tab) {
            if (tab === 'usuarios') cargarUsuarios();
            else if (tab === 'vehiculos') cargarVehiculos();
            else if (tab === 'zonas') cargarZonas();
            else if (tab === 'espacios') cargarEspacios();
            else if (tab === 'camaras') cargarCamaras();
            else if (tab === 'accesos') cargarAccesos();
            else if (tab === 'transacciones') cargarTransacciones();
            else if (tab === 'eventos') cargarEventos();
        }
        function cargarUsuarios() {
    const tabla = document.getElementById('tablaUsuarios');
    tabla.innerHTML = `<tr><td colspan="8" style="text-align:center;">Cargando usuarios...</td></tr>`;

    fetch('php/usuarios/cargar_usuarios.php')
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                tabla.innerHTML = `<tr><td colspan="8" style="text-align:center;color:red;">Error al cargar usuarios</td></tr>`;
                return;
            }

            const usuarios = data.usuarios;
            if (usuarios.length === 0) {
                tabla.innerHTML = `<tr><td colspan="8" style="text-align:center;">No hay usuarios registrados</td></tr>`;
                return;
            }

            tabla.innerHTML = ''; // limpiar

            usuarios.forEach(u => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${u.id_usuario}</td>
                    <td>${u.identificacion}</td>
                    <td>${u.nombre_completo}</td>
                    <td>${u.correo}</td>
                    <td>${u.telefono}</td>
                    <td>${u.tipo_usuario}</td>
                    <td>
                        <span class="status-badge ${u.activo ? 'status-active' : 'status-inactive'}">
                            ${u.activo ? 'Activo' : 'Inactivo'}
                        </span>
                    </td>
                    <td>
                        <button class="btn btn-warning" onclick="editarUsuario(${u.id_usuario})">Editar</button>
                        <button class="btn btn-danger" onclick="eliminarUsuario(${u.id_usuario})">Eliminar</button>
                    </td>
                `;
                tabla.appendChild(tr);
            });
        })
        .catch(error => {
            console.error('Error al cargar usuarios:', error);
            tabla.innerHTML = `<tr><td colspan="8" style="text-align:center;color:red;">Error de red</td></tr>`;
        });
}
function cargarVehiculos() {
    const tabla = document.getElementById('tablaVehiculos');
    tabla.innerHTML = `<tr><td colspan="8" style="text-align:center;">Cargando vehículos...</td></tr>`;

    fetch('php/vehiculos/cargar_vehiculos.php')
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                tabla.innerHTML = `<tr><td colspan="8" style="text-align:center;color:red;">Error al cargar vehículos</td></tr>`;
                return;
            }

            const vehiculos = data.vehiculos;
            if (vehiculos.length === 0) {
                tabla.innerHTML = `<tr><td colspan="8" style="text-align:center;">No hay vehículos registrados</td></tr>`;
                return;
            }

            tabla.innerHTML = ''; // limpiar tabla

            vehiculos.forEach(v => {
                const estado = v.autorizado ? 'Autorizado' : 'Pendiente';
                const badge = v.autorizado ? 'status-active' : 'status-reserved';

                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${v.placa}</td>
                    <td>${v.propietario}</td>
                    <td>${v.tipo_vehiculo}</td>
                    <td>${v.marca}</td>
                    <td>${v.modelo}</td>
                    <td>${v.color}</td>
                    <td><span class="status-badge ${badge}">${estado}</span></td>
                    <td>
                        <button class="btn btn-warning" onclick="editarVehiculo('${v.placa}')">Editar</button>
                        <button class="btn btn-danger" onclick="eliminarVehiculo('${v.placa}')">Eliminar</button>
                    </td>
                `;
                tabla.appendChild(tr);
            });
        })
        .catch(error => {
            console.error('Error al cargar vehículos:', error);
            tabla.innerHTML = `<tr><td colspan="8" style="text-align:center;color:red;">Error de red</td></tr>`;
        });
}
function cargarZonas() {
    const tabla = document.getElementById('tablaZonas');
    tabla.innerHTML = `<tr><td colspan="6" style="text-align:center;">Cargando zonas...</td></tr>`;

    fetch('php/zonas/cargar_zonas.php')
        .then(res => res.json())
        .then(data => {
            if (!data.success) {
                tabla.innerHTML = `<tr><td colspan="6" style="color:red;text-align:center;">Error al cargar zonas</td></tr>`;
                return;
            }

            if (data.zonas.length === 0) {
                tabla.innerHTML = `<tr><td colspan="6" style="text-align:center;">No hay zonas registradas</td></tr>`;
                return;
            }

            tabla.innerHTML = ''; // limpiar tabla

            data.zonas.forEach(z => {
                const tr = document.createElement('tr');
                const disponibles = z.disponibles || 0;
                const ocupados = z.ocupados || 0;

                tr.innerHTML = `
                    <td>${z.id_zona}</td>
                    <td>${z.nombre}</td>
                    <td>${z.capacidad_maxima}</td>
                    <td>${ocupados}</td>
                    <td>${disponibles}</td>
                    <td>
                        <button class="btn btn-warning" onclick="editarZona(${z.id_zona})">Editar</button>
                        <button class="btn btn-danger" onclick="eliminarZona(${z.id_zona})">Eliminar</button>
                    </td>
                `;
                tabla.appendChild(tr);
            });
        })
        .catch(err => {
            console.error('Error al cargar zonas:', err);
            tabla.innerHTML = `<tr><td colspan="6" style="color:red;text-align:center;">Error de red</td></tr>`;
        });
}
function cargarEspacios() {
    const tabla = document.getElementById('tablaEspacios');
    tabla.innerHTML = `<tr><td colspan="6" style="text-align:center;">Cargando espacios...</td></tr>`;

    fetch('php/espacios/cargar_espacios.php')
        .then(res => res.json())
        .then(data => {
            if (!data.success) {
                tabla.innerHTML = `<tr><td colspan="6" style="text-align:center;color:red;">Error al cargar espacios</td></tr>`;
                return;
            }

            const espacios = data.espacios;
            if (espacios.length === 0) {
                tabla.innerHTML = `<tr><td colspan="6" style="text-align:center;">No hay espacios registrados</td></tr>`;
                return;
            }

            tabla.innerHTML = ''; // limpiar

            espacios.forEach(e => {
                let badgeClass = '';
                switch (e.estado) {
                    case 'disponible': badgeClass = 'status-available'; break;
                    case 'ocupado': badgeClass = 'status-occupied'; break;
                    case 'reservado': badgeClass = 'status-reserved'; break;
                    case 'mantenimiento': badgeClass = 'status-maintenance'; break;
                }

                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${e.id_espacio}</td>
                    <td>${e.codigo_espacio}</td>
                    <td>${e.zona}</td>
                    <td>${e.tipo_vehiculo}</td>
                    <td><span class="status-badge ${badgeClass}">${e.estado}</span></td>
                    <td>
                        <button class="btn btn-warning" onclick="editarEspacio(${e.id_espacio})">Editar</button>
                        <button class="btn btn-danger" onclick="eliminarEspacio(${e.id_espacio})">Eliminar</button>
                    </td>
                `;
                tabla.appendChild(tr);
            });
        })
        .catch(err => {
            console.error('Error al cargar espacios:', err);
            tabla.innerHTML = `<tr><td colspan="6" style="text-align:center;color:red;">Error de red</td></tr>`;
        });
}
function cargarCamaras() {
    const tabla = document.getElementById('tablaCamaras');
    tabla.innerHTML = `<tr><td colspan="7" style="text-align:center;">Cargando cámaras...</td></tr>`;

    fetch('php/camaras/cargar_camaras.php')
        .then(res => res.json())
        .then(data => {
            if (!data.success) {
                tabla.innerHTML = `<tr><td colspan="7" style="text-align:center;color:red;">Error al cargar cámaras</td></tr>`;
                return;
            }

            const camaras = data.camaras;
            if (camaras.length === 0) {
                tabla.innerHTML = `<tr><td colspan="7" style="text-align:center;">No hay cámaras registradas</td></tr>`;
                return;
            }

            tabla.innerHTML = '';
            camaras.forEach(c => {
                const badge = {
                    activa: 'status-available',
                    inactiva: 'status-inactive',
                    mantenimiento: 'status-maintenance'
                }[c.estado] || 'status-inactive';

                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${c.id_camara}</td>
                    <td>${c.nombre}</td>
                    <td>${c.zona}</td>
                    <td>${c.direccion_ip}</td>
                    <td>${c.ultima_verificacion || '-'}</td>
                    <td><span class="status-badge ${badge}">${c.estado}</span></td>
                    <td>
                        <button class="btn btn-warning" onclick="editarCamara(${c.id_camara})">Editar</button>
                        <button class="btn btn-danger" onclick="eliminarCamara(${c.id_camara})">Eliminar</button>
                    </td>
                `;
                tabla.appendChild(tr);
            });
        })
        .catch(err => {
            console.error('Error al cargar cámaras:', err);
            tabla.innerHTML = `<tr><td colspan="7" style="text-align:center;color:red;">Error de red</td></tr>`;
        });
}
function cargarAccesos() {
    const tabla = document.getElementById('tablaAccesos');
    tabla.innerHTML = `<tr><td colspan="7" style="text-align:center;">Cargando accesos...</td></tr>`;

    fetch('php/accesos/cargar_accesos.php')
        .then(res => res.json())
        .then(data => {
            if (!data.success) {
                tabla.innerHTML = `<tr><td colspan="7" style="text-align:center;color:red;">Error al cargar accesos</td></tr>`;
                return;
            }

            const accesos = data.accesos;
            if (accesos.length === 0) {
                tabla.innerHTML = `<tr><td colspan="7" style="text-align:center;">No hay accesos registrados</td></tr>`;
                return;
            }

            tabla.innerHTML = ''; // limpiar tabla

            accesos.forEach(a => {
                let badge = '';
                switch (a.estado) {
                    case 'activo': badge = 'status-active'; break;
                    case 'finalizado': badge = 'status-inactive'; break;
                    case 'anomalia': badge = 'status-reserved'; break;
                }

                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${a.id_acceso}</td>
                    <td>${a.placa_vehiculo}</td>
                    <td>${a.codigo_espacio}</td>
                    <td>${a.fecha_entrada || '-'}</td>
                    <td>${a.fecha_salida || '-'}</td>
                    <td><span class="status-badge ${badge}">${a.estado}</span></td>
                    <td>
                        <button class="btn btn-warning" onclick="verAcceso(${a.id_acceso})">Ver</button>
                        <button class="btn btn-danger" onclick="eliminarAcceso(${a.id_acceso})">Eliminar</button>
                    </td>
                `;
                tabla.appendChild(tr);
            });
        })
        .catch(err => {
            console.error('Error al cargar accesos:', err);
            tabla.innerHTML = `<tr><td colspan="7" style="text-align:center;color:red;">Error de red</td></tr>`;
        });
}
function cargarTransacciones() {
    const tabla = document.getElementById('tablaTransacciones');
    tabla.innerHTML = `<tr><td colspan="8" style="text-align:center;">Cargando transacciones...</td></tr>`;

    fetch('php/transacciones/cargar_transacciones.php')
        .then(res => res.json())
        .then(data => {
            if (!data.success) {
                tabla.innerHTML = `<tr><td colspan="8" style="text-align:center;color:red;">Error al cargar transacciones</td></tr>`;
                return;
            }

            const transacciones = data.transacciones;
            if (transacciones.length === 0) {
                tabla.innerHTML = `<tr><td colspan="8" style="text-align:center;">No hay transacciones registradas</td></tr>`;
                return;
            }

            tabla.innerHTML = '';

            transacciones.forEach(t => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${t.id_transaccion}</td>
                    <td>${t.id_acceso}</td>
                    <td>${t.fecha_transaccion}</td>
                    <td>${t.tiempo_estadia} min</td>
                    <td>$${parseFloat(t.subtotal).toFixed(2)}</td>
                    <td>$${parseFloat(t.descuentos).toFixed(2)}</td>
                    <td>$${parseFloat(t.total_pagado).toFixed(2)}</td>
                    <td>${t.numero_comprobante}</td>
                `;
                tabla.appendChild(tr);
            });
        })
        .catch(err => {
            console.error('Error al cargar transacciones:', err);
            tabla.innerHTML = `<tr><td colspan="8" style="text-align:center;color:red;">Error de red</td></tr>`;
        });
}
function cargarEventos() {
    const tabla = document.getElementById('tablaEventos');
    tabla.innerHTML = `<tr><td colspan="7" style="text-align:center;">Cargando eventos...</td></tr>`;

    fetch('php/eventos/cargar_eventos.php')
        .then(res => res.json())
        .then(data => {
            if (!data.success) {
                tabla.innerHTML = `<tr><td colspan="7" style="text-align:center;color:red;">Error al cargar eventos</td></tr>`;
                return;
            }

            const eventos = data.eventos;
            if (eventos.length === 0) {
                tabla.innerHTML = `<tr><td colspan="7" style="text-align:center;">No hay eventos registrados</td></tr>`;
                return;
            }

            tabla.innerHTML = '';

            eventos.forEach(e => {
                let estadoColor = '';
                switch (e.estado_resolucion) {
                    case 'reportado': estadoColor = 'status-warning'; break;
                    case 'investigacion': estadoColor = 'status-reserved'; break;
                    case 'resuelto': estadoColor = 'status-active'; break;
                    case 'escalado': estadoColor = 'status-danger'; break;
                }

                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${e.id_evento}</td>
                    <td>${e.tipo}</td>
                    <td>${e.fecha_evento}</td>
                    <td>${e.descripcion}</td>
                    <td><span class="status-badge ${estadoColor}">${e.estado_resolucion}</span></td>
                    <td>${e.nivel_prioridad}</td>
                    <td>
                        <button class="btn btn-warning" onclick="verEvento(${e.id_evento})">Ver</button>
                        <button class="btn btn-danger" onclick="eliminarEvento(${e.id_evento})">Eliminar</button>
                    </td>
                `;
                tabla.appendChild(tr);
            });
        })
        .catch(err => {
            console.error('Error al cargar eventos:', err);
            tabla.innerHTML = `<tr><td colspan="7" style="text-align:center;color:red;">Error de red</td></tr>`;
        });
}
function openModal(tipo) {
    const modal = document.getElementById("modal");
    const modalTitle = document.getElementById("modalTitle");
    const modalBody = document.getElementById("modalBody");

    if (tipo === "nuevoUsuario") {
        modalTitle.textContent = "Registrar Nuevo Usuario";
        modalBody.innerHTML = `
            <div class="form-group">
                <label class="form-label">Identificación</label>
                <input class="form-input" type="text" id="identificacionUsuario">
            </div>
            <div class="form-group">
                <label class="form-label">Nombre Completo</label>
                <input class="form-input" type="text" id="nombreUsuario">
            </div>
            <div class="form-group">
                <label class="form-label">Correo</label>
                <input class="form-input" type="email" id="correoUsuario">
            </div>
            <div class="form-group">
                <label class="form-label">Teléfono</label>
                <input class="form-input" type="text" id="telefonoUsuario">
            </div>
            <div class="form-group">
                <label class="form-label">Tipo de Usuario</label>
                <select class="form-select" id="tipoUsuario">
                    <option value="1">Administrador</option>
                    <option value="2">Operador</option>
                    <option value="3">Cliente</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Estado</label>
                <select class="form-select" id="estadoUsuario">
                    <option value="1">Activo</option>
                    <option value="0">Inactivo</option>
                </select>
            </div>
            <div class="form-group">
                <button class="btn btn-success" onclick="guardarUsuario()">Guardar</button>
                <button class="btn btn-danger" onclick="closeModal()">Cancelar</button>
            </div>
        `;
    }
    else if (tipo === "nuevoVehiculo") {
    modalTitle.textContent = "Registrar Nuevo Vehículo";
    modalBody.innerHTML = `
        <div class="form-group">
            <label class="form-label">Placa</label>
            <input class="form-input" type="text" id="placaVehiculo">
        </div>
        <div class="form-group">
            <label class="form-label">Propietario</label>
            <select class="form-select" id="idUsuarioVehiculo"></select>
        </div>
        <div class="form-group">
            <label class="form-label">Tipo de Vehículo</label>
            <select class="form-select" id="tipoVehiculo"></select>
        </div>
        <div class="form-group">
            <label class="form-label">Marca</label>
            <input class="form-input" type="text" id="marcaVehiculo">
        </div>
        <div class="form-group">
            <label class="form-label">Modelo</label>
            <input class="form-input" type="text" id="modeloVehiculo">
        </div>
        <div class="form-group">
            <label class="form-label">Color</label>
            <input class="form-input" type="text" id="colorVehiculo">
        </div>
        <div class="form-group">
            <button class="btn btn-success" onclick="guardarVehiculo()">Guardar</button>
            <button class="btn btn-danger" onclick="closeModal()">Cancelar</button>
        </div>
    `;
    modal.style.display = "block";

    cargarUsuariosParaVehiculo();
    cargarTiposVehiculo();
}


    modal.style.display = "block";
}

function closeModal() {
    document.getElementById("modal").style.display = "none";
}
function guardarUsuario() {
    const identificacion = document.getElementById("identificacionUsuario").value;
    const nombre = document.getElementById("nombreUsuario").value;
    const correo = document.getElementById("correoUsuario").value;
    const telefono = document.getElementById("telefonoUsuario").value;
    const tipo = document.getElementById("tipoUsuario").value;
    const activo = document.getElementById("estadoUsuario").value;

    const formData = new FormData();
    formData.append('identificacion', identificacion);
    formData.append('nombre', nombre);
    formData.append('correo', correo);
    formData.append('telefono', telefono);
    formData.append('tipo', tipo);
    formData.append('activo', activo);

    fetch('php/usuarios/registrar_usuario.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert("✅ Usuario registrado correctamente.");
            closeModal();
            cargarUsuarios();
        } else {
            alert("❌ Error al registrar usuario: " + data.error);
        }
    })
    .catch(err => {
        console.error("Error al guardar usuario:", err);
        alert("❌ Error de red al guardar usuario.");
    });
}
function editarUsuario(id_usuario) {
    fetch(`php/usuarios/obtener_usuario.php?id=${id_usuario}`)
        .then(res => res.json())
        .then(data => {
            if (!data.success) {
                alert("Error al obtener usuario: " + data.error);
                return;
            }

            const u = data.usuario;

            document.getElementById("modalTitle").textContent = "Editar Usuario";
            document.getElementById("modalBody").innerHTML = `
                <div class="form-group">
                    <label class="form-label">Identificación</label>
                    <input class="form-input" type="text" id="identificacionUsuario" value="${u.identificacion}">
                </div>
                <div class="form-group">
                    <label class="form-label">Nombre Completo</label>
                    <input class="form-input" type="text" id="nombreUsuario" value="${u.nombre_completo}">
                </div>
                <div class="form-group">
                    <label class="form-label">Correo</label>
                    <input class="form-input" type="email" id="correoUsuario" value="${u.correo}">
                </div>
                <div class="form-group">
                    <label class="form-label">Teléfono</label>
                    <input class="form-input" type="text" id="telefonoUsuario" value="${u.telefono}">
                </div>
                <div class="form-group">
                    <label class="form-label">Tipo de Usuario</label>
                    <select class="form-select" id="tipoUsuario">
                        <option value="1" ${u.tipo_usuario == 1 ? "selected" : ""}>Administrador</option>
                        <option value="2" ${u.tipo_usuario == 2 ? "selected" : ""}>Operador</option>
                        <option value="3" ${u.tipo_usuario == 3 ? "selected" : ""}>Cliente</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Estado</label>
                    <select class="form-select" id="estadoUsuario">
                        <option value="1" ${u.activo == 1 ? "selected" : ""}>Activo</option>
                        <option value="0" ${u.activo == 0 ? "selected" : ""}>Inactivo</option>
                    </select>
                </div>
                <div class="form-group">
                    <button class="btn btn-success" onclick="actualizarUsuario(${id_usuario})">Actualizar</button>
                    <button class="btn btn-danger" onclick="closeModal()">Cancelar</button>
                </div>
            `;

            document.getElementById("modal").style.display = "block";
        })
        .catch(err => {
            alert("Error al cargar datos del usuario");
            console.error(err);
        });
}
function actualizarUsuario(id_usuario) {
    const formData = new FormData();
    formData.append("id_usuario", id_usuario);
    formData.append("identificacion", document.getElementById("identificacionUsuario").value);
    formData.append("nombre", document.getElementById("nombreUsuario").value);
    formData.append("correo", document.getElementById("correoUsuario").value);
    formData.append("telefono", document.getElementById("telefonoUsuario").value);
    formData.append("tipo", document.getElementById("tipoUsuario").value);
    formData.append("activo", document.getElementById("estadoUsuario").value);

    fetch("php/usuarios/actualizar_usuario.php", {
        method: "POST",
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert("✅ Usuario actualizado");
            closeModal();
            cargarUsuarios();
        } else {
            alert("❌ Error al actualizar: " + data.error);
        }
    })
    .catch(err => {
        console.error("Error:", err);
        alert("Error de red");
    });
}
function eliminarUsuario(id_usuario) {
    if (!confirm("¿Estás seguro de que deseas eliminar este usuario?")) return;

    fetch(`php/usuarios/eliminar_usuario.php?id=${id_usuario}`, {
        method: "DELETE"
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert("✅ Usuario eliminado");
            cargarUsuarios();
        } else {
            alert("❌ Error al eliminar: " + data.error);
        }
    })
    .catch(err => {
        console.error("Error al eliminar:", err);
        alert("Error de red");
    });
}
function cargarUsuariosParaVehiculo() {
    fetch('php/cargar_usuarios.php')
        .then(res => res.json())
        .then(data => {
            const select = document.getElementById('idUsuarioVehiculo');
            if (data.success) {
                data.usuarios.forEach(u => {
                    const opt = document.createElement('option');
                    opt.value = u.id_usuario;
                    opt.textContent = `${u.nombre_completo} (${u.identificacion})`;
                    select.appendChild(opt);
                });
            }
        });
}

function cargarTiposVehiculo() {
    fetch('php/vehiculos/cargar_tipos_vehiculo.php') // debes crear este archivo
        .then(res => res.json())
        .then(data => {
            const select = document.getElementById('tipoVehiculo');
            if (data.success) {
                data.tipos.forEach(t => {
                    const opt = document.createElement('option');
                    opt.value = t.id_tipo_vehiculo;
                    opt.textContent = t.nombre;
                    select.appendChild(opt);
                });
            }
        });
}

function guardarVehiculo() {
    const formData = new FormData();
    formData.append("placa", document.getElementById("placaVehiculo").value);
    formData.append("id_usuario", document.getElementById("idUsuarioVehiculo").value);
    formData.append("id_tipo_vehiculo", document.getElementById("tipoVehiculo").value);
    formData.append("marca", document.getElementById("marcaVehiculo").value);
    formData.append("modelo", document.getElementById("modeloVehiculo").value);
    formData.append("color", document.getElementById("colorVehiculo").value);

    fetch('php/vehiculos/registrar_vehiculo.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert("✅ Vehículo registrado correctamente.");
            closeModal();
            cargarVehiculos();
        } else {
            alert("❌ Error al registrar vehículo: " + data.error);
        }
    })
    .catch(err => {
        console.error("Error al registrar vehículo:", err);
        alert("❌ Error de red.");
    });
}
function eliminarVehiculo(placa) {
    if (!confirm("¿Estás seguro de eliminar este vehículo?")) return;

    fetch('php/vehiculos/eliminar_vehiculo.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'placa=' + encodeURIComponent(placa)
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert("Vehículo eliminado correctamente.");
            cargarVehiculos();
        } else {
            alert("Error al eliminar vehículo: " + data.error);
        }
    })
    .catch(err => {
        console.error("Error de red:", err);
        alert("Error de red al intentar eliminar.");
    });
}
function editarVehiculo(placa) {
    // Abre el modal, carga datos actuales del vehículo, y permite editar
    fetch('php/vehiculos/obtener_vehiculo.php?placa=' + encodeURIComponent(placa))
        .then(res => res.json())
        .then(data => {
            if (!data.success) {
                alert("Error al cargar datos del vehículo");
                return;
            }

            const v = data.vehiculo;
            openModal('editarVehiculo');
            document.getElementById("modalTitle").textContent = "Editar Vehículo";
            document.getElementById("modalBody").innerHTML = `
                <div class="form-group">
                    <label class="form-label">Marca</label>
                    <input class="form-input" type="text" id="marcaVehiculo" value="${v.marca}">
                </div>
                <!-- agrega los demás campos -->
                <div class="form-group">
                    <button class="btn btn-success" onclick="guardarCambiosVehiculo('${v.placa}')">Guardar</button>
                </div>
            `;
        });
}

function autorizarPendientes() {
    if (!confirm("¿Autorizar todos los vehículos pendientes?")) return;

    fetch('php/autorizar_vehiculos.php', {
        method: 'POST'
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert("Vehículos autorizados correctamente.");
            cargarVehiculos();
        } else {
            alert("Error al autorizar: " + data.error);
        }
    })
    .catch(err => {
        console.error("Error de red:", err);
        alert("Error de red al autorizar.");
    });
}
function openModalEditarVehiculo(placa) {
    fetch('php/obtener_vehiculo.php?placa=' + encodeURIComponent(placa))
        .then(res => res.json())
        .then(data => {
            if (!data.success) {
                alert('❌ No se pudo cargar la información del vehículo');
                return;
            }

            const vehiculo = data.vehiculo;
            const modal = document.getElementById('modal');
            const modalTitle = document.getElementById('modalTitle');
            const modalBody = document.getElementById('modalBody');

            modalTitle.textContent = 'Editar Vehículo';

            modalBody.innerHTML = `
                <div class="form-group">
                    <label class="form-label">Placa (no editable)</label>
                    <input class="form-input" type="text" id="editarPlaca" value="${vehiculo.placa}" readonly>
                </div>
                <div class="form-group">
                    <label class="form-label">Marca</label>
                    <input class="form-input" type="text" id="editarMarca" value="${vehiculo.marca || ''}">
                </div>
                <div class="form-group">
                    <label class="form-label">Modelo</label>
                    <input class="form-input" type="text" id="editarModelo" value="${vehiculo.modelo || ''}">
                </div>
                <div class="form-group">
                    <label class="form-label">Color</label>
                    <input class="form-input" type="text" id="editarColor" value="${vehiculo.color || ''}">
                </div>
                <div class="form-group">
                    <label class="form-label">Tipo de Vehículo</label>
                    <select class="form-select" id="editarTipo">
                        <option value="1" ${vehiculo.id_tipo_vehiculo == 1 ? 'selected' : ''}>Carro</option>
                        <option value="2" ${vehiculo.id_tipo_vehiculo == 2 ? 'selected' : ''}>Moto</option>
                        <option value="3" ${vehiculo.id_tipo_vehiculo == 3 ? 'selected' : ''}>Bicicleta</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Estado</label>
                    <select class="form-select" id="editarAutorizado">
                        <option value="1" ${vehiculo.autorizado == 1 ? 'selected' : ''}>Autorizado</option>
                        <option value="0" ${vehiculo.autorizado == 0 ? 'selected' : ''}>Pendiente</option>
                    </select>
                </div>
                <div class="form-group">
                    <button class="btn btn-success" onclick="guardarEdicionVehiculo()">Guardar Cambios</button>
                    <button class="btn btn-danger" onclick="closeModal()">Cancelar</button>
                </div>
            `;

            modal.style.display = 'block';
        })
        .catch(err => {
            console.error('Error al obtener vehículo:', err);
            alert('❌ Error al obtener datos del vehículo.');
        });
}

function guardarEdicionVehiculo() {
    const placa = document.getElementById("editarPlaca").value;
    const marca = document.getElementById("editarMarca").value;
    const modelo = document.getElementById("editarModelo").value;
    const color = document.getElementById("editarColor").value;
    const tipo = document.getElementById("editarTipo").value;
    const autorizado = document.getElementById("editarAutorizado").value;

    const formData = new FormData();
    formData.append('placa', placa);
    formData.append('marca', marca);
    formData.append('modelo', modelo);
    formData.append('color', color);
    formData.append('id_tipo_vehiculo', tipo);
    formData.append('autorizado', autorizado);

    fetch('php/vehiculos/editar_vehiculo.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert('✅ Vehículo actualizado correctamente.');
            closeModal();
            cargarVehiculos(); // Recarga tabla
        } else {
            alert('❌ Error al actualizar: ' + data.error);
        }
    })
    .catch(err => {
        console.error('Error al actualizar vehículo:', err);
        alert('❌ Error de red al actualizar vehículo.');
    });
}
function autorizarPendientes() {
    if (!confirm("¿Deseas autorizar todos los vehículos pendientes?")) return;

    fetch("php/vehiculos/autorizar_vehiculos.php")
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert(`✅ Vehículos autorizados: ${data.actualizados}`);
                cargarVehiculos();
            } else {
                alert("❌ Error: " + data.error);
            }
        })
        .catch(err => {
            console.error("Error al autorizar vehículos:", err);
            alert("❌ Error de red.");
        });
}

function cerrarSesion() {
  // Eliminar datos guardados en localStorage
  localStorage.removeItem('id_usuario');
  localStorage.removeItem('nombre_usuario');

  // Redirigir al login
  window.location.href = 'registro.html'; // o el nombre de tu login
}
