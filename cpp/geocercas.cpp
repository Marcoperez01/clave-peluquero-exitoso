/**
 * ==========================================================================
 * GEOCERCAS.CPP - Algoritmo de Geocercas Dinámicas en C++
 * Clave del Peluquero Exitoso
 * Fecha: 2026-04-06
 * Descripción: Implementación optimizada del algoritmo de agrupamiento
 *              logístico para compilar a WebAssembly (WASM) con Emscripten.
 *
 * Compilación nativa:
 *   g++ -O2 -std=c++17 -o geocercas geocercas.cpp
 *   ./geocercas --pedidos 100 --dmax 0.015 --capmax 15
 *
 * Compilación a WASM (requiere Emscripten SDK):
 *   emcc -O2 -std=c++17 -s WASM=1 -s EXPORTED_FUNCTIONS="['_agruparGeocercas']"
 *        -o geocercas.js geocercas.cpp
 *
 * Objetivo Específico:
 *   Implementar el algoritmo de geocercas dinámicas en C++ utilizando
 *   estructuras STL (vector, cmath) con medición de rendimiento mediante
 *   <chrono>, verificando la correcta inicialización de variables
 *   acumuladoras y la condición de término del ciclo principal.
 * ==========================================================================
 */

#include <iostream>
#include <vector>
#include <cmath>
#include <chrono>
#include <random>
#include <string>
#include <iomanip>
#include <limits>
#include <sstream>

// ============================================================
// 1. CONSTANTES DE CONFIGURACIÓN
// ============================================================

/** Centro de Caldas, Antioquia (Carrera 49 #134 sur-41) */
constexpr double CENTRO_LAT = 6.090722;
constexpr double CENTRO_LNG = -75.638787;

/** Radio de dispersión para generar pedidos (~2km en grados) */
constexpr double RADIO_DISPERSION = 0.018;

/** Pi para cálculos trigonométricos */
constexpr double PI = 3.14159265358979323846;


// ============================================================
// 2. ESTRUCTURAS DE DATOS
// ============================================================

/**
 * @struct Pedido
 * @brief Representa un pedido/paquete con ubicación geográfica.
 *
 * Cada pedido tiene coordenadas de destino, peso del paquete,
 * nombre del producto y un índice de grupo asignado.
 */
struct Pedido {
    int id;              ///< Identificador único del pedido
    double lat;          ///< Latitud del destino de entrega
    double lng;          ///< Longitud del destino de entrega
    double peso;         ///< Peso del paquete en kilogramos
    std::string producto; ///< Nombre del producto
    std::string codigo;  ///< Código de rastreo (CLV-2026-XXXXX)
    int grupo;           ///< Geocerca asignada (-1 = sin asignar)

    /// Constructor por defecto: inicializa grupo a -1 (sin asignar)
    Pedido() : id(0), lat(0), lng(0), peso(0), grupo(-1) {}

    /// Constructor con parámetros
    Pedido(int _id, double _lat, double _lng, double _peso,
           const std::string& _producto, const std::string& _codigo)
        : id(_id), lat(_lat), lng(_lng), peso(_peso),
          producto(_producto), codigo(_codigo), grupo(-1) {}
};


/**
 * @struct Centroide
 * @brief Centro geométrico de una geocerca.
 */
struct Centroide {
    double lat;  ///< Latitud del centroide
    double lng;  ///< Longitud del centroide

    Centroide(double _lat = 0, double _lng = 0) : lat(_lat), lng(_lng) {}
};


/**
 * @struct ResultadoBenchmark
 * @brief Métricas de rendimiento del algoritmo.
 */
struct ResultadoBenchmark {
    int num_pedidos;     ///< Cantidad de pedidos procesados
    int num_grupos;      ///< Cantidad de geocercas formadas
    double tiempo_ms;    ///< Tiempo de ejecución en milisegundos
    double memoria_mb;   ///< Memoria utilizada (estimada) en megabytes
};


