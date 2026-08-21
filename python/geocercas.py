"""
==========================================================================
GEOCERCAS.PY - Algoritmo de Geocercas Dinámicas en Python
Clave del Peluquero Exitoso
Fecha: 2026-04-06
Descripción: Implementación del algoritmo de agrupamiento logístico
             basado en proximidad euclidiana y capacidad máxima.
             Diseñado para ejecutarse como módulo backend o script standalone.

Uso:
    python geocercas.py --pedidos 100 --dmax 0.015 --capmax 15

Objetivo Específico:
    Modelar el algoritmo de geocercas dinámicas utilizando umbrales de
    proximidad euclidiana y capacidad de carga máxima, verificando la
    condición de término del ciclo principal y la correcta inicialización
    de variables acumuladoras para garantizar la ausencia de errores
    lógicos (ciclos infinitos o condiciones inalcanzables).
==========================================================================
"""

import math
import time
import random
import sys
import json
from dataclasses import dataclass, field
from typing import List, Tuple, Optional

# ============================================================
# 1. CONSTANTES DE CONFIGURACIÓN
# ============================================================

# Centro de Caldas, Antioquia (Carrera 49 #134 sur-41)
CENTRO_LAT: float = 6.090722
CENTRO_LNG: float = -75.638787

# Radio de dispersión para generar pedidos (~2km en grados)
RADIO_DISPERSION: float = 0.018

# Productos disponibles para simulación
PRODUCTOS: List[str] = [
    "Shampoo Profesional Keratina 500ml",
    "Kit Tinte + Oxidante 90ml",
    "Proteína Capilar Reconstructora 250g",
    "Secador Profesional 2200W",
    "Plancha de Titanio Pro",
    "Acondicionador Argán 400ml",
    "Cepillo Cerámico Iónico",
    "Suero Capilar Reparador 50ml",
    "Tijeras Profesionales 6.5 pulgadas",
    "Spray Fijador Extra Fuerte 300ml",
]


# ============================================================
# 2. ESTRUCTURA DE DATOS: Pedido
# ============================================================

@dataclass
class Pedido:
    """
    Representa un pedido/paquete con ubicación geográfica.

    Attributes:
        id (int): Identificador único del pedido
        lat (float): Latitud del destino de entrega
        lng (float): Longitud del destino de entrega
        peso (float): Peso del paquete en kilogramos
        producto (str): Nombre del producto
        codigo (str): Código de rastreo único (CLV-2026-XXXXX)
        grupo (int): Índice de la geocerca asignada (-1 = sin asignar)
    """
    id: int
    lat: float
    lng: float
    peso: float
    producto: str = ""
    codigo: str = ""
    grupo: int = -1  # -1 indica que no está asignado a ninguna geocerca


@dataclass
class Centroide:
    """
    Representa el centro geométrico de una geocerca.

    Attributes:
        lat (float): Latitud del centroide
        lng (float): Longitud del centroide
    """
    lat: float
    lng: float


# ============================================================
# 3. GENERACIÓN DE DATOS SIMULADOS
# ============================================================

def generar_codigo_rastreo() -> str:
    """
    Genera un código de rastreo único tipo 'CLV-2026-XXXXX'.

    Returns:
        str: Código de rastreo alfanumérico
    """
    num = random.randint(10000, 99999)
    return f"CLV-2026-{num}"


def generar_pedidos(n: int) -> List[Pedido]:
    """
    Genera N pedidos aleatorios alrededor de Caldas, Antioquia.
    Utiliza distribución gaussiana (Box-Muller) para una dispersión
    más realista alrededor del centro.

    Args:
        n (int): Cantidad de pedidos a generar

    Returns:
        List[Pedido]: Lista de pedidos generados con coordenadas
    """
    pedidos: List[Pedido] = []

    for i in range(n):
        # -- Distribución gaussiana (Box-Muller) --
        u1 = random.random()
        u2 = random.random()
        # Evitar log(0) que causaría error matemático
        if u1 == 0:
            u1 = 1e-10
        z0 = math.sqrt(-2 * math.log(u1)) * math.cos(2 * math.pi * u2)
        z1 = math.sqrt(-2 * math.log(u1)) * math.sin(2 * math.pi * u2)

        lat = CENTRO_LAT + z0 * RADIO_DISPERSION
        lng = CENTRO_LNG + z1 * RADIO_DISPERSION
        peso = round(0.2 + random.random() * 4.8, 2)  # 0.2 a 5.0 kg
        producto = random.choice(PRODUCTOS)
        codigo = generar_codigo_rastreo()

        pedidos.append(Pedido(
            id=i + 1,
            lat=lat,
            lng=lng,
            peso=peso,
            producto=producto,
            codigo=codigo
        ))

    return pedidos


# ============================================================
# 4. ALGORITMO DE AGRUPAMIENTO POR GEOCERCAS
# ============================================================

