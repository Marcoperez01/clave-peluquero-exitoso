/* ==========================================================================
   SEGUIMIENTO-MAP.JS - Controlador del Mapa Leaflet para Geocercas
   Clave del Peluquero Exitoso
   Fecha: 2026-04-06
   Descripción: Inicialización del mapa Leaflet con tema oscuro (CartoDB),
                dibujo de geocercas como círculos, marcadores de paquetes,
                y actualización dinámica de la visualización.
   ========================================================================== */

// ============================================================
// 1. VARIABLES GLOBALES DEL MAPA
// ============================================================

/** Instancia del mapa Leaflet */
let mapa = null;

/** Layer group para los marcadores de pedidos */
let layerPedidos = null;

/** Layer group para los círculos de geocercas */
let layerGeocercas = null;

/** Layer group para las rutas de entrega */
let layerRutas = null;

/** Marcador de la sede principal */
let marcadorSede = null;

// ============================================================
// 2. INICIALIZACIÓN DEL MAPA
// ============================================================

/**
 * Inicializa el mapa Leaflet centrado en Caldas, Antioquia.
 * Usa tiles de CartoDB Dark Matter para el tema oscuro.
 * Se llama una vez al cargar la página.
 */
function inicializarMapa() {
    // -- Crear instancia del mapa centrada en Caldas --
    mapa = L.map('mapSeguimiento', {
        center: [CENTRO_CALDAS.lat, CENTRO_CALDAS.lng],
        zoom: 14,
        zoomControl: true,
        attributionControl: true
    });

    // -- Capa de tiles: CartoDB Dark Matter (gratis, sin API key) --
    L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OSM</a> &copy; <a href="https://carto.com/">CARTO</a>',
        subdomains: 'abcd',
        maxZoom: 19
    }).addTo(mapa);

    // -- Inicializar layer groups vacíos --
    layerPedidos = L.layerGroup().addTo(mapa);
    layerGeocercas = L.layerGroup().addTo(mapa);
    layerRutas = L.layerGroup().addTo(mapa);

    // -- Marcador especial para la sede principal --
    var iconoSede = L.divIcon({
        html: '<div style="background: linear-gradient(135deg, #d4a853, #b08930); width: 28px; height: 28px; border-radius: 50%; border: 3px solid #0a0a0f; display: flex; align-items: center; justify-content: center; font-size: 14px; box-shadow: 0 0 20px rgba(212,168,83,0.5);">🏠</div>',
        className: 'sede-marker',
        iconSize: [28, 28],
        iconAnchor: [14, 14]
    });

    marcadorSede = L.marker([CENTRO_CALDAS.lat, CENTRO_CALDAS.lng], { icon: iconoSede })
        .bindPopup('<strong style="color: #d4a853;">🏠 Sede Principal</strong><br>Carrera 49 #134 sur-41<br>Caldas, Antioquia')
        .addTo(mapa);

    console.log('✅ Mapa de seguimiento inicializado en Caldas, Antioquia');
}

// ============================================================
// 3. DIBUJO DE PEDIDOS EN EL MAPA
// ============================================================

/**
 * Dibuja los marcadores de pedidos en el mapa.
 * Cada pedido se representa como un punto circular coloreado.
 * @param {Pedido[]} pedidos - Array de pedidos a dibujar
 */
function dibujarPedidos(pedidos) {
    // -- Limpiar capa anterior --
    layerPedidos.clearLayers();

    pedidos.forEach(function(pedido) {
        // Determinar color según si tiene grupo asignado
        var color = pedido.grupo >= 0
            ? colorGeocerca(pedido.grupo)
            : '#c0c0d0'; // Gris si no está asignado

        // -- Crear marcador circular --
        var marcador = L.circleMarker([pedido.lat, pedido.lng], {
            radius: 6,
            fillColor: color,
            color: '#0a0a0f',
            weight: 2,
            opacity: 1,
            fillOpacity: 0.9
        });

        // -- Popup con información del pedido --
        var popupHTML = '<div style="min-width: 180px;">' +
            '<strong style="color: #d4a853;">' + pedido.codigo + '</strong><br>' +
            '<span style="color: #c0c0d0;">' + pedido.producto + '</span><br>' +
            '<span style="color: #8a8a9a;">Peso: ' + pedido.peso + ' kg</span><br>' +
            '<span style="color: ' + colorEstado(pedido.estado) + ';">● ' + traducirEstado(pedido.estado) + '</span>' +
            '</div>';

        marcador.bindPopup(popupHTML);
        layerPedidos.addLayer(marcador);
    });

    // -- Actualizar contador en la UI --
    var contadorEl = document.getElementById('contadorPedidos');
    if (contadorEl) {
        contadorEl.textContent = pedidos.length + ' pedidos';
    }
}

// ============================================================
// 4. DIBUJO DE GEOCERCAS EN EL MAPA
// ============================================================

/**
 * Dibuja las geocercas como círculos semitransparentes en el mapa.
 * Cada geocerca tiene un color único y muestra info en tooltip.
 * @param {Object} resultado - Resultado del algoritmo { grupos, centroides }
 * @param {number} dMax - Radio máximo en grados (para convertir a metros)
 */
