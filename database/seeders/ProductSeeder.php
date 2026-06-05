<?php

namespace Database\Seeders;

use App\Models\Bulk;
use App\Models\BulkType;
use App\Models\Category;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Safe truncate existing tables to avoid duplicate key violations
        Schema::disableForeignKeyConstraints();
        Product::truncate();
        Inventory::truncate();
        Bulk::truncate();
        Schema::enableForeignKeyConstraints();

        // Fetch all categories
        $categories = Category::all();
        if ($categories->isEmpty()) {
            $this->call(CategorySeeder::class);
            $categories = Category::all();
        }

        // Fetch all bulk types
        $bulkTypes = BulkType::all();
        if ($bulkTypes->isEmpty()) {
            $this->call(BulkTypeSeeder::class);
            $bulkTypes = BulkType::all();
        }

        // Map bulk types by slug for easy retrieval
        $bulkTypesMap = $bulkTypes->pluck('id', 'slug')->toArray();

        // Get a default admin user to assign as creator
        $creator = User::first() ?? User::create([
            'dni' => '29873955',
            'name' => 'Luis',
            'last_name' => 'Vasquez',
            'phone_number' => '04145018145',
            'email' => 'wueyluis@gmail.com',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);

        // A rich bank of realistic products grouped by category slug
        $productBank = [
            'bebidas' => [
                ['name' => 'Refresco Coca-Cola 1.5L', 'brand' => 'Coca-Cola', 'unit_type' => 'unit', 'cost' => 1.20 * 540, 'price' => 1.80 * 540],
                ['name' => 'Refresco Pepsi 2L', 'brand' => 'Pepsi', 'unit_type' => 'unit', 'cost' => 1.40 * 540, 'price' => 2.10 * 540],
                ['name' => 'Agua Mineral Minalba 5L', 'brand' => 'Minalba', 'unit_type' => 'unit', 'cost' => 2.00 * 540, 'price' => 3.00 * 540],
                ['name' => 'Jugo de Naranja Yukery 1L', 'brand' => 'Yukery', 'unit_type' => 'unit', 'cost' => 1.50 * 540, 'price' => 2.25 * 540],
                ['name' => 'Té Frío Lipton Limón 500ml', 'brand' => 'Lipton', 'unit_type' => 'unit', 'cost' => 0.80 * 540, 'price' => 1.20 * 540],
                ['name' => 'Malta Polar 355ml', 'brand' => 'Polar', 'unit_type' => 'unit', 'cost' => 0.60 * 540, 'price' => 0.95 * 540],
                ['name' => 'Jugo de Manzana Yukery 250ml', 'brand' => 'Yukery', 'unit_type' => 'unit', 'cost' => 0.45 * 540, 'price' => 0.70 * 540],
                ['name' => 'Soda Schweppes Tónica 355ml', 'brand' => 'Schweppes', 'unit_type' => 'unit', 'cost' => 0.90 * 540, 'price' => 1.40 * 540],
            ],
            'alimentos' => [
                ['name' => 'Harina de Maíz Precocida Harina P.A.N. 1kg', 'brand' => 'P.A.N.', 'unit_type' => 'unit', 'cost' => 0.95 * 540, 'price' => 1.35 * 540],
                ['name' => 'Arroz Blanco Primor Clasico 1kg', 'brand' => 'Primor', 'unit_type' => 'unit', 'cost' => 1.10 * 540, 'price' => 1.60 * 540],
                ['name' => 'Pasta Spaguetti Primor 1kg', 'brand' => 'Primor', 'unit_type' => 'unit', 'cost' => 1.20 * 540, 'price' => 1.75 * 540],
                ['name' => 'Aceite Vegetal Vatel 1L', 'brand' => 'Vatel', 'unit_type' => 'unit', 'cost' => 2.20 * 540, 'price' => 3.20 * 540],
                ['name' => 'Azúcar Refinada Montalbán 1kg', 'brand' => 'Montalbán', 'unit_type' => 'unit', 'cost' => 0.90 * 540, 'price' => 1.30 * 540],
                ['name' => 'Sal Común de Mesa Refinada 500g', 'brand' => 'La Fina', 'unit_type' => 'unit', 'cost' => 0.40 * 540, 'price' => 0.60 * 540],
                ['name' => 'Salsa de Tomate Pampero 397g', 'brand' => 'Pampero', 'unit_type' => 'unit', 'cost' => 0.85 * 540, 'price' => 1.25 * 540],
                ['name' => 'Mayonesa Kraft Real 445g', 'brand' => 'Kraft', 'unit_type' => 'unit', 'cost' => 2.10 * 540, 'price' => 2.99 * 540],
                ['name' => 'Lentejas Importadas Seleccionadas 500g', 'brand' => 'Granos del Norte', 'unit_type' => 'unit', 'cost' => 0.75 * 540, 'price' => 1.15 * 540],
                ['name' => 'Caraotas Negras Seleccionadas 500g', 'brand' => 'Granos del Norte', 'unit_type' => 'unit', 'cost' => 0.80 * 540, 'price' => 1.20 * 540],
            ],
            'limpieza' => [
                ['name' => 'Jabón en Polvo Las Llaves 1kg', 'brand' => 'Las Llaves', 'unit_type' => 'unit', 'cost' => 2.40 * 540, 'price' => 3.50 * 540],
                ['name' => 'Jabón Líquido Lavaplatos Axion 500ml', 'brand' => 'Axion', 'unit_type' => 'unit', 'cost' => 1.50 * 540, 'price' => 2.20 * 540],
                ['name' => 'Cloro Líquido Tradicional Nevex 1L', 'brand' => 'Nevex', 'unit_type' => 'unit', 'cost' => 0.90 * 540, 'price' => 1.40 * 540],
                ['name' => 'Desinfectante Floral Poett 1L', 'brand' => 'Poett', 'unit_type' => 'unit', 'cost' => 1.30 * 540, 'price' => 1.95 * 540],
                ['name' => 'Esponja Multiuso Scotch-Brite 2 Und', 'brand' => '3M', 'unit_type' => 'unit', 'cost' => 0.70 * 540, 'price' => 1.10 * 540],
                ['name' => 'Suavizante Downy Concentrado 800ml', 'brand' => 'Downy', 'unit_type' => 'unit', 'cost' => 3.50 * 540, 'price' => 4.99 * 540],
                ['name' => 'Detergente Líquido Ariel Concentrado 1L', 'brand' => 'Ariel', 'unit_type' => 'unit', 'cost' => 4.20 * 540, 'price' => 5.99 * 540],
                ['name' => 'Limpiador Multiuso Cif Crema 500ml', 'brand' => 'Cif', 'unit_type' => 'unit', 'cost' => 1.80 * 540, 'price' => 2.60 * 540],
            ],
            'lacteos' => [
                ['name' => 'Leche Líquida Completa Campestre 1L', 'brand' => 'Campestre', 'unit_type' => 'unit', 'cost' => 1.30 * 540, 'price' => 1.85 * 540],
                ['name' => 'Leche en Polvo Completa La Campiña 900g', 'brand' => 'La Campiña', 'unit_type' => 'unit', 'cost' => 6.50 * 540, 'price' => 8.99 * 540],
                ['name' => 'Mantequilla con Sal Mavesa 500g', 'brand' => 'Mavesa', 'unit_type' => 'unit', 'cost' => 1.80 * 540, 'price' => 2.50 * 540],
                ['name' => 'Queso Blanco Duro Llanero (por g)', 'brand' => 'Lácteos Llaneros', 'unit_type' => 'gram', 'cost' => 0.0035 * 540, 'price' => 0.0055 * 540], // Gram scale pricing ($3.50 cost, $5.50 retail per Kg)
                ['name' => 'Queso Amarillo Rebanado Torondoy (por g)', 'brand' => 'Torondoy', 'unit_type' => 'gram', 'cost' => 0.0060 * 540, 'price' => 0.0090 * 540], // Gram scale pricing ($6.00 cost, $9.00 retail per Kg)
                ['name' => 'Yogurt Natural Mi Vaca 150g', 'brand' => 'Mi Vaca', 'unit_type' => 'unit', 'cost' => 0.70 * 540, 'price' => 1.05 * 540],
                ['name' => 'Crema de Leche Campestre 200g', 'brand' => 'Campestre', 'unit_type' => 'unit', 'cost' => 1.10 * 540, 'price' => 1.65 * 540],
                ['name' => 'Queso Crema Philadelphia 220g', 'brand' => 'Philadelphia', 'unit_type' => 'unit', 'cost' => 2.80 * 540, 'price' => 3.99 * 540],
            ],
            'snacks' => [
                ['name' => 'Papas Fritas Ruffles Originales 120g', 'brand' => 'Frito-Lay', 'unit_type' => 'unit', 'cost' => 1.10 * 540, 'price' => 1.65 * 540],
                ['name' => 'Doritos Queso Mega 150g', 'brand' => 'Frito-Lay', 'unit_type' => 'unit', 'cost' => 1.20 * 540, 'price' => 1.80 * 540],
                ['name' => 'Galletas Oreo Chocolate 108g', 'brand' => 'Nabisco', 'unit_type' => 'unit', 'cost' => 0.65 * 540, 'price' => 0.99 * 540],
                ['name' => 'Chocolate con Leche Savoy 80g', 'brand' => 'Nestlé', 'unit_type' => 'unit', 'cost' => 0.90 * 540, 'price' => 1.40 * 540],
                ['name' => 'Galletas Club Social Original 9 Und', 'brand' => 'Nabisco', 'unit_type' => 'unit', 'cost' => 1.60 * 540, 'price' => 2.30 * 540],
                ['name' => 'Pepitonas Salsa Picante Margarita 140g', 'brand' => 'Margarita', 'unit_type' => 'unit', 'cost' => 1.30 * 540, 'price' => 1.95 * 540],
                ['name' => 'Maní Salado Jack\'s 100g', 'brand' => 'Jack\'s', 'unit_type' => 'unit', 'cost' => 0.50 * 540, 'price' => 0.80 * 540],
                ['name' => 'Chis Tris Queso 120g', 'brand' => 'Frito-Lay', 'unit_type' => 'unit', 'cost' => 0.75 * 540, 'price' => 1.15 * 540],
            ],
            'otros' => [
                ['name' => 'Papel Higiénico Scott 4 Rollos', 'brand' => 'Scott', 'unit_type' => 'unit', 'cost' => 1.40 * 540, 'price' => 2.00 * 540],
                ['name' => 'Crema Dental Colgate Triple Acción 75ml', 'brand' => 'Colgate', 'unit_type' => 'unit', 'cost' => 1.10 * 540, 'price' => 1.60 * 540],
                ['name' => 'Jabón de Tocador Lux Suave 125g', 'brand' => 'Lux', 'unit_type' => 'unit', 'cost' => 0.60 * 540, 'price' => 0.90 * 540],
                ['name' => 'Champú Head & Shoulders Renovadora 375ml', 'brand' => 'P&G', 'unit_type' => 'unit', 'cost' => 3.20 * 540, 'price' => 4.50 * 540],
                ['name' => 'Toallas Húmedas Huggies Triple Cuidado 80', 'brand' => 'Huggies', 'unit_type' => 'unit', 'cost' => 2.10 * 540, 'price' => 2.99 * 540],
                ['name' => 'Desodorante Speed Stick Active Fresh 50g', 'brand' => 'Colgate', 'unit_type' => 'unit', 'cost' => 1.90 * 540, 'price' => 2.70 * 540],
                ['name' => 'Máquina de Afeitar Prestobarba 3 (2 Und)', 'brand' => 'Gillette', 'unit_type' => 'unit', 'cost' => 1.50 * 540, 'price' => 2.20 * 540],
                ['name' => 'Servilletas de Papel Familia 100 Und', 'brand' => 'Familia', 'unit_type' => 'unit', 'cost' => 0.80 * 540, 'price' => 1.20 * 540],
            ],
        ];

        $count = 0;
        $totalRequested = 50;

        // Loop to create exactly 50 products rotating categories
        while ($count < $totalRequested) {
            foreach ($categories as $category) {
                if ($count >= $totalRequested) {
                    break;
                }

                $slug = Str::slug($category->name);
                $bank = $productBank[$slug] ?? $productBank['otros'];

                // Get a product item from the bank, rotating using count
                $itemIndex = $count % count($bank);
                $template = $bank[$itemIndex];

                // Append a unique code suffix to avoid duplicate names and slugs
                $suffix = ' #'.($count + 1);
                $name = $template['name'].$suffix;
                $pSlug = Str::slug($name);

                // Generate unique barcodes and SKUs
                $sku = 'SKU-'.str_pad($count + 1, 5, '0', STR_PAD_LEFT);
                $barcode = '7591000'.str_pad($count + 1, 5, '0', STR_PAD_LEFT);

                // Create the product
                $product = Product::create([
                    'category_id' => $category->id,
                    'uuid' => (string) Str::uuid(),
                    'name' => $name,
                    'slug' => $pSlug,
                    'description' => "Seeder automático para {$name}. Excelente producto de calidad garantizada.",
                    'sku' => $sku,
                    'sku_barcode' => $barcode,
                    'brand' => $template['brand'],
                    'cost' => $template['cost'],
                    'price' => $template['price'],
                    'unit_type' => $template['unit_type'],
                    'track_inventory' => true,
                    'allow_negative_stock' => false,
                    'has_variants' => false,
                    'status' => 'active',
                    'created_by' => $creator->id,
                ]);

                // Determine stock bounds (measured in grams if unit_type is 'gram')
                $initialStock = $template['unit_type'] === 'gram'
                    ? rand(10000, 80000) // 10kg to 80kg
                    : rand(15, 120);     // 15 units to 120 units

                $minStock = $template['unit_type'] === 'gram'
                    ? 5000               // 5kg min stock
                    : rand(5, 10);       // 5 to 10 units min stock

                $maxStock = $template['unit_type'] === 'gram'
                    ? 100000             // 100kg max stock
                    : rand(150, 200);    // 150 to 200 units max stock

                // Create associated inventory entry
                Inventory::create([
                    'product_id' => $product->id,
                    'stock' => $initialStock,
                    'reserved_stock' => 0,
                    'minimum_stock' => $minStock,
                    'maximum_stock' => $maxStock,
                ]);

                // Seed BULK packaging/presentations (Prestaciones) based on product unit_type
                if ($template['unit_type'] === 'gram') {
                    // Weighable / gram products get Kilo (default) and Gramo presentations
                    if (isset($bulkTypesMap['kilo'])) {
                        Bulk::create([
                            'product_id' => $product->id,
                            'bulk_type_id' => $bulkTypesMap['kilo'],
                            'name' => 'Kilo',
                            'description' => 'Venta al por kilo (1000 gramos)',
                            'quantity' => 1000.00,
                            'purchase_price' => $template['cost'] * 1000,
                            'sale_price' => $template['price'] * 1000,
                            'sku' => $product->sku.'-KG',
                            'sku_barcode' => $product->sku_barcode.'1',
                            'is_default' => true,
                            'is_active' => true,
                        ]);
                    }

                    if (isset($bulkTypesMap['gramo'])) {
                        Bulk::create([
                            'product_id' => $product->id,
                            'bulk_type_id' => $bulkTypesMap['gramo'],
                            'name' => 'Gramo',
                            'description' => 'Venta al detalle por gramo individual',
                            'quantity' => 1.00,
                            'purchase_price' => $template['cost'],
                            'sale_price' => $template['price'],
                            'sku' => $product->sku.'-GR',
                            'sku_barcode' => $product->sku_barcode.'2',
                            'is_default' => false,
                            'is_active' => true,
                        ]);
                    }
                } else {
                    // Standard Unit products get Unidad (default), Caja, and Bulto presentations
                    if (isset($bulkTypesMap['unidad'])) {
                        Bulk::create([
                            'product_id' => $product->id,
                            'bulk_type_id' => $bulkTypesMap['unidad'],
                            'name' => 'Unidad',
                            'description' => 'Venta por unidad individual al detal',
                            'quantity' => 1.00,
                            'purchase_price' => $template['cost'],
                            'sale_price' => $template['price'],
                            'sku' => $product->sku.'-UND',
                            'sku_barcode' => $product->sku_barcode.'1',
                            'is_default' => true,
                            'is_active' => true,
                        ]);
                    }

                    if (isset($bulkTypesMap['caja'])) {
                        Bulk::create([
                            'product_id' => $product->id,
                            'bulk_type_id' => $bulkTypesMap['caja'],
                            'name' => 'Caja (12 Unidades)',
                            'description' => 'Caja sellada conteniendo 12 unidades (5% desc.)',
                            'quantity' => 12.00,
                            // 5% discount for box purchase
                            'purchase_price' => round($template['cost'] * 12 * 0.95, 2),
                            'sale_price' => round($template['price'] * 12 * 0.95, 2),
                            'sku' => $product->sku.'-CJ',
                            'sku_barcode' => $product->sku_barcode.'2',
                            'is_default' => false,
                            'is_active' => true,
                        ]);
                    }

                    if (isset($bulkTypesMap['bulto'])) {
                        Bulk::create([
                            'product_id' => $product->id,
                            'bulk_type_id' => $bulkTypesMap['bulto'],
                            'name' => 'Bulto (24 Unidades)',
                            'description' => 'Bulto distribuidor conteniendo 24 unidades (10% desc.)',
                            'quantity' => 24.00,
                            // 10% discount for bulk purchase
                            'purchase_price' => round($template['cost'] * 24 * 0.90, 2),
                            'sale_price' => round($template['price'] * 24 * 0.90, 2),
                            'sku' => $product->sku.'-BLT',
                            'sku_barcode' => $product->sku_barcode.'3',
                            'is_default' => false,
                            'is_active' => true,
                        ]);
                    }
                }

                $count++;
            }
        }
    }
}
