# Arquitectura del Sistema SaaS - Proveedor Electrónico DGII (República Dominicana)

## 1. Filosofía y Core del Sistema
Este proyecto es un sistema SaaS Multi-Tenant desarrollado en Laravel utilizando **Filament (v5)** como panel de administración. Su objetivo es fungir como Proveedor de Servicios de Facturación Electrónica (PSFE) certificado por la DGII.

El sistema utiliza el paquete `platinum-place/laravel-dgii` como motor principal. Este paquete funciona bajo una filosofía de "facilitador" (enabler) y es 100% libre de estado (stateless). Esto significa que el paquete **no guarda** credenciales, tokens, ni facturas en bases de datos o archivos de configuración. Toda la responsabilidad de persistencia, multi-tenancy (múltiples empresas), control de secuencias (e-NCF) y almacenamiento de los XML/PDF recae sobre nuestra base de datos.

El paquete se encarga exclusivamente de la complejidad técnica de la DGII:
1. Firma digital PKCS#12 de los XML.
2. Autenticación (ciclo Semilla -> Firma -> Token).
3. Comunicación HTTP nativa con los web services de la DGII.

## 2. Estructura de Base de Datos (Esquema Principal)
El sistema maneja la siguiente estructura relacional, la cual debe estar preparada para integrarse con la característica Multi-Tenancy nativa de Filament:

* **`companies`**: Almacena los tenants (empresas clientes), su RNC, ambiente (`testecf`, `certecf`, `ecf`) y las rutas/contraseñas encriptadas de sus certificados `.p12`. (Actuará como el Tenant Model de Filament).
* **`dgii_tokens`**: Almacena los Access Tokens JWT temporales de la DGII por empresa para evitar solicitar uno nuevo por cada factura.
* **`ecf_sequences`**: Controla los talonarios de comprobantes fiscales electrónicos (e-NCF) otorgados por la DGII, administrando el `start_range`, `end_range` y el `current_sequence`.
* **`contacts`**: Clientes y suplidores de las empresas, identificando si son o no receptores electrónicos.
* **`ecfs`**: Tabla principal de facturas emitidas. Almacena montos, estado de la DGII (`Aceptado`, `Rechazado`, `Aceptado Condicional`), rutas de los archivos (`signed_xml_path`, `pdf_path`) y el `track_id`.
* **`ecf_items`, `ecf_payments`, `ecf_taxes`**: Tablas de detalles de las facturas (productos, formas de pago y tributos adicionales como propina legal o ISC).
* **`received_ecfs`**: Bandeja de entrada de facturas de suplidores, controlando el envío del Acuse de Recibo (ARECF) y Aprobación Comercial (ACECF).
* **`contingencies` & `ecf_annulments`**: Registro de caídas de sistema para la DGII y anulación de rangos de secuencias e-NCF.

*(Importante: Todas las tablas relacionadas al Tenant deben tener un `company_id` y usar un Scope Global o el trait proporcionado por el paquete de Multi-tenancy utilizado junto a Filament, si aplica).*

## 3. Flujos de Trabajo (Workflows) a seguir por la IA
Al generar código para este proyecto, la IA debe respetar los siguientes flujos de trabajo utilizando el Facade `PlatinumPlace\LaravelDgii\Facades\Dgii`:

### A. Autenticación (Tokens)
Antes de enviar un e-CF, se debe verificar si la empresa tiene un token válido en `dgii_tokens`. Si no lo tiene o expiró:
1. Obtener semilla: `$seedXml = Dgii::getSeed($env);`
2. Firmar semilla y verificar: `$authInfo = Dgii::verifySeed($env, $rutaSemillaFirmada);`
3. Guardar `$authInfo['token']` y la fecha de expiración en la base de datos.

### B. Emisión de Facturas (e-CF)
1. Construir el DTO o Array con la estructura requerida por el paquete, extrayendo los datos de `ecfs`, `ecf_items`, `ecf_taxes`, etc.
2. Renderizar y firmar: `$result = Dgii::renderInvoice($certContent, $certPassword, $invoiceData);`
3. Guardar el string `$result['xml']` en el storage de Laravel (ej. AWS S3 o local) y guardar la ruta en `ecfs.signed_xml_path`.
4. Enviar a DGII: `$response = Dgii::sendInvoice($env, $accessToken, $pathAlXML);`
5. Guardar el `$response['trackId']` en la tabla `ecfs`.
   *Nota: Si es una Factura de Consumo (Tipo 32) por un monto menor a RD$250,000, se debe enviar el Resumen (RFCE) al endpoint específico de consumo.*

### C. Consulta de Estatus (Asíncrono)
Un Job en cola debe tomar los comprobantes con status "No Enviado" o "En Proceso" y consultar su estado definitivo usando:
`$status = Dgii::findInvoice($env, $accessToken, $trackId);`. Se debe actualizar `ecfs.dgii_status` con "Aceptado", "Rechazado" o "Aceptado Condicional".

### D. Generación de PDF (Representación Impresa)
El PDF debe generarse de forma independiente usando vistas Blade y una librería de PDF (DomPDF, Snappy, etc.). Debe incluir obligatoriamente un Código QR que contenga un string con formato específico y el "Código de Seguridad" (los primeros 6 caracteres del Hash de la firma digital).

### E. Recepción y Webhooks
El sistema expondrá endpoints (webhooks) sin autenticación pesada (o con token básico) para recibir comprobantes de suplidores, guardar el XML en `received_ecfs.received_xml_path` y despachar Jobs para responder con el XML de Acuse de Recibo (ARECF) y luego el de Aprobación Comercial (ACECF).

## 4. Reglas de Código para la IA
- **Filament V5 como Panel:** Toda la UI de administración de la plataforma SaaS (gestión de empresas, contactos, facturas) debe construirse utilizando los recursos (Resources), páginas (Pages) y widgets nativos de Filament v5, aprovechando la integración con **Livewire v4**.
- **Acciones Asíncronas en Filament:** Las acciones (Actions) en Filament que involucren comunicación con la DGII (ej. "Enviar Factura ahora") deben, en su mayoría, despachar Jobs en lugar de bloquear el ciclo de petición/respuesta de Livewire, proporcionando un feedback de "Proceso iniciado" al usuario mediante notificaciones de Filament.
- **DTOs Primeros:** Usar Data Transfer Objects (DTOs) para mapear los datos de las tablas de Eloquent hacia los Arrays que requiere `PlatinumPlace\LaravelDgii\Facades\Dgii`.
- **Jobs / Colas:** Toda comunicación con la DGII (envíos, consultas de track_id, respuestas de aprobaciones comerciales) debe realizarse en **Jobs** para no bloquear la respuesta HTTP del usuario final en el panel SaaS.
- **Nombres en Inglés:** Todas las tablas, columnas, modelos y variables internas usarán inglés. Los campos que van hacia la DGII respetarán las llaves exactas del XML exigidas por el paquete (Ej. `TipoeCF`, `eNCF`, `RNCComprador`).
