<?php
// ADMIN BOOKS: add newly acquired books and update the status of existing catalogue books.
declare(strict_types=1);
require __DIR__ . '/admin_auth.php';
requireAdmin();
require __DIR__ . '/db.php';

$errors = [];
$statuses = ['available', 'loaned', 'coming_soon', 'unavailable'];
$statusLabels = ['available' => 'Available', 'loaned' => 'Loaned', 'coming_soon' => 'Coming Soon', 'unavailable' => 'Unavailable'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // The hidden operation field selects either the add-book or update-status workflow.
    $token = (string) ($_POST['csrf_token'] ?? '');
    $operation = (string) ($_POST['operation'] ?? 'add');
    if (!hash_equals(adminCsrfToken(), $token)) $errors[] = 'Your session expired. Please try again.';

    if ($operation === 'update_status') {
        // STATUS UPDATE: only the four approved status values are accepted.
        $bookId = filter_input(INPUT_POST, 'book_id', FILTER_VALIDATE_INT);
        $status = (string) ($_POST['status'] ?? '');
        if (!$bookId) $errors[] = 'Choose a valid book.';
        if (!in_array($status, $statuses, true)) $errors[] = 'Choose a valid book status.';
        if (!$errors) {
            $statement = $mysqli->prepare('UPDATE books SET status = ? WHERE id = ?');
            $statement->bind_param('si', $status, $bookId);
            $statement->execute();
            header('Location: admin_books.php?updated=1');
            exit;
        }
    } elseif ($operation === 'add') {
    // NEW BOOK: collect and validate the book, category, format, and initial status.
    $title = trim((string) ($_POST['title'] ?? ''));
    $author = trim((string) ($_POST['author'] ?? ''));
    $format = trim((string) ($_POST['format'] ?? ''));
    $status = (string) ($_POST['status'] ?? '');
    $categoryName = trim((string) ($_POST['category'] ?? ''));

    if ($title === '' || mb_strlen($title) > 180) $errors[] = 'Enter a title of up to 180 characters.';
    if ($author === '' || mb_strlen($author) > 150) $errors[] = 'Enter an author of up to 150 characters.';
    if ($format === '' || mb_strlen($format) > 40) $errors[] = 'Enter a format of up to 40 characters.';
    if ($categoryName === '' || mb_strlen($categoryName) > 80) $errors[] = 'Enter a category of up to 80 characters.';
    if (!in_array($status, $statuses, true)) $errors[] = 'Choose a valid book status.';

    if (!$errors) {
        $mysqli->begin_transaction();
        try {
            // DATABASE TRANSACTION: create/reuse the category and insert the book as one operation.
            $categoryStatement = $mysqli->prepare('INSERT INTO book_categories (name) VALUES (?) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id)');
            $categoryStatement->bind_param('s', $categoryName);
            $categoryStatement->execute();
            $categoryId = $categoryStatement->insert_id;
            $bookStatement = $mysqli->prepare('INSERT INTO books (category_id, title, author, format, status) VALUES (?, ?, ?, ?, ?)');
            $bookStatement->bind_param('issss', $categoryId, $title, $author, $format, $status);
            $bookStatement->execute();
            $mysqli->commit();
            header('Location: admin_books.php?created=1');
            exit;
        } catch (mysqli_sql_exception $exception) {
            $mysqli->rollback();
            if ($exception->getCode() === 1062) $errors[] = 'That title and author already exist in the catalogue.';
            else throw $exception;
        }
    }
    } else {
        $errors[] = 'That book operation is not supported.';
    }
}