// ============================================================
// 3. GENERACIÓN DE DATOS SIMULADOS
// ============================================================

/**
 * @brief Genera un código de rastreo único tipo "CLV-2026-XXXXX"
 * @param rng Generador de números aleatorios
 * @return Código de rastreo como string
 */
std::string generarCodigoRastreo(std::mt19937& rng) {
    std::uniform_int_distribution<int> dist(10000, 99999);
    return "CLV-2026-" + std::to_string(dist(rng));
}


/**
 * @brief Genera N pedidos aleatorios alrededor de Caldas, Antioquia.
 *
 * Utiliza distribución gaussiana (Box-Muller) para dispersión realista.
 *
 * @param n Cantidad de pedidos a generar
 * @return Vector de pedidos con coordenadas aleatorias
 */
std::vector<Pedido> generarPedidos(int n) {
    // -- Inicialización del generador aleatorio con semilla --
    std::mt19937 rng(std::chrono::steady_clock::now().time_since_epoch().count());
    std::uniform_real_distribution<double> uniforme(0.0, 1.0);
    std::uniform_real_distribution<double> peso_dist(0.2, 5.0);

    // -- Lista de productos para simulación --
    const std::vector<std::string> productos = {
        "Shampoo Profesional Keratina 500ml",
        "Kit Tinte + Oxidante 90ml",
        "Proteina Capilar Reconstructora 250g",
        "Secador Profesional 2200W",
        "Plancha de Titanio Pro",
        "Acondicionador Argan 400ml",
        "Cepillo Ceramico Ionico",
        "Suero Capilar Reparador 50ml",
        "Tijeras Profesionales 6.5 pulgadas",
        "Spray Fijador Extra Fuerte 300ml"
    };

    std::vector<Pedido> pedidos;
    pedidos.reserve(n);  // Reservar memoria de antemano

    for (int i = 0; i < n; ++i) {
        // -- Distribución gaussiana con Box-Muller --
        double u1 = uniforme(rng);
        double u2 = uniforme(rng);
        if (u1 < 1e-10) u1 = 1e-10;  // Evitar log(0)

        double z0 = std::sqrt(-2.0 * std::log(u1)) * std::cos(2.0 * PI * u2);
        double z1 = std::sqrt(-2.0 * std::log(u1)) * std::sin(2.0 * PI * u2);

        double lat = CENTRO_LAT + z0 * RADIO_DISPERSION;
        double lng = CENTRO_LNG + z1 * RADIO_DISPERSION;
        double peso = std::round(peso_dist(rng) * 100.0) / 100.0;

        // Seleccionar producto aleatorio
        std::uniform_int_distribution<int> prod_dist(0, productos.size() - 1);
        std::string producto = productos[prod_dist(rng)];
        std::string codigo = generarCodigoRastreo(rng);

        pedidos.emplace_back(i + 1, lat, lng, peso, producto, codigo);
    }

    return pedidos;
}


// ============================================================
// 4. ALGORITMO DE AGRUPAMIENTO POR GEOCERCAS
// ============================================================

/**
 * @brief Calcula la distancia euclidiana entre dos puntos geográficos.
 *
 * Fórmula simplificada para distancias cortas (mismo municipio).
 * Para distancias largas se debería usar Haversine.
 *
 * @param lat1, lng1 Coordenadas del punto 1
 * @param lat2, lng2 Coordenadas del punto 2
 * @return Distancia euclidiana en grados
 */
inline double distanciaEuclidiana(double lat1, double lng1,
                                   double lat2, double lng2) {
    double dLat = lat2 - lat1;
    double dLng = lng2 - lng1;
    return std::sqrt(dLat * dLat + dLng * dLng);
}


