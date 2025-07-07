<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateStockSummaryView extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("
            CREATE VIEW stock_summary_view AS
            SELECT
                items.id AS id,
                items.id AS item_id,
                items.name AS item_name,
                item_types.name AS item_type,
                w.name AS warehouse_name,
                w.id AS warehouse_id,
                CASE WHEN COALESCE(SUM(virtual_ledger.qty), 0) < 0 THEN 0 ELSE COALESCE(SUM(virtual_ledger.qty), 0) END AS qty_virtual,
                CASE WHEN COALESCE(SUM(factual_ledger.qty), 0) < 0 THEN 0 ELSE COALESCE(SUM(factual_ledger.qty), 0) END AS qty_factual
            FROM
                items
                JOIN item_types ON items.item_type_id = item_types.id
                CROSS JOIN warehouses w
                -- Virtual Ledger Movements (Warehouse Level)
                LEFT JOIN (
                    -- Incoming to warehouse (positive)
                    SELECT 
                        item_id,
                        to_warehouse_id AS warehouse_id,
                        SUM(quantity) AS qty
                    FROM 
                        ledger_virtual
                    WHERE 
                        to_warehouse_id IS NOT NULL
                    GROUP BY 
                        item_id, to_warehouse_id
                    
                    UNION ALL
                    
                    -- Outgoing from warehouse (negative)
                    SELECT 
                        item_id,
                        from_warehouse_id AS warehouse_id,
                        SUM(-1 * quantity) AS qty
                    FROM 
                        ledger_virtual
                    WHERE 
                        from_warehouse_id IS NOT NULL
                    GROUP BY 
                        item_id, from_warehouse_id
                ) virtual_ledger ON virtual_ledger.item_id = items.id AND virtual_ledger.warehouse_id = w.id
                
                -- Factual Ledger Movements (Rack Level)
                LEFT JOIN (
                    -- Incoming to rack (positive)
                    SELECT 
                        lf.item_id,
                        r_to.warehouse_id,
                        SUM(lf.quantity) AS qty
                    FROM 
                        ledger_factual lf
                        JOIN racks r_to ON lf.to_rack_id = r_to.id
                    WHERE 
                        lf.to_rack_id IS NOT NULL
                    GROUP BY 
                        lf.item_id, r_to.warehouse_id
                        
                    UNION ALL
                    
                    -- Outgoing from rack (negative)
                    SELECT 
                        lf.item_id,
                        r_from.warehouse_id,
                        SUM(lf.quantity) AS qty
                    FROM 
                        ledger_factual lf
                        JOIN racks r_from ON lf.from_rack_id = r_from.id
                    WHERE 
                        lf.from_rack_id IS NOT NULL
                    GROUP BY 
                        lf.item_id, r_from.warehouse_id
                ) factual_ledger ON factual_ledger.item_id = items.id AND factual_ledger.warehouse_id = w.id
            
            GROUP BY items.id, items.name, item_types.name, w.name, w.id
            HAVING 
                (CASE WHEN COALESCE(SUM(virtual_ledger.qty), 0) < 0 THEN 0 ELSE COALESCE(SUM(virtual_ledger.qty), 0) END) > 0
                OR 
                (CASE WHEN COALESCE(SUM(factual_ledger.qty), 0) < 0 THEN 0 ELSE COALESCE(SUM(factual_ledger.qty), 0) END) > 0
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('DROP VIEW IF EXISTS stock_summary_view');
    }
}
