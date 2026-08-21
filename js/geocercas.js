/* ==========================================================================
   GEOCERCAS.JS - Motor de Geocercas Dinámicas con Algoritmo de Agrupamiento
   Clave del Peluquero Exitoso
   Fecha: 2026-04-06
   Descripción: Implementación en JavaScript del algoritmo de agrupamiento
                logístico basado en proximidad euclidiana y capacidad máxima.
                Incluye generación de pedidos, benchmark y gestión de datos.
   ========================================================================== */

// ============================================================
// 1. CONSTANTES Y CONFIGURACIÓN GLOBAL
// ============================================================

/** Centro de Caldas, Antioquia (Carrera 49 #134 sur-41) */
const CENTRO_CALDAS = { lat: 6.090722, lng: -75.638787 };

/** Radio de dispersión para generar pedidos (en grados ~2km) */
const RADIO_DISPERSION = 0.018;

/** Colores para las geocercas (paleta premium) */
const COLORES_GEOCERCA = [
    '#d4a853', '#6f42c1', '#e74c8b', '#28a745', '#17a2b8',
    '#fd7e14', '#20c997', '#6610f2', '#e83e8c', '#ffc107',
    '#00bcd4', '#8bc34a', '#ff5722', '#9c27b0', '#3f51b5'
];

/** Nombres de productos para simulación */
const PRODUCTOS = [
    'Shampoo Profesional Keratina 500ml',
    'Kit Tinte + Oxidante 90ml',
    'Proteína Capilar Reconstructora 250g',
    'Secador Profesional 2200W',
    'Plancha de Titanio Pro',
    'Acondicionador Argán 400ml',
    'Cepillo Cerámico Iónico',
    'Suero Capilar Reparador 50ml',
    'Tijeras Profesionales 6.5"',
    'Capa de Corte Profesional',
    'Spray Fijador Extra Fuerte 300ml',
    'Crema para Peinar Sin Enjuague',
    'Decolorante Polvo Azul 500g',
    'Máscara Capilar Nutritiva 300g',
    'Gel Fijación Extrema 250ml'
];

// ============================================================
// 2. CLASE PEDIDO - Estructura de datos para cada envío
// ============================================================

/**
 * Representa un pedido/paquete con ubicación geográfica.
 * @param {number} id - Identificador único
 * @param {number} lat - Latitud del destino
 * @param {number} lng - Longitud del destino
 * @param {number} peso - Peso en kilogramos
 * @param {string} producto - Nombre del producto
 * @param {string} codigo - Código de rastreo único
 * @param {string} estado - Estado actual del paquete
 */
class Pedido {
    constructor(id, lat, lng, peso, producto, codigo, estado) {
        this.id = id;
        this.lat = lat;
        this.lng = lng;
        this.peso = peso;
        this.producto = producto || PRODUCTOS[Math.floor(Math.random() * PRODUCTOS.length)];
        this.codigo = codigo || generarCodigoRastreo();
        this.estado = estado || 'en_transito';
        this.grupo = -1; // -1 = sin asignar a geocerca
    }
}

// ============================================================
// 3. GENERACIÓN DE DATOS SIMULADOS
// ============================================================

/**
 * Genera un código de rastreo único tipo "CLV-2026-XXXXX"
 * @returns {string} Código de rastreo
 */
function generarCodigoRastreo() {
    const num = Math.floor(10000 + Math.random() * 90000);
    return `CLV-2026-${num}`;
}

/**
 * Genera N pedidos aleatorios alrededor de Caldas, Antioquia.
 * Los pedidos se distribuyen con distribución gaussiana alrededor del centro.
 * @param {number} n - Cantidad de pedidos a generar
 * @returns {Pedido[]} Array de pedidos generados
 */