/**
 * @brief Algoritmo principal de agrupamiento por geocercas dinámicas.
 *
 * LÓGICA:
 *   1. Inicializar vectores de grupos y centroides (vacíos)
 *   2. Para cada pedido (i = 0 hasta n-1):
 *      a. Buscar grupo existente más cercano con dist ≤ dMax
 *      b. Verificar capacidad (grupo.size() < capMax)
 *      c. Si existe → asignar y recalcular centroide
 *      d. Si no → crear nuevo grupo con pedido como semilla
 *   3. Retornar estructuras de datos
 *
 * VERIFICACIÓN DE TERMINACIÓN:
 *   - Ciclo externo: for(i=0; i<n; i++) → siempre termina en N pasos
 *   - Ciclo interno: for(g=0; g<grupos.size(); g++) → termina en K pasos
 *   - No hay condiciones inalcanzables
 *   - Variables acumuladoras correctamente inicializadas
 *
 * COMPLEJIDAD: O(n × k) donde n = pedidos, k = grupos
 *
 * @param pedidos Vector de pedidos a agrupar
 * @param dMax Distancia máxima (umbral de proximidad) en grados
 * @param capMax Capacidad máxima de paquetes por geocerca
 * @param[out] out_centroides Vector de centroides de salida
 * @return Vector de vectores (cada sub-vector es un grupo)
 */
std::vector<std::vector<Pedido>> agruparGeocercas(
    std::vector<Pedido>& pedidos,
    double dMax,
    int capMax,
    std::vector<Centroide>& out_centroides)
{
    // -- Variables acumuladoras inicializadas correctamente --
    std::vector<std::vector<Pedido>> grupos;    // Grupos formados (vacío ✓)
    std::vector<Centroide> centroides;           // Centroides (vacío ✓)
    std::vector<double> pesos_grupo;             // Pesos acumulados (vacío ✓)

    int n = static_cast<int>(pedidos.size());

    // -- Ciclo principal: iterar exactamente N veces --
    // Condición de término: i < n (siempre alcanzable, no es ciclo infinito)
    for (int i = 0; i < n; ++i) {
        Pedido& pedido = pedidos[i];

        // Variables de búsqueda reinicializadas en cada iteración
        int mejorGrupo = -1;                    // Índice del mejor grupo
        double mejorDistancia = std::numeric_limits<double>::infinity();

        // -- Buscar grupo más cercano (ciclo interno) --
        int numGrupos = static_cast<int>(grupos.size());
        for (int g = 0; g < numGrupos; ++g) {
            // Restricción 1: Capacidad máxima
            if (static_cast<int>(grupos[g].size()) >= capMax) {
                continue;
            }

            // Calcular distancia euclidiana al centroide
            double dist = distanciaEuclidiana(
                pedido.lat, pedido.lng,
                centroides[g].lat, centroides[g].lng
            );

            // Restricción 2: Radio máximo
            if (dist <= dMax && dist < mejorDistancia) {
                mejorGrupo = g;
                mejorDistancia = dist;
            }
        }

        // -- Decisión de asignación --
        if (mejorGrupo != -1) {
            // Asignar al grupo existente más cercano
            grupos[mejorGrupo].push_back(pedido);
            pesos_grupo[mejorGrupo] += pedido.peso;
            pedido.grupo = mejorGrupo;

            // Recalcular centroide (media incremental)
            int m = static_cast<int>(grupos[mejorGrupo].size());
            centroides[mejorGrupo].lat =
                ((centroides[mejorGrupo].lat * (m - 1)) + pedido.lat) / m;
            centroides[mejorGrupo].lng =
                ((centroides[mejorGrupo].lng * (m - 1)) + pedido.lng) / m;
        } else {
            // Crear nuevo grupo con este pedido como semilla
            pedido.grupo = static_cast<int>(grupos.size());
            grupos.push_back({pedido});
            centroides.emplace_back(pedido.lat, pedido.lng);
            pesos_grupo.push_back(pedido.peso);
        }
    }

    // Copiar centroides al parámetro de salida
    out_centroides = centroides;

    return grupos;
}


// ============================================================
// 5. FUNCIÓN PRINCIPAL Y BENCHMARK
// ============================================================

