# Sistema de Inventario y Punto de Venta (POS) - Estado del Proyecto

## Tecnologías Principales
- **Backend:** Laravel 11.x (o superior)
- **Frontend:** Blade Templates, Alpine.js, Tailwind CSS
- **Paquetes Relevantes:**
  - `spatie/laravel-permission`: Para el manejo de roles y permisos (ej. admin vs cliente).
  - `owen-it/laravel-auditing`: Para la auditoría y rastreo de cambios en modelos.
  - `livewire/livewire`: Posible uso en componentes reactivos (listado en composer).
  - `intervention/image`: Para el manejo de imágenes de productos y comprobantes.
  - `laravel/breeze`: Implementado para la autenticación y scaffolding inicial.

## Estructura de la Base de Datos y Modelos
El sistema cuenta con un ecosistema completo para administrar compras, ventas e inventario:

1. **Usuarios y Accesos:** `User`, `Role`, `Permission` (vía Spatie). Redirección basada en roles implementada tras el login.
2. **Catálogo de Productos:** 
   - `Category`: Clasificación de productos.
   - `Product`: Soporta venta por unidad o por peso (`unit_type`). Incluye manejo de borrado lógico (soft-deletes) y recuperación.
   - `Image`: Galería asociada a los productos.
3. **Inventario y Almacenaje:**
   - `Inventory` e `InventoryMovement`: Control de existencias y registro de entradas/salidas.
   - `Bulk` y `BulkType`: Manejo de compras al mayor o lotes (bultos).
4. **Compras (Purchases):**
   - `Supplier`: Proveedores.
   - `Purchase` y `PurchaseDetail`: Registro de compras realizadas para reabastecer el inventario, calculando costos e ingresos en el inventario.
5. **Ventas / POS (Orders):**
   - `Client`: Clientes (ahora vinculados a un `user_id` para cuentas de clientes).
   - `Order` y `OrderDetail`: Registro de ventas desde el Punto de Venta, con soporte para productos pesables (gramos/kilos) y por unidad. Almacena la `exchange_rate` (tasa de cambio) del momento.
6. **Pagos y Finanzas:**
   - `ExchangeRate`: Tasa de cambio (Ej: USD a Moneda Local) que soporta decimales con comas en el frontend.
   - `PaymentMethod`: Métodos de pago disponibles.
   - `OrderPayment`, `AccountReceivable`, `PaymentInstallment`: Cuentas por cobrar y pagos a plazos o abonos.
   - `PaymentProof`: Comprobantes de pago (transferencias, capturas).

## Lógica y Controladores Principales
- **`ProductController`**: Administra el CRUD de productos. Tiene lógica para restaurar productos eliminados (Soft-Deletes) y limpiar lotes antiguos. Calcula el precio por kilo basado en gramos para la vista.
- **`OrderController` / POS**: Implementación de un Punto de Venta donde Alpine.js maneja el carrito en el cliente. Distingue entre cantidades unitarias y por gramos. Envía la data estructurada al controlador para procesar la venta, descontar inventario y asentar los montos.
- **`AuthController` / Roles**: Lógica para redirigir al dashboard de `Admin` o al perfil de `Cliente` dependiendo del rol del usuario autenticado.

## Retos Recientemente Resueltos
1. Diferenciación de productos por peso vs unidades en el POS y persistencia del tipo en base de datos.
2. Ajuste de formato de monedas y decimales (puntos para miles, comas para decimales) en inputs y Alpine.js.
3. Redireccionamiento dinámico post-login según el rol del usuario (Admin vs Client).
4. Recuperación automática de productos borrados lógicamente si se intenta crear uno con el mismo SKU/Código de barras.
5. Solución a errores de compilación de Vite al instalar Laravel Breeze.

## Próximos Pasos (Contexto General)
Mantener la estabilidad en los cálculos matemáticos (especialmente por la tasa de cambio y la conversión gramos a kilos), seguir mejorando la interfaz de cliente y refinar la seguridad y validaciones del backend al recibir datos del componente POS con Alpine.js.
