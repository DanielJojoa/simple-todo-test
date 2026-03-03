<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/env.php';
require_once __DIR__ . '/../src/database.php';

loadEnv(__DIR__ . '/../.env');

$errors = [];

try {
    $pdo = db();
} catch (Throwable $e) {
    http_response_code(500);
    echo '<h1>Database connection failed</h1>';
    echo '<pre>' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '</pre>';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $title = trim((string) ($_POST['title'] ?? ''));
        if ($title === '') {
            $errors[] = 'Title is required.';
        } else {
            $stmt = $pdo->prepare('INSERT INTO todos (title) VALUES (:title)');
            $stmt->execute(['title' => $title]);
            redirectToHome();
        }
    }

    if ($action === 'toggle') {
        $id = (int) ($_POST['id'] ?? 0);
        $stmt = $pdo->prepare('UPDATE todos SET is_done = NOT is_done WHERE id = :id');
        $stmt->execute(['id' => $id]);
        redirectToHome();
    }

    if ($action === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);
        $stmt = $pdo->prepare('DELETE FROM todos WHERE id = :id');
        $stmt->execute(['id' => $id]);
        redirectToHome();
    }
}

$todos = $pdo->query('SELECT id, title, is_done, created_at FROM todos ORDER BY id DESC')->fetchAll();

function redirectToHome(): void
{
    header('Location: /');
    exit;
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>PHP Todo App</title>
  <style>
    body { font-family: Arial, sans-serif; margin: 2rem; max-width: 700px; }
    form { margin-bottom: 1rem; }
    input[type="text"] { width: 70%; padding: 0.5rem; }
    button { padding: 0.45rem 0.8rem; }
    ul { list-style: none; padding: 0; }
    li { display: flex; align-items: center; justify-content: space-between; padding: 0.5rem 0; border-bottom: 1px solid #ddd; }
    .todo-title { flex: 1; margin-right: 1rem; }
    .done { text-decoration: line-through; color: #666; }
    .actions { display: flex; gap: 0.5rem; }
    .error { color: #b00020; margin-bottom: 1rem; }
  </style>
</head>
<body>
  <h1>Todo App (PHP + PostgreSQL)</h1>

  <?php foreach ($errors as $error): ?>
    <p class="error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
  <?php endforeach; ?>

  <form method="post">
    <input type="hidden" name="action" value="create">
    <input type="text" name="title" placeholder="Add a new task...">
    <button type="submit">Add</button>
  </form>

  <ul>
    <?php foreach ($todos as $todo): ?>
      <li>
        <span class="todo-title <?= $todo['is_done'] ? 'done' : '' ?>">
          <?= htmlspecialchars($todo['title'], ENT_QUOTES, 'UTF-8') ?>
        </span>
        <div class="actions">
          <form method="post">
            <input type="hidden" name="action" value="toggle">
            <input type="hidden" name="id" value="<?= (int) $todo['id'] ?>">
            <button type="submit"><?= $todo['is_done'] ? 'Undo' : 'Done' ?></button>
          </form>
          <form method="post">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="<?= (int) $todo['id'] ?>">
            <button type="submit">Delete</button>
          </form>
        </div>
      </li>
    <?php endforeach; ?>
  </ul>
</body>
</html>

