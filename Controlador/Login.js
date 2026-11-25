const formulario = document.getElementById('FormuLogin');

formulario.addEventListener('submit', async function (e) {
    e.preventDefault();

    const botton = formulario.querySelector('button[type="submit"]');
    const mensaje = document.getElementById("mensajelogin");

    mensaje.textContent = "";
    botton.disabled = true;

    try {
        const datosFormulario = Object.fromEntries(new FormData(formulario).entries());

        const respuesta = await fetch(formulario.action, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(datosFormulario),
        });

        const resultado = await respuesta.json();

        if (!respuesta.ok || !resultado.success) {
            mensaje.textContent = resultado.mensaje || "No fue posible iniciar sesión. Por favor, verifica tus credenciales";
            return;
        }

        localStorage.setItem("Logueado", "true");
        localStorage.setItem("Rol", resultado.rol);
        localStorage.setItem("Nombre", resultado.nombre);
        window.location.href = "PaginaPrincipal.html";

    } catch (error) {
        console.error("Error al iniciar sesión:", error);
        mensaje.textContent = "Ocurrió un error al intentar iniciar sesión. Por favor, inténtalo de nuevo más tarde.";
    } finally {
        botton.disabled = false;
    }
});