// notificaciones.js
function mostrarMensaje(mensaje, tipo = 'success') {
    const alertContainer = document.getElementById('alert-container');

    if (!alertContainer) {
        const nuevoContainer = document.createElement('div');
        nuevoContainer.id = 'alert-container';
        nuevoContainer.style.position = 'fixed';
        nuevoContainer.style.top = '20px';
        nuevoContainer.style.right = '20px';
        nuevoContainer.style.zIndex = '1000';
        document.body.appendChild(nuevoContainer);
    }

    const alertType = tipo === 'success' ? 'alert-success' : 'alert-danger';
    const alerta = document.createElement('div');
    alerta.className = `alert ${alertType} alert-dismissible fade show`;
    alerta.role = 'alert';
    alerta.innerHTML = `
        ${mensaje}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    `;

    document.getElementById('alert-container').appendChild(alerta);

    setTimeout(() => {
        alerta.classList.remove('show');
        alerta.classList.add('fade');
        setTimeout(() => alerta.remove(), 150);
    }, 5000);
}