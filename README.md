# 💈 Clave del Peluquero Exitoso

> Sistema web integral para gestión de peluquería con seguimiento de paquetes, geocercas adaptativas, chatbot IA, dashboard de administración y portafolio de desarrollador.

![HTML5](https://img.shields.io/badge/HTML5-E34F26?style=for-the-badge&logo=html5&logoColor=white)
![CSS3](https://img.shields.io/badge/CSS3-1572B6?style=for-the-badge&logo=css3&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black)
![PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)
![Python](https://img.shields.io/badge/Python-3776AB?style=for-the-badge&logo=python&logoColor=white)
![C++](https://img.shields.io/badge/C++-00599C?style=for-the-badge&logo=cplusplus&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![Leaflet](https://img.shields.io/badge/Leaflet-199900?style=for-the-badge&logo=leaflet&logoColor=white)

---

## 📋 Descripción

**Clave del Peluquero Exitoso** es una plataforma web completa que combina:

- 🏠 **Sitio web premium** con diseño Glassmorphism oscuro y dorado
- 🗺️ **Sistema de seguimiento** con geocercas adaptativas y mapa interactivo Leaflet
- 🤖 **Chatbot con IA** integrada para atención al cliente
- 📊 **Dashboard de administración** con gráficos y métricas
- 🔐 **Sistema de autenticación** PHP con arquitectura MVC
- ⚡ **Algoritmo de agrupamiento** implementado en 3 lenguajes (JavaScript, Python, C++)
- 🎨 **Portafolio de desarrollador** y materiales académicos

---

## 🏗️ Estructura del Proyecto

```
clave/
│
├── 📄 index.html                # Página principal (diseño premium oscuro)
├── 📄 seguimiento.html          # Sistema de seguimiento + mapa geocercas
├── 📄 acceder.html              # Página de registro/acceso
├── 📄 login.php                 # Sistema de login PHP
├── 📄 config.php                # Configuración de base de datos
├── 📄 chatbot-api.php           # API del chatbot IA
├── 📄 database.sql              # Esquema BD principal
├── 📄 seguimiento.sql           # Esquema BD seguimiento (5 tablas)
├── 📄 servicios.html            # Página de servicios
├── 📄 404.html                  # Página de error personalizada
│
├── 📁 controlador/              # Controladores MVC
│   ├── ControladorAcceso.php
│   └── ControladorUsuario.php
│
├── 📁 modelo/                   # Modelos MVC
│   ├── Acceso.php
│   ├── Conexion.php
│   ├── CrudUsuario.php
│   └── Usuario.php
│
├── 📁 css/                      # Hojas de estilo
│   ├── modern.css               # Diseño premium principal
│   ├── seguimiento.css          # Estilos del mapa/seguimiento
│   ├── chatbot.css              # Estilos del chatbot
│   ├── ingresar.css             # Estilos del login
│   ├── style.css                # Estilos base
│   └── bootstrap.min.css       # Bootstrap
│
├── 📁 js/                       # JavaScript
│   ├── geocercas.js             # Algoritmo de geocercas (JS)
│   ├── seguimiento-map.js       # Controlador mapa Leaflet
│   ├── chatbot.js               # Lógica del chatbot
│   └── main.js                  # JS principal
│
├── 📁 python/                   # Implementación Python
│   └── geocercas.py             # Algoritmo con dataclasses + CLI
│
├── 📁 cpp/                      # Implementación C++
│   └── geocercas.cpp            # Algoritmo con STL (ready for WASM)
│
├── 📁 dashboard/                # Panel de administración
│   ├── pages/                   # Páginas del dashboard
│   ├── css/                     # Estilos del dashboard
│   ├── js/                      # Scripts del dashboard
│   └── fonts/                   # Fuentes
│
├── 📁 img/                      # Imágenes del sitio
├── 📁 lib/                      # Librerías JS externas
├── 📁 scss/                     # SASS sources
├── 📁 PHP/                      # Conexión BD adicional
│
├── 📁 sist_paquetes/            # Proyecto integrado
│   ├── index.html               # Portafolio Developer Python
│   ├── graf.html                # Mapa visual: Sistemas Operativos de Red
│   ├── mapa.py                  # Gráficas matplotlib (cuota de mercado S.O.)
│   ├── styles.css               # Estilos del portafolio
│   └── script.js                # Lógica del portafolio
│
└── 📄 Páginas adicionales...
    ├── about.html
    ├── contact.html
    ├── gallery.html
    ├── price.html
    ├── service.html
    ├── team.html
    └── testimonial.html
```

---

## 🚀 Funcionalidades Principales

### 🗺️ Sistema de Seguimiento con Geocercas
- Mapa interactivo con **Leaflet.js + CartoDB Dark** (sin API key)
- Geocercas adaptativas con algoritmo de agrupamiento por proximidad
- Rastreo de paquetes con código tipo `CLV-2026-XXXXX`
- Benchmark comparativo: JS vs Python vs C++ (WASM)
- Base de datos con 5 tablas normalizadas

### 🤖 Chatbot con IA
- Interfaz tipo terminal/hacker con efecto typewriter
- API PHP para procesamiento de mensajes
- Diseño Glassmorphism oscuro integrado

### 📊 Dashboard de Administración
- Gráficos con Morris.js y Flot
- Tablas de datos con DataTables
- Panel de métricas en tiempo real

### 🔐 Sistema de Login (MVC)
- Arquitectura Modelo-Vista-Controlador en PHP
- Conexión segura a MySQL
- Gestión de usuarios y sesiones

### 📈 Materiales Académicos (sist_paquetes)
- Portafolio de desarrollador Python
- Mapa visual interactivo de Sistemas Operativos de Red (NOS)
- Gráficas de cuota de mercado con matplotlib

---

## ⚙️ Instalación

### Requisitos
- [XAMPP](https://www.apachefriends.org/) (Apache + MySQL + PHP)
- Navegador web moderno
- Python 3.x (opcional, para scripts de análisis)

### Pasos
1. Clona el repositorio:
   ```bash
   git clone https://github.com/TU_USUARIO/clave-peluquero-exitoso.git
   ```

2. Copia la carpeta `clave/` dentro de `C:/xampp/htdocs/clave2.00/`

3. Importa las bases de datos en phpMyAdmin:
   - `database.sql` — Esquema principal
   - `seguimiento.sql` — Sistema de seguimiento

4. Configura la conexión en `config.php`:
   ```php
   $host = 'localhost';
   $dbname = 'clave_peluquero';
   $user = 'root';
   $pass = '';
   ```

5. Inicia Apache y MySQL desde XAMPP

6. Accede desde tu navegador:
   ```
   http://localhost/clave2.00/clave/index.html
   ```

---

## 🛠️ Tecnologías

| Categoría | Tecnologías |
|-----------|------------|
| **Frontend** | HTML5, CSS3, JavaScript, Bootstrap, SCSS |
| **Backend** | PHP 7+ (MVC) |
| **Base de Datos** | MySQL / MariaDB |
| **Mapas** | Leaflet.js + CartoDB Dark Tiles |
| **Gráficos** | Morris.js, Flot, Chart.js, Matplotlib |
| **Algoritmos** | JavaScript, Python, C++ |
| **Diseño** | Glassmorphism, Responsive, Premium Dark Theme |

---

## 📄 Licencia

Este proyecto utiliza la plantilla [Sparlex](https://htmlcodex.com/spa-website-template) como base.
Consulta el archivo [LICENSE.txt](LICENSE.txt) para más detalles.

---

> Desarrollado con ❤️ para **Clave del Peluquero Exitoso**