def distancia_euclidiana(lat1: float, lng1: float,
                         lat2: float, lng2: float) -> float:
    """
    Calcula la distancia euclidiana entre dos puntos geográficos.
    Se usa la fórmula simplificada para distancias cortas (mismo municipio).

    Args:
        lat1, lng1: Coordenadas del punto 1
        lat2, lng2: Coordenadas del punto 2

    Returns:
        float: Distancia euclidiana en grados

    Nota:
        Para distancias largas se debería usar la fórmula de Haversine,
        pero para un municipio (~5km) la diferencia es despreciable.
    """
    d_lat = lat2 - lat1
    d_lng = lng2 - lng1
    return math.sqrt(d_lat ** 2 + d_lng ** 2)


def agrupar_geocercas(pedidos: List[Pedido],
                       d_max: float,
                       cap_max: int) -> Tuple[List[List[Pedido]], List[Centroide]]:
    """
    Algoritmo principal de agrupamiento por geocercas dinámicas.

    LÓGICA DEL ALGORITMO:
    ─────────────────────
    1. Inicializar listas de grupos y centroides (vacías)
    2. Para cada pedido (iteración sobre N pedidos):
       a. Buscar entre todos los grupos existentes el más cercano
          cuyo centroide esté a distancia ≤ d_max
       b. Verificar que el grupo no exceda cap_max pedidos
       c. Si se encuentra grupo válido → asignar pedido y
          recalcular centroide con media incremental
       d. Si no se encuentra → crear nuevo grupo (semilla)
    3. Retornar listas de grupos y centroides

    VERIFICACIÓN DE ESTRUCTURA DE CONTROL:
    ──────────────────────────────────────
    - Ciclo externo: for i in range(len(pedidos))
      → Itera exactamente N veces, SIEMPRE termina
      → No es un while, por lo tanto NO hay riesgo de ciclo infinito

    - Ciclo interno: for g in range(len(grupos))
      → Itera sobre grupos existentes (máximo N grupos)
      → Se ejecuta como máximo N×N = N² veces en total

    - Variables acumuladoras:
      → grupos: List[List[Pedido]] = [] (inicializada vacía ✓)
      → centroides: List[Centroide] = [] (inicializada vacía ✓)
      → pesos_grupo: List[float] = [] (inicializada vacía ✓)
      → mejor_grupo: int = -1 (reinicializada en cada iteración ✓)
      → mejor_distancia: float = inf (reinicializada en cada iteración ✓)

    COMPLEJIDAD:
    → Tiempo: O(n × k) donde n = pedidos, k = grupos formados
    → Espacio: O(n) para almacenar las asignaciones

    Args:
        pedidos (List[Pedido]): Lista de pedidos a agrupar
        d_max (float): Distancia máxima (umbral de proximidad) en grados
        cap_max (int): Capacidad máxima de paquetes por geocerca

    Returns:
        Tuple: (grupos: List[List[Pedido]], centroides: List[Centroide])
    """
    # -- Inicialización de variables acumuladoras --
    grupos: List[List[Pedido]] = []       # Cada elemento es un grupo de pedidos
    centroides: List[Centroide] = []       # Centroide de cada grupo
    pesos_grupo: List[float] = []          # Peso acumulado por grupo

    # -- Ciclo principal: iterar exactamente N veces (siempre termina) --
    for i in range(len(pedidos)):
        pedido = pedidos[i]

        # Variables de búsqueda reinicializadas en cada iteración
        mejor_grupo: int = -1               # Índice del grupo más cercano
        mejor_distancia: float = float('inf')  # Distancia mínima encontrada

        # -- Buscar grupo más cercano que cumpla restricciones --
        for g in range(len(grupos)):
            # Restricción 1: No exceder capacidad máxima
            if len(grupos[g]) >= cap_max:
                continue

            # Calcular distancia euclidiana al centroide
            dist = distancia_euclidiana(
                pedido.lat, pedido.lng,
                centroides[g].lat, centroides[g].lng
            )

            # Restricción 2: Debe estar dentro del radio máximo
            if dist <= d_max and dist < mejor_distancia:
                mejor_grupo = g
                mejor_distancia = dist

        # -- Decisión: asignar a grupo existente o crear nuevo --
        if mejor_grupo != -1:
            # Asignar al grupo más cercano
            grupos[mejor_grupo].append(pedido)
            pesos_grupo[mejor_grupo] += pedido.peso
            pedido.grupo = mejor_grupo

            # Recalcular centroide (media incremental)
            n = len(grupos[mejor_grupo])
            centroides[mejor_grupo].lat = (
                (centroides[mejor_grupo].lat * (n - 1) + pedido.lat) / n
            )
            centroides[mejor_grupo].lng = (
                (centroides[mejor_grupo].lng * (n - 1) + pedido.lng) / n
            )
        else:
            # Crear nuevo grupo con este pedido como semilla
            pedido.grupo = len(grupos)
            grupos.append([pedido])
            centroides.append(Centroide(lat=pedido.lat, lng=pedido.lng))
            pesos_grupo.append(pedido.peso)

    return grupos, centroides