function dibujarGeocercas(resultado, dMax) {
    // -- Limpiar capa anterior --
    layerGeocercas.clearLayers();

    if (!resultado || !resultado.centroides) return;

    resultado.centroides.forEach(function(centroide, i) {
        var color = colorGeocerca(i);
        var numPedidos = resultado.grupos[i] ? resultado.grupos[i].length : 0;

        // -- Convertir dMax (grados) a metros aproximados --
        // 1 grado ≈ 111,320 metros en el ecuador
        var radioMetros = dMax * 111320;

        // -- Dibujar círculo de la geocerca --
        var circulo = L.circle([centroide.lat, centroide.lng], {
            radius: radioMetros,
            fillColor: color,
            color: color,
            weight: 2,
            opacity: 0.7,
            fillOpacity: 0.12,
            dashArray: '8, 6'
        });

        // -- Tooltip con información de la geocerca --
        circulo.bindTooltip(
            '<strong>Geocerca #' + (i + 1) + '</strong><br>' +
            'Pedidos: ' + numPedidos + '<br>' +
            'Radio: ' + radioMetros.toFixed(0) + 'm',
            { className: 'geocerca-tooltip', sticky: true }
        );

        layerGeocercas.addLayer(circulo);

        // -- Marcador del centroide --
        var iconoCentroide = L.divIcon({
            html: '<div style="background: ' + color + '; width: 14px; height: 14px; border-radius: 50%; border: 2px solid #0a0a0f; box-shadow: 0 0 10px ' + color + '40;"></div>',
            className: 'centroide-marker',
            iconSize: [14, 14],
            iconAnchor: [7, 7]
        });

        var marcadorCentro = L.marker([centroide.lat, centroide.lng], { icon: iconoCentroide });
        layerGeocercas.addLayer(marcadorCentro);
    });

    // -- Actualizar contadores --
    var contadorGeo = document.getElementById('contadorGeocercas');
    if (contadorGeo) {
        contadorGeo.textContent = resultado.centroides.length + ' geocercas';
    }
}

// ============================================================
// 5. VISUALIZACIÓN DE RUTAS
// ============================================================

/**
 * Dibuja líneas de ruta desde la sede a cada centroide de geocerca.
 * @param {Object} resultado - Resultado del algoritmo con centroides
 */
function dibujarRutas(resultado) {
    layerRutas.clearLayers();
    if (!resultado || !resultado.centroides) return;

    resultado.centroides.forEach(function(centroide, i) {
        var color = colorGeocerca(i);

        // Línea punteada desde sede al centroide
        var ruta = L.polyline(
            [[CENTRO_CALDAS.lat, CENTRO_CALDAS.lng], [centroide.lat, centroide.lng]],
            {
                color: color,
                weight: 2,
                opacity: 0.4,
                dashArray: '6, 10'
            }
        );
        layerRutas.addLayer(ruta);
    });
}

// ============================================================
// 6. UTILIDADES DE VISUALIZACIÓN
// ============================================================

/**
 * Devuelve el color correspondiente al estado de un paquete.
 * @param {string} estado - Estado del paquete
 * @returns {string} Color hexadecimal
 */
function colorEstado(estado) {
    var colores = {
        'procesando':   '#ffc107',
        'preparado':    '#17a2b8',
        'en_transito':  '#a78bfa',
        'en_geocerca':  '#d4a853',
        'entregado':    '#28a745',
        'devuelto':     '#dc3545'
    };
    return colores[estado] || '#8a8a9a';
}

/**
 * Traduce el estado al español legible.
 * @param {string} estado - Estado técnico
 * @returns {string} Estado en español
 */
function traducirEstado(estado) {
    var traducciones = {
        'procesando':   'Procesando',
        'preparado':    'Preparado',
        'en_transito':  'En Tránsito',
        'en_geocerca':  'En Geocerca',
        'entregado':    'Entregado',
        'devuelto':     'Devuelto'
    };
    return traducciones[estado] || estado;
}

/**
 * Ajusta la vista del mapa para mostrar todos los pedidos.
 * @param {Pedido[]} pedidos - Array de pedidos
 */
function ajustarVistaMapa(pedidos) {
    if (!mapa || pedidos.length === 0) return;

    var bounds = L.latLngBounds(pedidos.map(function(p) {
        return [p.lat, p.lng];
    }));

    // Incluir la sede en los bounds
    bounds.extend([CENTRO_CALDAS.lat, CENTRO_CALDAS.lng]);

    mapa.fitBounds(bounds, { padding: [40, 40], maxZoom: 15 });
}

/**
 * Limpia todas las capas del mapa (excepto tiles y sede).
 */
function limpiarMapa() {
    if (layerPedidos) layerPedidos.clearLayers();
    if (layerGeocercas) layerGeocercas.clearLayers();
    if (layerRutas) layerRutas.clearLayers();
}

// ============================================================
// FIN DEL ARCHIVO SEGUIMIENTO-MAP.JS
// ============================================================
