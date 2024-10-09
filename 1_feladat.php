<?php
class CSVImporter {
    public $csv_file = "";
    public $xml_file = "";
    public $delimiter = ",";
    public $products = [];
    private $database;
    
    public function __construct($csv_file, $database_file, $xml_file) {
        $this->csv_file = $csv_file;
        $this->xml_file = $xml_file;
        $this->database = new PDO('sqlite:' . $database_file);
        $this->database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->database->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $this->initializeDatabase();
    }
    /**
     * Initializing the tables of the database. This function creates the
     * required tables for proper program working.
     * @return void 
     */
    private function initializeDatabase() {
        $this->database->exec("
            CREATE TABLE IF NOT EXISTS products (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                price INTEGER NOT NULL
            )
        ");
        $this->database->exec("
            CREATE TABLE IF NOT EXISTS categories (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL UNIQUE
            )
        ");
        $this->database->exec("
            CREATE TABLE IF NOT EXISTS product_categories (
                product_id INTEGER,
                category_id INTEGER,
                PRIMARY KEY (product_id, category_id),
                FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
                FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
            )
        ");
    }
    /**
     * This function process the loaded csv file, and stores the data in a database.
     * @return void 
     */
    public function processingFile() {
        $current_file = fopen($this->csv_file, 'r');
        $i = 0;
        while (($row = fgetcsv($current_file, null, $this->delimiter)) !== FALSE) {
            if ($i > 0) {
                $this->products[] = [
                    'name' => $row[0],
                    'price' => (int)$row[1],
                    'categories' => array_slice($row, 2)
                ];
            }
            $i++;
        }
        fclose($current_file);
        $this->updatingDatabase();
    }
    /**
     * This function updating the database alongs the processed data.
     * @return void
     */
    public function updatingDatabase() {
        try {
            $this->database->beginTransaction();
            foreach($this->products as $product) {
                $product_sql = $this->database->prepare("SELECT id FROM products WHERE name = :name");
                $product_sql->execute([':name' => $product['name']]);
                $current_product = $product_sql->fetch();
                if ($current_product) {
                    $current_product_id = $current_product['id'];
                    $update_sql = $this->database->prepare("UPDATE products SET price = :price WHERE id = :id");
                    $update_sql->execute([
                        ':price' => $product['price'],
                        ':id' => $current_product_id
                    ]);
                } else {
                    $insert_sql = $this->database->prepare("INSERT INTO products (name, price) VALUES (:name, :price)");
                    $insert_sql->execute([
                        ':name' => $product['name'],
                        ':price' => (int)$product['price']
                    ]);
                    $current_product_id = $this->database->lastInsertId();
                }
                foreach ($product['categories'] as $category_name) {
                    if (empty($category_name)) continue;
                    $category_sql = $this->database->prepare("SELECT id FROM categories WHERE name = :name");
                    $category_sql->execute([':name' => $category_name]);
                    $current_category = $category_sql->fetch();
                    if (!$current_category) {
                        $insert_sql = $this->database->prepare("INSERT INTO categories (name) VALUES (:name)");
                        $insert_sql->execute([':name' => $category_name]);
                        $current_category_id = $this->database->lastInsertId();
                    } else {
                        $current_category_id = $current_category['id'];
                    }
                    $assignment_sql = $this->database->prepare(
                        "INSERT OR IGNORE INTO product_categories (product_id, category_id) VALUES (:product_id, :category_id)"
                    );
                    $assignment_sql->execute([
                        ':product_id' => $current_product_id,
                        ':category_id' => $current_category_id
                    ]);
                }
            }
            $this->database->commit();
        } catch (Exception $e) {
            $this->database->rollBack();
            echo "Hiba történt: " . $e->getMessage();
        }
    }
    /**
     * This function generating xml file from the processed data.
     * @return void
     */
    public function generateXML() {
        $products_xml = new SimpleXMLElement('<products/>');
        foreach($this->products as $product) {
            $product_entity = $products_xml->addChild('product');
            $product_entity->addChild("title", $product['name']);
            $product_entity->addChild("price", $product['price']);
            if (!empty($product['categories'])) {
                $categories_entity = $product_entity->addChild('categories');
                foreach ($product['categories'] as $category) {
                    $categories_entity->addChild('category', $category);
                }
            }
        }
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML($products_xml->asXML());
        $dom->save($this->xml_file);
    }
}

$csv_file = "termekek.csv";
$database_file = "products.db";
$xml_file = "products.xml";

$CSVImport = new CSVImporter($csv_file, $database_file, $xml_file);

$CSVImport->processingFile();
$CSVImport->generateXML();
