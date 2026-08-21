import matplotlib.pyplot as plt
import numpy as np

# Configuración del gráfico
plt.figure(figsize=(14, 8))

# Datos actualizados 2024-2025
categorias = ['Computadores\nHogar', 'Tablets', 'Teléfonos\nMóviles', 'IoT']
colores = ['#2E86AB', '#A23B72', '#F18F01', '#C73E1D', '#88A2AA']

# Windows (75%), macOS (16%), Otros (9%)
hogar = [75, 16, 9]
# iPadOS (55%), Android (43%), Otros (2%)
tablets = [55, 43, 2]
# Android (72%), iOS (27%), Otros (1%)
moviles = [72, 27, 1]
# FreeRTOS (25%), Linux Embebido (20%), Otros (55%)
iot = [25, 20, 55]

# Posiciones de las barras
x = np.arange(len(categorias))
width = 0.25

# Crear barras
plt.bar(x - width, hogar, width, label='Sistema 1', color='#2E86AB')
plt.bar(x, tablets, width, label='Sistema 2', color='#A23B72')
plt.bar(x + width, moviles, width, label='Sistema 3', color='#F18F01')
plt.bar(x + width*2, iot, width, label='Sistema 4', color='#C73E1D')

# Personalizar
plt.xlabel('Categorías de Dispositivos', fontsize=12, fontweight='bold')
plt.ylabel('Porcentaje de Mercado (%)', fontsize=12, fontweight='bold')
plt.title('TOP 5 SISTEMAS OPERATIVOS POR CATEGORÍA 2025', fontsize=14, fontweight='bold')
plt.xticks(x + width/2, categorias)
plt.legend(['Windows/macOS/Linux', 'iPadOS/Android/Chrome OS', 'Android/iOS/HarmonyOS', 'FreeRTOS/Linux IoT/TinyOS'], 
           loc='upper center', bbox_to_anchor=(0.5, -0.15), ncol=2)
plt.grid(axis='y', alpha=0.3)
plt.ylim(0, 80)

# Agregar valores en las barras
for i, (h, t, m, io) in enumerate(zip(hogar, tablets, moviles, iot)):
    plt.text(i - width, h + 1, f'{h}%', ha='center', fontweight='bold')
    plt.text(i, t + 1, f'{t}%', ha='center', fontweight='bold')
    plt.text(i + width, m + 1, f'{m}%', ha='center', fontweight='bold')
    plt.text(i + width*2, io + 1, f'{io}%', ha='center', fontweight='bold')

plt.tight_layout()
plt.show()

# Versión simplificada para pastel
fig, axes = plt.subplots(2, 2, figsize=(12, 10))
categorias_pastel = ['Hogar', 'Tablets', 'Móviles', 'IoT']
datos_pastel = [hogar, tablets, moviles, iot]
labels_pastel = [['Windows', 'macOS', 'Linux'],
                 ['iPadOS', 'Android', 'Otros'],
                 ['Android', 'iOS', 'Otros'],
                 ['FreeRTOS', 'Linux Emb.', 'Otros']]

for i, ax in enumerate(axes.flat):
    wedges, texts, autotexts = ax.pie(datos_pastel[i], 
                                       labels=labels_pastel[i], 
                                       autopct='%1.1f%%',
                                       colors=colores,
                                       startangle=90)
    ax.set_title(f'S.O. en {categorias_pastel[i]}', fontweight='bold')

plt.suptitle('DISTRIBUCIÓN DE SISTEMAS OPERATIVOS POR DISPOSITIVO', fontsize=16, fontweight='bold')
plt.tight_layout()
plt.show()