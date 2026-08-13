<?php
// MEMBER CATALOGUE: retrieves categories and books from MySQL instead of hard-coding them.
declare(strict_types=1);
require __DIR__ . '/member_auth.php';
requireMember();
require __DIR__ . '/db.php';
require __DIR__ . '/member_nav.php';

// MYSQLI JOIN: combine each book with its category for display.
$categories = $mysqli->query(
    'SELECT c.id, c.name, b.id AS book_id, b.title, b.author, b.format, b.status
     FROM book_categories c
     LEFT JOIN books b ON b.category_id = c.id
     ORDER BY c.name, b.title'
);
$catalogue = [];
while ($row = $categories->fetch_assoc()) {
    $categoryId = (int) $row['id'];
    $catalogue[$categoryId] ??= ['name' => $row['name'], 'books' => []];
    if ($row['book_id'] !== null) $catalogue[$categoryId]['books'][] = $row;
}

// Convert database status values into presentation-friendly labels.
$statusLabels = [
    'available' => 'Available',
    'loaned' => 'Loaned',
    'coming_soon' => 'Coming Soon',
    'unavailable' => 'Unavailable',
];
function e(string $value): string { return htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Browse books available at Moonlight Library.">
  <link rel="stylesheet" href="csslab.css">
  <script src="script.js" defer></script>
  <title>Moonlight Library | Book Catalogue</title>
</head>
<body>
<header id="top"><h1>📚 Book Catalogue</h1><nav aria-label="Main navigation"><a href="index.php">Home</a><a href="catalogue.php" aria-current="page">Book Catalogue</a><a href="events.php">Events</a><a href="gallery.php">Gallery</a><a href="contact.php">Contact Us</a><a href="member_logout.php">Log Out</a><?= memberProfileNavigation($mysqli) ?></nav></header>
<main>
  <!-- JAVASCRIPT SEARCH: script.js filters all rows marked with the book-row class. -->
  <section><h2>Find your next book</h2><form action="catalogue.php" method="get" id="catalogue-search"><p><label for="search">Search by title, author, category, format, or status:</label><br><input type="search" id="search" name="q" placeholder="e.g. Things Fall Apart"><button type="submit">Search</button> <button type="button" class="secondary-button" id="clear-search">Show all</button></p></form><p id="search-status" class="status-message" aria-live="polite">Browse all books or search the catalogue.</p></section>
  <section><h2>Browse by category</h2><?php if (!$catalogue): ?><p>No categories have been added.</p><?php else: ?><ol><?php foreach ($catalogue as $categoryId => $category): ?><li><a href="#category-<?= $categoryId ?>"><?= e($category['name']) ?></a></li><?php endforeach; ?></ol><?php endif; ?></section>
  <!-- DATABASE OUTPUT: PHP creates one accessible table for every category. -->
  <?php foreach ($catalogue as $categoryId => $category): ?>
  <section id="category-<?= $categoryId ?>" class="full-width catalogue-category">
    <h2><?= e($category['name']) ?></h2>
    <?php if (!$category['books']): ?><p>No books in this category yet.</p><?php else: ?>
    <div class="table-wrapper"><table><thead><tr><th scope="col">Title</th><th scope="col">Author</th><th scope="col">Format</th><th scope="col">Status</th></tr></thead><tbody>
    <?php foreach ($category['books'] as $book): ?><tr class="book-row"><td><?= e($book['title']) ?></td><td><?= e($book['author']) ?></td><td><?= e($book['format']) ?></td><td><span class="status-badge <?= e(str_replace('_', '-', $book['status'])) ?>"><?= e($statusLabels[$book['status']] ?? ucfirst($book['status'])) ?></span></td></tr><?php endforeach; ?>
    </tbody></table></div><?php endif; ?>
  </section>
  <?php endforeach; ?>
</main>
<footer><p><small>&copy; 2026 Moonlight Library</small></p><p><a href="#top">Back to top ↑</a></p></footer>
</body></html>
