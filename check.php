<?php
require 'db.php';

echo "<p>Connected to database: <b>" . $pdo->query("SELECT DATABASE()")->fetchColumn() . "</b></p>";
echo "<p>Tables in this database:</p><pre>";
print_r($pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN));
echo "</pre>";