# ============================================================
# 5. MEDICIÓN DE RENDIMIENTO (BENCHMARK)
# ============================================================

def ejecutar_benchmark(pedidos: List[Pedido],
                        d_max: float,
                        cap_max: int) -> dict:
    """
    Ejecuta el algoritmo midiendo tiempo de ejecución y uso de memoria.

    Args:
        pedidos: Lista de pedidos a procesar
        d_max: Distancia máxima en grados
        cap_max: Capacidad máxima por geocerca

    Returns:
        dict: Resultados con métricas de rendimiento
    """
    # -- Medir tiempo de ejecución --
    inicio = time.perf_counter()

    # -- Medir memoria (aproximado) --
    import tracemalloc
    tracemalloc.start()

    # Ejecutar algoritmo
    grupos, centroides = agrupar_geocercas(pedidos, d_max, cap_max)

    # Obtener métricas
    _, pico_memoria = tracemalloc.get_traced_memory()
    tracemalloc.stop()
    fin = time.perf_counter()

    tiempo_ms = (fin - inicio) * 1000  # Convertir a milisegundos
    memoria_mb = pico_memoria / (1024 * 1024)  # Convertir a megabytes

    return {
        "lenguaje": "Python",
        "num_pedidos": len(pedidos),
        "num_grupos": len(grupos),
        "tiempo_ms": round(tiempo_ms, 3),
        "memoria_mb": round(memoria_mb, 3),
        "grupos": [
            {
                "id": i,
                "num_pedidos": len(g),
                "centroide": {"lat": c.lat, "lng": c.lng},
                "peso_total": round(sum(p.peso for p in g), 2)
            }
            for i, (g, c) in enumerate(zip(grupos, centroides))
        ]
    }


# ============================================================
# 6. INTERFAZ DE LÍNEA DE COMANDOS
# ============================================================

def main():
    """
    Punto de entrada principal para ejecución desde terminal.

    Uso:
        python geocercas.py --pedidos 100 --dmax 0.015 --capmax 15
        python geocercas.py  (usa valores por defecto)
    """
    import argparse

    parser = argparse.ArgumentParser(
        description="Algoritmo de Geocercas Dinámicas - Clave del Peluquero"
    )
    parser.add_argument("--pedidos", type=int, default=100,
                        help="Número de pedidos a generar (default: 100)")
    parser.add_argument("--dmax", type=float, default=0.015,
                        help="Distancia máxima en grados (default: 0.015)")
    parser.add_argument("--capmax", type=int, default=15,
                        help="Capacidad máxima por geocerca (default: 15)")
    parser.add_argument("--json", action="store_true",
                        help="Salida en formato JSON")

    args = parser.parse_args()

    print(f"\n{'='*60}")
    print(f"  GEOCERCAS DINÁMICAS - Clave del Peluquero Exitoso")
    print(f"  Centro: Caldas, Antioquia ({CENTRO_LAT}, {CENTRO_LNG})")
    print(f"{'='*60}\n")

    # Generar pedidos
    print(f"📦 Generando {args.pedidos} pedidos aleatorios...")
    pedidos = generar_pedidos(args.pedidos)

    # Ejecutar benchmark
    print(f"🔄 Ejecutando algoritmo (d_max={args.dmax}, cap_max={args.capmax})...")
    resultado = ejecutar_benchmark(pedidos, args.dmax, args.capmax)

    if args.json:
        # Salida JSON para integración con frontend
        print(json.dumps(resultado, indent=2, ensure_ascii=False))
    else:
        # Salida legible
        print(f"\n{'─'*40}")
        print(f"  RESULTADOS")
        print(f"{'─'*40}")
        print(f"  Lenguaje:        {resultado['lenguaje']}")
        print(f"  Pedidos:         {resultado['num_pedidos']}")
        print(f"  Geocercas:       {resultado['num_grupos']}")
        print(f"  Tiempo:          {resultado['tiempo_ms']:.3f} ms")
        print(f"  Memoria pico:    {resultado['memoria_mb']:.3f} MB")
        print(f"{'─'*40}\n")

        # Detalle por geocerca
        for g in resultado["grupos"]:
            print(f"  📍 Geocerca #{g['id']+1}: "
                  f"{g['num_pedidos']} pedidos, "
                  f"{g['peso_total']} kg, "
                  f"centro ({g['centroide']['lat']:.4f}, "
                  f"{g['centroide']['lng']:.4f})")

    print(f"\n✅ Algoritmo completado exitosamente.\n")


if __name__ == "__main__":
    main()
