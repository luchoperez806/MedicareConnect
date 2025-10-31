# MediCareConnect

**MediCareConnect** es una plataforma integral de **telemedicina y gestión clínica digital**, desarrollada por **Eduardo Luis Pérez**, que conecta a **pacientes, médicos y administradores** dentro de un entorno moderno, seguro y eficiente.

El sistema permite gestionar turnos médicos, realizar consultas por videollamada, cargar y visualizar estudios clínicos, generar **historias clínicas digitales verificables mediante código QR**, y mantener comunicación directa entre paciente y profesional de la salud.

---

## Funcionalidades principales

-  **Autenticación segura por roles**: pacientes, médicos y administradores.  
-  **Gestión de turnos médicos** con calendario dinámico y confirmación en tiempo real.  
-  **Videollamadas integradas** activadas automáticamente al confirmar un turno.  
-  **Chat en tiempo real** entre médico y paciente.  
-  **Carga y visualización de estudios clínicos** por parte del paciente, con acceso para el médico tratante.  
-  **Generación automática de historias clínicas digitales en PDF con código QR verificable.**  
-  **Sistema de notificaciones** con actualizaciones instantáneas.  
-  **Panel médico avanzado**, con vista general de pacientes, estudios y teleconsultas activas.  
-  **Diseño responsive**: totalmente adaptable a celulares, tablets y PC.  
-  **Protección de datos y flujo autenticado**, implementado con PHP + PDO y sesiones seguras.  

---

## Tecnologías utilizadas

| Componente | Descripción |
|-------------|--------------|
| **PHP 8 + PDO** | Lógica del lado del servidor y conexión segura a base de datos |
| **MySQL / MariaDB** | Base de datos relacional |
| **JavaScript (AJAX + Fetch)** | Interactividad dinámica y actualización en tiempo real |
| **Bootstrap 5 + CSS3** | Interfaz moderna y adaptable |
| **FPDF + phpqrcode** | Generación de PDF con verificación QR |
| **XAMPP / Hosting Linux** | Entorno de desarrollo y producción |
| **Git + GitHub** | Control de versiones y despliegue |
| **Font Awesome + Google Fonts** | Iconografía y tipografía profesional |

---

## 📁 Estructura del proyecto

/doctor/ -> Panel médico y gestión de pacientes
/patient/ -> Panel de paciente y carga de estudios
/includes/ -> Conexión a base de datos, funciones y librerías
/uploads/ -> Almacenamiento de imágenes y documentos
/verificar.php -> Verificación pública del código QR
/video_call.php -> Módulo de teleconsulta (videollamada)


---

## Enlaces de acceso

- **Sitio oficial:** [https://medicareconnect.site](https://medicareconnect.site)  
- **Repositorio GitHub:** [https://github.com/luchoperez806/MedicareConnect](https://github.com/luchoperez806/MedicareConnect)  
- **Video comercial:** *(Disponible en hosting externo por límite de tamaño)*  

---

## Autor

**Eduardo Luis Pérez**  
Desarrollador Full Stack – Proyecto *MediCareConnect*  
 **luchoperez806@gmail.com**

---

## Nota técnica

> Por limitaciones de GitHub (100 MB por archivo), el video comercial original `video/CONEXION-COMERCIAL.mp4` se encuentra alojado externamente en el hosting oficial del proyecto.

---

##  Estado actual del proyecto

 Plataforma funcional, estable y en producción.  
 Próximas etapas: optimización de rendimiento, integración de facturación médica y app Android con WebView.

---

## Agradecimientos

A todos los profesionales médicos, docentes y colaboradores que contribuyeron a la creación de una herramienta enfocada en mejorar la atención digital y la comunicación médico–paciente.


