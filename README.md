# VIGIA-MASATRANS
> **Sistema Inteligente de Gestión HSEQ y Talento Humano**

---

## 📋 1. Objetivo del Proyecto

El objetivo principal de **VIGIA-MASATRANS** es desarrollar e implementar una solución tecnológica local para la empresa **MASATRANS**, diseñada para centralizar, automatizar y optimizar la administración del personal y los procesos de Seguridad, Salud en el Trabajo, Medio Ambiente y Calidad (HSEQ).

El sistema está proyectado para:
*   **Centralización de Datos:** Gestionar el ciclo de vida completo de los empleados de forma digital.
*   **Control de Cumplimiento:** Monitorear de manera estricta el vencimiento de cursos, certificaciones y documentos legales obligatorios.
*   **Mitigación de Riesgos:** Generar alertas tempranas e inteligentes para prevenir vacíos en la documentación del personal.
*   **Trazabilidad HSEQ:** Reemplazar de forma definitiva la matriz actual en Excel, eliminando el error humano y la duplicidad de datos.
*   **Auditorías Eficientes:** Facilitar la extracción de informes, visualización de KPI y preparación de datos para auditorías internas y externas.

---

## 💡 2. Identidad del Software: VIGIA-MASATRANS

*   **VIGIA:** Representa la vigilancia activa, el control riguroso, la prevención de riesgos y la custodia de la información.
*   **MASATRANS:** Alineación directa con la identidad y el ADN corporativo de la organización.

**Atributos de la marca:** Profesional, memorable, con alto impacto corporativo y orientado a la seguridad operativa.

---

## 🛠️ 3. Stack Tecnológico y Arquitectura

Para garantizar un software mantenible, escalable y robusto, el sistema se construyó bajo los siguientes estándares técnicos:

### Tecnologías (Stack)
*   **Frontend:** HTML5, CSS3, JavaScript (ES6) y **Bootstrap** (para un diseño responsivo, limpio y profesional).
*   **Backend:** **PHP** (Procesamiento lógico robusto en el servidor).
*   **Base de Datos:** **MySQL** (Relacional, óptima para garantizar la integridad de los datos de los trabajadores).

### Patrón de Arquitectura
El sistema implementa **Onion Architecture (Arquitectura de Cebolla)**, lo que asegura el desacoplamiento de código mediante capas claras:
1.  **Core / Dominio:** Contiene las entidades de negocio (Empleado, Curso, Alerta) puras, sin dependencias externas.
2.  **Servicios / Aplicación:** Define la lógica de negocio y los casos de uso (ej. procesar una alerta de vencimiento).
3.  **Infraestructura / Capa Externa:** Maneja las conexiones a la base de datos MySQL, librerías de terceros (lectores de Excel) y la interfaz de usuario con Bootstrap.

---

## 📦 4. Módulos Principales del Sistema

El software está dividido en módulos estratégicos controlados por el área de **Gestión Humana (GH)**:

1.  **Módulo de Gestión de Empleados:** Registro, edición, hojas de vida digitales y control del estado contractual de cada trabajador.
2.  **Módulo de Carga Masiva (Optimización):** Herramienta que permite migrar bases de datos masivas desde archivos Excel hacia el sistema de forma segura, reduciendo drásticamente el tiempo de digitación inicial.
3.  **Módulo HSEQ (Control de Vencimientos):** Matriz inteligente que rastrea la vigencia de exámenes médicos, cursos de alturas, licencias de conducción y demás certificaciones obligatorias.
4.  **Sistema de Alertas:** Panel visual (semáforo de criticidad) que notifica de forma automática los documentos que están próximos a vencer (30, 15 y 5 días antes).

---

## ⚙️ 5. Entorno de Despliegue e Infraestructura

El software opera bajo un modelo de red privada para proteger la confidencialidad de la información de la empresa:

*   **Entorno de Red:** Despliegue exclusivo en **Red Local Interna (Intranet)**, blindando los datos de accesos externos no autorizados.
*   **Concurrencia:** Optimizado para la conexión simultánea de aproximadamente **5 estaciones de trabajo** en el área de Gestión Humana.
*   **Servidor Local:** Aloja tanto el servidor Apache (PHP) como el motor de base de datos MySQL.

---

## 🚀 6. Requisitos e Instalación Local

### Requisitos del Servidor Local
*   Servidor web (Apache 2.4 o superior).
*   PHP 8.x instalado.
*   Motor de Base de Datos MySQL 8.x o MariaDB.
*   (Opcional para desarrollo/entorno local: XAMPP, Laragon o WampServer).

### Pasos para el Despliegue en la Red de la Empresa
1.  **Clonar el proyecto** en la carpeta raíz del servidor local (ej. `C:/xampp/htdocs/vigia-masatrans`).
2.  **Importar la base de datos** utilizando el archivo `database.sql` incluido en el proyecto a través de phpMyAdmin.
3.  **Configurar las variables de entorno** en el archivo de configuración de la capa de infraestructura (conectar con la IP local del servidor, usuario y contraseña de MySQL).
4.  **Habilitar el acceso en red:** Configurar el servidor Apache para escuchar peticiones de la red local (permitir tráfico por el puerto 80/8080 en el Firewall).
5.  **Acceso desde los equipos de GH:** Las 5 computadoras de Gestión Humana ingresarán al sistema desde su navegador web apuntando a la IP estática del servidor (ej. `http://192.168.1.50/vigia-masatrans`).