/**
 * @brief Punto de entrada principal. Ejecuta el benchmark completo.
 *
 * Uso:
 *   ./geocercas                               (valores por defecto)
 *   ./geocercas --pedidos 500 --dmax 0.02      (personalizado)
 *
 * @param argc Número de argumentos
 * @param argv Argumentos de línea de comandos
 * @return 0 si ejecuta correctamente
 */
int main(int argc, char* argv[]) {
    // -- Valores por defecto --
    int numPedidos = 100;
    double dMax = 0.015;
    int capMax = 15;

    // -- Parsear argumentos de línea de comandos --
    for (int i = 1; i < argc; ++i) {
        std::string arg = argv[i];
        if (arg == "--pedidos" && i + 1 < argc) {
            numPedidos = std::stoi(argv[++i]);
        } else if (arg == "--dmax" && i + 1 < argc) {
            dMax = std::stod(argv[++i]);
        } else if (arg == "--capmax" && i + 1 < argc) {
            capMax = std::stoi(argv[++i]);
        }
    }

    // -- Encabezado --
    std::cout << "\n" << std::string(60, '=') << std::endl;
    std::cout << "  GEOCERCAS DINAMICAS - Clave del Peluquero Exitoso (C++)" << std::endl;
    std::cout << "  Centro: Caldas, Antioquia (" << CENTRO_LAT << ", " << CENTRO_LNG << ")" << std::endl;
    std::cout << std::string(60, '=') << "\n" << std::endl;

    // -- Generar pedidos --
    std::cout << "Generando " << numPedidos << " pedidos aleatorios..." << std::endl;
    auto pedidos = generarPedidos(numPedidos);

    // -- Ejecutar algoritmo con medición de tiempo --
    std::cout << "Ejecutando algoritmo (dMax=" << dMax
              << ", capMax=" << capMax << ")..." << std::endl;

    auto inicio = std::chrono::high_resolution_clock::now();

    std::vector<Centroide> centroides;
    auto grupos = agruparGeocercas(pedidos, dMax, capMax, centroides);

    auto fin = std::chrono::high_resolution_clock::now();

    // -- Calcular métricas --
    double tiempo_ms = std::chrono::duration<double, std::milli>(fin - inicio).count();
    // Estimación de memoria: sizeof(Pedido) * N + overhead
    double memoria_mb = (sizeof(Pedido) * numPedidos) / (1024.0 * 1024.0);

    // -- Mostrar resultados --
    std::cout << "\n" << std::string(40, '-') << std::endl;
    std::cout << "  RESULTADOS" << std::endl;
    std::cout << std::string(40, '-') << std::endl;
    std::cout << "  Lenguaje:        C++" << std::endl;
    std::cout << "  Pedidos:         " << numPedidos << std::endl;
    std::cout << "  Geocercas:       " << grupos.size() << std::endl;
    std::cout << std::fixed << std::setprecision(3);
    std::cout << "  Tiempo:          " << tiempo_ms << " ms" << std::endl;
    std::cout << "  Memoria (est):   " << memoria_mb << " MB" << std::endl;
    std::cout << std::string(40, '-') << "\n" << std::endl;

    // -- Detalle por geocerca --
    for (size_t i = 0; i < grupos.size(); ++i) {
        double pesoTotal = 0;
        for (const auto& p : grupos[i]) pesoTotal += p.peso;

        std::cout << "  Geocerca #" << (i + 1) << ": "
                  << grupos[i].size() << " pedidos, "
                  << std::setprecision(2) << pesoTotal << " kg, "
                  << "centro (" << std::setprecision(4)
                  << centroides[i].lat << ", " << centroides[i].lng << ")"
                  << std::endl;
    }

    std::cout << "\nAlgoritmo completado exitosamente.\n" << std::endl;

    return 0;
}

// ==========================================================================
// FIN DEL ARCHIVO GEOCERCAS.CPP
// ==========================================================================
