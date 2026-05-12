<?php
require 'db.php';
$sort = $_GET['sort'] ?? 'default';
$category = $_GET['category'] ?? 'all';

$query = "SELECT id, name, description, price, image_url, average_rating FROM products";
$params = [];

if ($category !== 'all') {
    $query .= " WHERE category = ?";
    $params[] = $category;
}

switch ($sort) {
    case 'price-asc': $query .= " ORDER BY price ASC"; break;
    case 'price-desc': $query .= " ORDER BY price DESC"; break;
    case 'name': $query .= " ORDER BY name ASC"; break;
    case 'rating': $query .= " ORDER BY average_rating DESC"; break;
    default: $query .= " ORDER BY id ASC";
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$products = $stmt->fetchAll();

header('Content-Type: application/json');
echo json_encode($products);