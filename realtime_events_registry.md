# Registro de Eventos en Tiempo Real (Real-time Events Registry)

Este documento registra los eventos del sistema y sus escuchas reactivas para mantener sincronizados los paneles de control y vistas administrativas sin requerir recargas de página.

---

## 1. Eventos de Compras (Purchases)

### `purchase-created`
* **Descripción**: Se dispara inmediatamente después de procesar y guardar con éxito una nueva compra a un proveedor.
* **Emisor**: `PurchaseController` (en `store`) o componentes de creación de compras.
* **Escuchas Activos**:
  * `App\Livewire\InventoryIndex`:
    * Acción: Actualiza estadísticas globales (`totalStock`, `totalLowStock`, `totalOutOfStock`) e incrementa cantidades de la lista.
    * Método: `refreshInventoryStats()`
  * `App\Livewire\PurchaseIndex`:
    * Acción: Actualiza los resúmenes financieros históricos (Total facturas, Inversión Bs y USD) y recarga la tabla de auditoría.
    * Método: `refreshPurchases()`

### `purchase-cancelled`
* **Descripción**: Se dispara al anular o revertir una compra (devolución al proveedor).
* **Emisor**: Controlador de anulación de compras.
* **Escuchas Activos**:
  * `App\Livewire\InventoryIndex`:
    * Acción: Disminuye los artículos devueltos y refresca las alertas.
    * Método: `refreshInventoryStats()`
  * `App\Livewire\PurchaseIndex`:
    * Acción: Resta los montos financieros del total histórico y marca el registro como anulado.
    * Método: `refreshPurchases()`

---

## 2. Eventos de Ventas / Facturación (Sales / Invoices)

### `sale-created`
* **Descripción**: Se dispara cuando una venta es concretada y el stock reservado/disponible de los productos se ve afectado.
* **Emisor**: Controlador de ventas/caja.
* **Escuchas Activos**:
  * `App\Livewire\InventoryIndex`:
    * Acción: Reduce el stock físico/disponible y recalcula alertas críticas de stock mínimo y agotados.
    * Método: `refreshInventoryStats()`

### `sale-cancelled` / `sale-rejected`
* **Descripción**: Se dispara cuando una venta es anulada, devuelta o rechazada por el cliente, reintegrando los productos al stock disponible.
* **Emisor**: Controlador de devoluciones o rechazos de ventas.
* **Escuchas Activos**:
  * `App\Livewire\InventoryIndex`:
    * Acción: Reintegra las unidades devueltas al inventario físico y recalcula alertas.
    * Método: `refreshInventoryStats()`

---

## 3. Eventos Generales de Inventario (General Inventory)

### `inventory-updated`
* **Descripción**: Se dispara cuando ocurre un ajuste manual de stock (entrada por merma, corrección manual, conteo físico).
* **Emisor**: `InventoryIndex` (Modal de Ajuste Manual).
* **Escuchas Activos**:
  * `App\Livewire\InventoryIndex`:
    * Acción: Recalcula en tiempo real los contadores de alertas y actualiza la lista.
    * Método: `refreshInventoryStats()`