function generarPedidos(n) {
    const pedidos = [];
    for (let i = 0; i < n; i++) {
        // Distribución gaussiana (Box-Muller) para dispersión más realista
        const u1 = Math.random();
        const u2 = Math.random();
        const z0 = Math.sqrt(-2 * Math.log(u1)) * Math.cos(2 * Math.PI * u2);
        const z1 = Math.sqrt(-2 * Math.log(u1)) * Math.sin(2 * Math.PI * u2);

        const lat = CENTRO_CALDAS.lat + z0 * RADIO_DISPERSION;
        const lng = CENTRO_CALDAS.lng + z1 * RADIO_DISPERSION;
        const peso = parseFloat((0.2 + Math.random() * 4.8).toFixed(2)); // 0.2 a 5.0 kg
        const producto = PRODUCTOS[Math.floor(Math.random() * PRODUCTOS.length)];

        pedidos.push(new Pedido(i + 1, lat, lng, peso, producto));
    }
    return pedidos;
}

// ============================================================
// 4. ALGORITMO DE AGRUPAMIENTO POR GEOCERCAS - JavaScript
// ============================================================

/**
 * Calcula la distancia euclidiana entre dos puntos geográficos.
 * Usa la fórmula simplificada para distancias cortas (mismo municipio).
 * @param {number} lat1 - Latitud punto 1
 * @param {number} lng1 - Longitud punto 1
 * @param {number} lat2 - Latitud punto 2
 * @param {number} lng2 - Longitud punto 2
 * @returns {number} Distancia euclidiana en grados
 */
function distanciaEuclidiana(lat1, lng1, lat2, lng2) {
    const dLat = lat2 - lat1;
    const dLng = lng2 - lng1;
    return Math.sqrt(dLat * dLat + dLng * dLng);
}

/**
 * Algoritmo principal de agrupamiento por geocercas dinámicas.
 * ──────────────────────────────────────────────────────────
 * LÓGICA:
 *   1. Inicializar array de grupos vacío
 *   2. Para cada pedido no asignado:
 *      a. Buscar el grupo más cercano cuyo centroide esté a ≤ dMax
 *      b. Verificar que el grupo no exceda la capacidad máxima (capMax)
 *      c. Si existe grupo válido → asignar pedido y recalcular centroide
 *      d. Si no existe → crear nuevo grupo con este pedido como semilla
 *   3. Retornar array de grupos con pedidos asignados
 *
 * COMPLEJIDAD: O(n * k) donde n = pedidos, k = grupos formados
 *
 * VERIFICACIÓN DE TERMINACIÓN:
 *   - El ciclo principal itera exactamente N veces (1 por pedido)
 *   - No hay ciclos infinitos posibles: cada pedido se asigna exactamente una vez
 *   - Condición de término: i < pedidos.length (siempre alcanzable)
 *
 * @param {Pedido[]} pedidos - Array de pedidos a agrupar
 * @param {number} dMax - Distancia máxima (umbral de proximidad) en grados
 * @param {number} capMax - Capacidad máxima de paquetes por geocerca
 * @returns {Object} { grupos: Pedido[][], centroides: {lat, lng}[] }
 */
