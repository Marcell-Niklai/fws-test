SELECT
	product_packages.id as 'Package ID',
    product_packages.title as 'Package Name',
    SUM(product_package_contents.quantity * price_history.price) as 'Actual Price',
    price_history.updated_at as 'Date'
FROM
	product_packages
	RIGHT JOIN product_package_contents ON product_packages.id = product_package_contents.product_package_id
    LEFT JOIN products ON product_package_contents.product_id = products.id
    RIGHT JOIN price_history ON products.id = price_history.product_id
WHERE
	product_packages.id = 11 AND price_history.updated_at <= "2024-02-21 00:00:00"
GROUP BY
    products.id
ORDER BY
	price_history.updated_at DESC
LIMIT
	0,1;
