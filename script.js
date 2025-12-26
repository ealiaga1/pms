let slideIndex = 0;
showSlides();

function showSlides() {
    let i;
    let slides = document.getElementsByClassName("slide");
    
    // Ocultar todas las imagenes
    for (i = 0; i < slides.length; i++) {
        slides[i].style.display = "none";  
    }
    
    slideIndex++;
    if (slideIndex > slides.length) {slideIndex = 1}    
    
    // Mostrar la imagen actual
    slides[slideIndex-1].style.display = "block";  
    slides[slideIndex-1].classList.add("active");
    
    // Cambiar imagen cada 4 segundos
    setTimeout(showSlides, 4000); 
}
// Convertimos el array PHP a JSON para usarlo en JS
const productosDb = <?php echo json_encode($lista_productos_json); ?>;

function cargarVenta(idHabitacion) {
    // Generamos las opciones del select dinámicamente
    let opciones = '';
    productosDb.forEach(prod => {
        opciones += `<option value="${prod.nombre}" data-precio="${prod.precio}">${prod.nombre} - S/ ${prod.precio}</option>`;
    });

    document.getElementById('areaVenta').innerHTML = `
        <h4>Agregar Consumo</h4>
        <form action="procesar_hotel.php" method="POST">
            <input type="hidden" name="accion" value="venta">
            <input type="hidden" name="id_habitacion" value="${idHabitacion}">
            
            <label>Seleccionar Producto:</label>
            <select id="selectProducto" name="producto" onchange="actualizarPrecio()" style="width:100%; padding:8px; margin-bottom:10px;">
                ${opciones}
            </select>

            <label>Precio (S/):</label>
            <input type="number" id="inputPrecioVenta" name="monto" step="0.50" required style="width:100%; padding:8px; margin-bottom:10px;" readonly>

            <button type="submit" style="width:100%; padding:10px; background:#3498db; color:white; border:none; border-radius:5px;">
                CARGAR
            </button>
        </form>
    `;
    
    // Ejecutar una vez para poner el precio del primer item
    actualizarPrecio();
}

function actualizarPrecio() {
    let select = document.getElementById('selectProducto');
    let precio = select.options[select.selectedIndex].getAttribute('data-precio');
    document.getElementById('inputPrecioVenta').value = precio;
}