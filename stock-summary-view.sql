  CREATE VIEW stock_summary_view AS
SELECT
		items.id AS id,
                items.id AS item_id,
                items.name AS item_name,
                item_types.name AS item_type,
                COALESCE(vw.name, fw.name) AS warehouse_name,
                COALESCE(vw.id, fw.id) AS warehouse_id,
                SUM(lv.quantity) AS qty_virtual,
                SUM(lf.quantity) AS qty_factual
            FROM items
            JOIN item_types ON items.item_type_id = item_types.id

            LEFT JOIN ledger_virtual lv ON lv.item_id = items.id
            LEFT JOIN warehouses vw ON lv.to_warehouse_id = vw.id

            LEFT JOIN ledger_factual lf ON lf.item_id = items.id
            LEFT JOIN racks r ON lf.to_rack_id = r.id
            LEFT JOIN warehouses fw ON r.warehouse_id = fw.id

            GROUP BY items.id, items.name, item_types.name, warehouse_name, warehouse_id;