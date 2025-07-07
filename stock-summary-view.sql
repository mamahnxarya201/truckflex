CREATE VIEW stock_summary_view AS
SELECT
    items.id AS id,
    items.id AS item_id,
    items.name AS item_name,
    item_types.name AS item_type,
    w.name AS warehouse_name,
    w.id AS warehouse_id,
    COALESCE(SUM(virtual_ledger.qty), 0) AS qty_virtual,
    COALESCE(SUM(factual_ledger.qty), 0) AS qty_factual
FROM
    items
    JOIN item_types ON items.item_type_id = item_types.id
    CROSS JOIN warehouses w
    -- Virtual Ledger Movements (Warehouse Level)
    LEFT JOIN (
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
        
        SELECT 
            item_id,
            from_warehouse_id AS warehouse_id,
            SUM(quantity) AS qty
        FROM 
            ledger_virtual
        WHERE 
            from_warehouse_id IS NOT NULL
        GROUP BY 
            item_id, from_warehouse_id
    ) virtual_ledger ON virtual_ledger.item_id = items.id AND virtual_ledger.warehouse_id = w.id
    
    -- Factual Ledger Movements (Rack Level)
    LEFT JOIN (
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

GROUP BY items.id, items.name, item_types.name, w.name, w.id;