// DATABASE RETRIEVAL: populate category suggestions and the current catalogue table.
$categoryOptions = $mysqli->query('SELECT name FROM book_categories ORDER BY name');
$books = $mysqli->query('SELECT b.id, b.title, b.author, b.format, b.status, c.name AS category FROM books b JOIN book_categories c ON c.id = b.category_id ORDER BY b.created_at DESC, b.title');
function e(string $value): string { return htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><link rel="stylesheet" href="csslab.css"><title>Moonlight Library | Manage Books</title></head>
<body><header><h1>Manage books</h1><p>Add books and control their catalogue status.</p><nav aria-label="Admin navigation"><a href="index.php">View site</a><a href="admin_profile.php">Events</a><a href="admin_books.php" aria-current="page">Books</a><a href="view_members.php">Members</a><a href="admin_logout.php">Sign out</a></nav></header>
<main><section><h2>Add a library book</h2>
<?php if (isset($_GET['created'])): ?><p class="form-message success-message" role="status">The book was added successfully.</p><?php endif; ?>
<?php if (isset($_GET['updated'])): ?><p class="form-message success-message" role="status">The book status was updated successfully.</p><?php endif; ?>
<?php if ($errors): ?><div class="form-message error-message" role="alert"><ul><?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
<form method="post" action="admin_books.php"><input type="hidden" name="csrf_token" value="<?= e(adminCsrfToken()) ?>"><input type="hidden" name="operation" value="add">
<p><label for="book-title">Title</label><br><input id="book-title" name="title" maxlength="180" required value="<?= e($title ?? '') ?>"></p>
<p><label for="book-author">Author</label><br><input id="book-author" name="author" maxlength="150" required value="<?= e($author ?? '') ?>"></p>
<p><label for="book-category">Category</label><br><input id="book-category" name="category" maxlength="80" list="book-categories" required value="<?= e($categoryName ?? '') ?>"><datalist id="book-categories"><?php while ($category = $categoryOptions->fetch_assoc()): ?><option value="<?= e($category['name']) ?>"><?php endwhile; ?></datalist></p>
<p><label for="book-format">Format</label><br><select id="book-format" name="format" required><?php foreach (['Paperback', 'Hardcover', 'E-book', 'Audiobook', 'Reference'] as $option): ?><option<?= ($format ?? '') === $option ? ' selected' : '' ?>><?= e($option) ?></option><?php endforeach; ?></select></p>
<p><label for="book-status">Status</label><br><select id="book-status" name="status" required><?php foreach ($statusLabels as $value => $label): ?><option value="<?= e($value) ?>"<?= ($status ?? 'available') === $value ? ' selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?></select></p>
<button type="submit">Add book</button></form></section>
<section><h2>Catalogue status</h2><?php if ($books->num_rows === 0): ?><p>No books have been added.</p><?php else: ?><div class="table-wrapper"><table><thead><tr><th>Title</th><th>Category</th><th>Status</th><th>Change status</th></tr></thead><tbody><?php while ($book = $books->fetch_assoc()): ?><tr><td><strong><?= e($book['title']) ?></strong><br><small><?= e($book['author']) ?> · <?= e($book['format']) ?></small></td><td><?= e($book['category']) ?></td><td><span class="status-badge <?= e(str_replace('_', '-', $book['status'])) ?>"><?= e($statusLabels[$book['status']]) ?></span></td><td><form class="inline-status-form" method="post" action="admin_books.php"><input type="hidden" name="csrf_token" value="<?= e(adminCsrfToken()) ?>"><input type="hidden" name="operation" value="update_status"><input type="hidden" name="book_id" value="<?= (int) $book['id'] ?>"><label class="visually-hidden" for="status-<?= (int) $book['id'] ?>">Status for <?= e($book['title']) ?></label><select id="status-<?= (int) $book['id'] ?>" name="status"><?php foreach ($statusLabels as $value => $label): ?><option value="<?= e($value) ?>"<?= $book['status'] === $value ? ' selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?></select><button type="submit">Update</button></form></td></tr><?php endwhile; ?></tbody></table></div><?php endif; ?><p><a class="button" href="catalogue.php">View member catalogue</a></p></section></main>
<footer><p><small>&copy; 2026 Moonlight Library</small></p></footer></body></html>
