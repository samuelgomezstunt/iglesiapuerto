# Despliegue de Base de Datos — Iglesia Puerto del Rosario

Este directorio contiene las herramientas y volcados necesarios para desplegar los cambios de base de datos en el servidor de producción o pruebas.

---

## Opciones de Despliegue Disponibles

### Opción 1: Aplicar únicamente los cambios del Sistema de Eventos (Recomendada para servidores ya existentes)
Si tu servidor de producción ya tiene WordPress instalado con la base de datos previa y solo quieres aplicar los nuevos eventos, metadatos y la integración en la portada:

**Archivo:** `database/cambios_eventos.sql` (700 KB)

**Instrucciones con MySQL CLI:**
```bash
mysql -u <usuario> -p <nombre_bd> < database/cambios_eventos.sql
```
O impórtalo directamente desde **phpMyAdmin** o **Adminer**.

---

### Opción 2: Despliegue Dinámico mediante Script PHP / WP-CLI
Si los IDs de posts en el servidor de destino pudieran diferir o si la instalación final es un WordPress individual (no multisite), este script utiliza la API interna de WordPress (`wp_insert_post`, `update_post_meta`) para crear o actualizar los registros sin riesgo de colisión de IDs.

**Archivo:** `database/deploy_eventos.php`

**Ejecución con WP-CLI:**
```bash
wp eval-file database/deploy_eventos.php
```
**O mediante PHP:**
```bash
php database/deploy_eventos.php
```

---

### Opción 3: Restauración Completa de la Base de Datos (Servidor nuevo desde cero)
Si estás desplegando la web desde cero en un nuevo servidor y necesitas toda la base de datos completa de WordPress Multisite:

**Archivo:** `database/iglesia_multisite_completa.sql.gz` (~40 MB)

**Descompresión e importación:**
```bash
# En Linux / macOS
gzip -dc database/iglesia_multisite_completa.sql.gz | mysql -u <usuario> -p <nombre_bd>

# En Windows (PowerShell con gzip o 7-Zip)
gzip -dc database/iglesia_multisite_completa.sql.gz | mysql -u root -p <nombre_bd>
```

---

## Ajuste de Dominio en Servidor Real (Search & Replace)
En el entorno local el dominio utilizado es `iglesia.test`. Tras restaurar en el servidor real, actualiza las URLs con WP-CLI para que apunten a tu dominio definitivo:

```bash
# En WordPress Multisite:
wp search-replace 'https://iglesia.test' 'https://tudominio.com' --all-tables --network

# En WordPress individual (si solo se usó un sitio):
wp search-replace 'https://iglesia.test' 'https://tudominio.com' --all-tables
```