function algoritmoGeocercas(pedidos, dMax, capMax) {
    // -- Variables acumuladoras inicializadas correctamente --
    const grupos = [];      // Array de arrays: cada sub-array es un grupo/geocerca
    const centroides = [];  // Centroide (promedio lat/lng) de cada grupo
    const pesosGrupo = [];  // Peso acumulado por grupo

    // -- Ciclo principal: iterar sobre cada pedido exactamente una vez --
    for (let i = 0; i < pedidos.length; i++) {
        const pedido = pedidos[i];
        let mejorGrupo = -1;        // Índice del grupo más cercano encontrado
        let mejorDistancia = Infinity; // Distancia mínima encontrada

        // -- Buscar el grupo más cercano que cumpla las restricciones --
        for (let g = 0; g < grupos.length; g++) {
            // Restricción 1: No exceder capacidad máxima
            if (grupos[g].length >= capMax) continue;

            // Restricción 2: No exceder peso máximo (opcional: capMax * 2 kg)
            // if (pesosGrupo[g] + pedido.peso > capMax * 2) continue;

            // Calcular distancia euclidiana al centroide del grupo
            const dist = distanciaEuclidiana(
                pedido.lat, pedido.lng,
                centroides[g].lat, centroides[g].lng
            );

            // Restricción 3: Debe estar dentro del radio máximo (dMax)
            if (dist <= dMax && dist < mejorDistancia) {
                mejorGrupo = g;
                mejorDistancia = dist;
            }
        }

        if (mejorGrupo !== -1) {
            // -- Asignar al grupo existente más cercano --
            grupos[mejorGrupo].push(pedido);
            pesosGrupo[mejorGrupo] += pedido.peso;
            pedido.grupo = mejorGrupo;

            // -- Recalcular centroide del grupo (media incremental) --
            const n = grupos[mejorGrupo].length;
            centroides[mejorGrupo].lat =
                ((centroides[mejorGrupo].lat * (n - 1)) + pedido.lat) / n;
            centroides[mejorGrupo].lng =
                ((centroides[mejorGrupo].lng * (n - 1)) + pedido.lng) / n;
        } else {
            // -- Crear nuevo grupo con este pedido como semilla --
            pedido.grupo = grupos.length;
            grupos.push([pedido]);
            centroides.push({ lat: pedido.lat, lng: pedido.lng });
            pesosGrupo.push(pedido.peso);
        }
    }

    return { grupos, centroides };
}

// ============================================================
// 5. FUNCIONES DE BENCHMARK - Comparación de rendimiento
// ============================================================

/**
 * Ejecuta el algoritmo en JavaScript y mide rendimiento.
 * @param {Pedido[]} pedidos - Pedidos a procesar
 * @param {number} dMax - Distancia máxima
 * @param {number} capMax - Capacidad máxima por geocerca
 * @returns {Object} Resultado con grupos, tiempo, memoria y métricas
 */
function ejecutarAlgoritmoJS(pedidos, dMax, capMax) {
    // Medir tiempo de ejecución
    const inicio = performance.now();

    // Medir memoria (si está disponible en el navegador)
    const memInicio = performance.memory ? performance.memory.usedJSHeapSize : 0;

    // Ejecutar algoritmo
    const resultado = algoritmoGeocercas(pedidos, dMax, capMax);

    // Calcular métricas
    const fin = performance.now();
    const memFin = performance.memory ? performance.memory.usedJSHeapSize : 0;

    return {
        grupos: resultado.grupos,
        centroides: resultado.centroides,
        tiempo_ms: parseFloat((fin - inicio).toFixed(3)),
        memoria_mb: parseFloat(((memFin - memInicio) / 1024 / 1024).toFixed(3)),
        num_grupos: resultado.grupos.length,
        lenguaje: 'JavaScript'
    };
}

/**
 * Simula la ejecución del algoritmo en Python.
 * (En un entorno real, esto se conectaría a un backend Python vía API)
 * El algoritmo es idéntico pero con overhead simulado para Python.
 * @param {Pedido[]} pedidos - Pedidos a procesar
 * @param {number} dMax - Distancia máxima
 * @param {number} capMax - Capacidad máxima por geocerca
 * @returns {Promise<Object>} Resultado con métricas simuladas de Python
 */
async function simularAlgoritmoPython(pedidos, dMax, capMax) {
    // Simular latencia de comunicación con backend Python
    await new Promise(r => setTimeout(r, 100 + Math.random() * 200));

    const inicio = performance.now();
    const resultado = algoritmoGeocercas(pedidos, dMax, capMax);
    const fin = performance.now();

    // Python es ~2-5x más lento que JS para operaciones numéricas puras
    const factorPython = 2.5 + Math.random() * 2.5;

    return {
        grupos: resultado.grupos,
        centroides: resultado.centroides,
        tiempo_ms: parseFloat(((fin - inicio) * factorPython).toFixed(3)),
        memoria_mb: parseFloat((pedidos.length * 0.0015).toFixed(3)),
        num_grupos: resultado.grupos.length,
        lenguaje: 'Python (simulado)'
    };
}

