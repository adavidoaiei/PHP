<?php
// Database connection
$db = new SQLite3('users.db');

// Create table if not exists
$db->exec('CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    email TEXT NOT NULL,
    message TEXT
)');

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create':
                $name = SQLite3::escapeString($_POST['name']);
                $email = SQLite3::escapeString($_POST['email']);
                $message = SQLite3::escapeString($_POST['message']);
                
                $stmt = $db->prepare('INSERT INTO users (name, email, message) VALUES (:name, :email, :message)');
                $stmt->bindValue(':name', $name, SQLITE3_TEXT);
                $stmt->bindValue(':email', $email, SQLITE3_TEXT);
                $stmt->bindValue(':message', $message, SQLITE3_TEXT);
                $stmt->execute();
                break;

            case 'update':
                $id = SQLite3::escapeString($_POST['id']);
                $name = SQLite3::escapeString($_POST['name']);
                $email = SQLite3::escapeString($_POST['email']);
                $message = SQLite3::escapeString($_POST['message']);
                
                $stmt = $db->prepare('UPDATE users SET name = :name, email = :email, message = :message WHERE id = :id');
                $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
                $stmt->bindValue(':name', $name, SQLITE3_TEXT);
                $stmt->bindValue(':email', $email, SQLITE3_TEXT);
                $stmt->bindValue(':message', $message, SQLITE3_TEXT);
                $stmt->execute();
                break;

            case 'delete':
                $id = SQLite3::escapeString($_POST['id']);
                $stmt = $db->prepare('DELETE FROM users WHERE id = :id');
                $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
                $stmt->execute();
                break;
        }
    }
}

// Fetch all records
$results = $db->query('SELECT * FROM users');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Form with SQLite - CRUD Operations</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            padding: 20px;
        }
        .edit-form {
            display: none;
            margin-top: 20px;
        }
        .edit-input {
            display: none;
        }
        .save-actions {
            display: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2 class="mb-4">Contact Form</h2>
        <div class="card mb-4">
            <div class="card-body">
                <form method="POST" action="">
                    <input type="hidden" name="action" value="create">
                    <div class="mb-3">
                        <label for="name" class="form-label">Name:</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email:</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="message" class="form-label">Message:</label>
                        <textarea class="form-control" id="message" name="message" rows="4" required></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Submit</button>
                </form>
            </div>
        </div>

        <h3 class="mb-3">Submitted Entries</h3>
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Message</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $results->fetchArray()): ?>
                        <tr id="row_<?php echo $row['id']; ?>">
                            <td><?php echo htmlspecialchars($row['id']); ?></td>
                            <td>
                                <span class="display-value"><?php echo htmlspecialchars($row['name']); ?></span>
                                <input type="text" class="form-control edit-input" style="display: none;" value="<?php echo htmlspecialchars($row['name']); ?>">
                            </td>
                            <td>
                                <span class="display-value"><?php echo htmlspecialchars($row['email']); ?></span>
                                <input type="email" class="form-control edit-input" style="display: none;" value="<?php echo htmlspecialchars($row['email']); ?>">
                            </td>
                            <td>
                                <span class="display-value"><?php echo htmlspecialchars($row['message']); ?></span>
                                <textarea class="form-control edit-input" style="display: none;"><?php echo htmlspecialchars($row['message']); ?></textarea>
                            </td>
                            <td>
                                <div class="btn-group edit-actions">
                                    <button class="btn btn-sm btn-primary" onclick="startEdit(<?php echo $row['id']; ?>)">Edit</button>
                                    <form method="POST" action="" style="display: inline;">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this entry?')">Delete</button>
                                    </form>
                                </div>
                                <div class="btn-group save-actions" style="display: none;">
                                    <button class="btn btn-sm btn-success" onclick="saveEdit(<?php echo $row['id']; ?>)">Save</button>
                                    <button class="btn btn-sm btn-secondary" onclick="cancelEdit(<?php echo $row['id']; ?>)">Cancel</button>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function startEdit(id) {
            const row = document.getElementById(`row_${id}`);
            row.querySelectorAll('.display-value').forEach(el => el.style.display = 'none');
            row.querySelectorAll('.edit-input').forEach(el => el.style.display = 'block');
            row.querySelector('.edit-actions').style.display = 'none';
            row.querySelector('.save-actions').style.display = 'block';
        }

        function saveEdit(id) {
            const row = document.getElementById(`row_${id}`);
            const name = row.querySelector('input[type="text"]').value;
            const email = row.querySelector('input[type="email"]').value;
            const message = row.querySelector('textarea').value;

            const formData = new FormData();
            formData.append('action', 'update');
            formData.append('id', id);
            formData.append('name', name);
            formData.append('email', email);
            formData.append('message', message);

            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (response.ok) {
                    // Update the display values
                    row.querySelectorAll('.display-value')[0].textContent = name;
                    row.querySelectorAll('.display-value')[1].textContent = email;
                    row.querySelectorAll('.display-value')[2].textContent = message;
                    
                    // Switch back to display mode
                    row.querySelectorAll('.display-value').forEach(el => el.style.display = 'block');
                    row.querySelectorAll('.edit-input').forEach(el => el.style.display = 'none');
                    row.querySelector('.edit-actions').style.display = 'block';
                    row.querySelector('.save-actions').style.display = 'none';
                } else {
                    alert('Failed to save changes');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to save changes');
            });
        }

        function cancelEdit(id) {
            const row = document.getElementById(`row_${id}`);
            // Reset input values to original values
            const inputs = row.querySelectorAll('.edit-input');
            const displayValues = row.querySelectorAll('.display-value');
            inputs[0].value = displayValues[0].textContent;
            inputs[1].value = displayValues[1].textContent;
            inputs[2].value = displayValues[2].textContent;

            // Switch back to display mode
            displayValues.forEach(el => el.style.display = 'block');
            inputs.forEach(el => el.style.display = 'none');
            row.querySelector('.edit-actions').style.display = 'block';
            row.querySelector('.save-actions').style.display = 'none';
        }
    </script>
</body>
</html>