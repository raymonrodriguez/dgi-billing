# Guía de Estructura de Archivos XML (Plantillas Blade)

Este documento detalla la organización y el propósito de las plantillas Blade utilizadas para generar los documentos XML requeridos por la DGII de la República Dominicana. Estas plantillas se encuentran en `resources/views/xml/`.

---

## 1. Emisión de Facturas (Carpeta `invoices/`)
Contiene las estructuras para la emisión de facturas individuales. Cada archivo corresponde a un **Tipo de e-CF** oficial.

| Archivo | Tipo e-CF | Descripción |
| :--- | :---: | :--- |
| `ecf_31.blade.php` | 31 | **Crédito Fiscal Electrónico:** Ventas B2B con valor fiscal. |
| `ecf_32.blade.php` | 32 | **Consumo Electrónica:** Ventas B2C a consumidores finales. |
| `ecf_33.blade.php` | 33 | **Nota de Débito Electrónica:** Incremento de valor de e-CF previo. |
| `ecf_34.blade.php` | 34 | **Nota de Crédito Electrónica:** Descuentos, devoluciones o anulaciones. |
| `ecf_41.blade.php` | 41 | **Compras Electrónico:** Registro de compras a proveedores informales. |
| `ecf_43.blade.php` | 43 | **Gastos Menores Electrónico:** Pagos de baja cuantía. |
| `ecf_44.blade.php` | 44 | **Regímenes Especiales:** Ventas a entidades bajo leyes especiales. |
| `ecf_45.blade.php` | 45 | **Gubernamental Electrónico:** Ventas al Estado Dominicano. |
| `ecf_46.blade.php` | 46 | **Exportaciones Electrónico:** Ventas de bienes fuera del territorio nacional. |
| `ecf_47.blade.php` | 47 | **Pagos al Exterior:** Pagos por servicios fuera de RD. |

---

## 2. Resúmenes de Consumo (Carpeta `summaries/`)
*   **`xml.blade.php`**: Implementa el **RFCE**. 
    *   *Regla DGII:* Obligatorio para facturas de Tipo 32 con montos menores a **RD$250,000**.

---

## 3. Mensajería de Intercambio (Recepción)
Utilizadas para la comunicación entre contribuyentes (Suplidor <-> Receptor).

| Carpeta | Archivo | Propósito |
| :--- | :--- | :--- |
| `acknowledgments/` | `xml.blade.php` | **Acuse de Recibo (ARECF):** Confirmación técnica de recepción del XML. |
| `approvals/` | `xml.blade.php` | **Aprobación Comercial (ACECF):** Aceptación o rechazo comercial de la factura. |

---

## 4. Gestión de Secuencias (Carpeta `voiding/`)
*   **`xml.blade.php`**: Genera la estructura de **Anulación de Rangos de e-NCF (ANECF)**.

---

## 5. Ciclo de Autenticación (Carpeta `auth/`)
*   **`xml.blade.php`**: Estructura utilizada para el envío de la **Semilla Firmada**.

---

## Notas Técnicas
- **Ruta Base**: `resources/views/xml/`
- **Extensiones**: Todos los archivos deben mantener la extensión `.blade.php`.
- **Inyección de Datos**: La lógica de mapeo se centraliza en el `EcfTransformer` o los servicios en `app/Services/DGII/`.
- **Estandarización**: Los nombres de etiquetas XML deben respetar CamelCase exacto según el esquema XSD de la DGII.