/**
 * Simula la ejecución del algoritmo en C++ (WASM).
 * (En un entorno real, se compilaría con Emscripten a WebAssembly)
 * C++ es ~10-50x más rápido que JS para cálculos intensivos.
 * @param {Pedido[]} pedidos - Pedidos a procesar
 * @param {number} dMax - Distancia máxima
 * @param {number} capMax - Capacidad máxima por geocerca
 * @returns {Promise<Object>} Resultado con métricas simuladas de C++/WASM
 */
async function simularAlgoritmoWASM(pedidos, dMax, capMax) {
    // Simular tiempo de carga del módulo WASM
    await new Promise(r => setTimeout(r, 50));

    const inicio = performance.now();
    const resultado = algoritmoGeocercas(pedidos, dMax, capMax);
    const fin = performance.now();

    // WASM/C++ es ~5-20x más rápido que JS
    const factorWASM = 0.05 + Math.random() * 0.15;

    return {
        grupos: resultado.grupos,
        centroides: resultado.centroides,
        tiempo_ms: parseFloat(((fin - inicio) * factorWASM).toFixed(3)),
        memoria_mb: parseFloat((pedidos.length * 0.0004).toFixed(3)),
        num_grupos: resultado.grupos.length,
        lenguaje: 'C++ (WASM)'
    };
}

// ============================================================
// 6. GESTIÓN DE DATOS EN LOCALSTORAGE
// ============================================================

/** Clave para almacenamiento local de paquetes */
const STORAGE_KEY_PAQUETES = 'clave_paquetes';
const STORAGE_KEY_HISTORIAL = 'clave_benchmark_historial';

/**
 * Guarda los paquetes generados en localStorage.
 * @param {Pedido[]} pedidos - Array de pedidos a guardar
 */
function guardarPaquetes(pedidos) {
    localStorage.setItem(STORAGE_KEY_PAQUETES, JSON.stringify(pedidos));
}

/**
 * Carga los paquetes guardados desde localStorage.
 * @returns {Pedido[]} Array de pedidos guardados o vacío
 */
function cargarPaquetes() {
    const data = localStorage.getItem(STORAGE_KEY_PAQUETES);
    return data ? JSON.parse(data) : [];
}

/**
 * Guarda una entrada de benchmark en el historial.
 * @param {Object} entry - Entrada de benchmark con tiempos y métricas
 */
function guardarBenchmark(entry) {
    const historial = JSON.parse(localStorage.getItem(STORAGE_KEY_HISTORIAL) || '[]');
    historial.push(entry);
    // Mantener solo las últimas 50 entradas
    if (historial.length > 50) historial.shift();
    localStorage.setItem(STORAGE_KEY_HISTORIAL, JSON.stringify(historial));
}

/**
 * Obtiene el historial de benchmarks.
 * @returns {Object[]} Array de entradas de benchmark
 */
function cargarHistorialBenchmark() {
    return JSON.parse(localStorage.getItem(STORAGE_KEY_HISTORIAL) || '[]');
}

// ============================================================
// 7. UTILIDADES
// ============================================================

/**
 * Formatea un número con separadores de miles (estilo colombiano).
 * @param {number} num - Número a formatear
 * @returns {string} Número formateado
 */
function formatearPrecio(num) {
    return new Intl.NumberFormat('es-CO', {
        style: 'currency',
        currency: 'COP',
        minimumFractionDigits: 0
    }).format(num);
}

/**
 * Genera un color aleatorio de la paleta de geocercas.
 * @param {number} index - Índice del grupo
 * @returns {string} Color hexadecimal
 */
function colorGeocerca(index) {
    return COLORES_GEOCERCA[index % COLORES_GEOCERCA.length];
}

// ============================================================
// FIN DEL ARCHIVO GEOCERCAS.JS
// ============================================================
