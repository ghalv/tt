<?php
include 'includes/db_connect.php';
include 'includes/header.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $role = $_POST['role'];
    $stmt = $db->prepare("INSERT INTO Users (name, role) VALUES (:name, :role)");
    $stmt->execute([':name' => $name, ':role' => $role]);
    echo "<p>User added successfully!</p>";
}
?>

<h2>User Management</h2>

<form method="POST" action="">
    <label for="name">Name:</label>
    <input type="text" id="name" name="name" required>
    <label for="role">Role:</label>
    <select id="role" name="role" required>
        <option value="teamlead">Teamlead</option>
        <option value="devs">Devs</option>
        <option value="leveranseansvarlig">Leveranseansvarlig</option>
        <option value="eksterne">Eksterne</option>
    </select>
    <button type="submit">Add User</button>
</form>

<h3>Current Users</h3>
<table>
    <tr>
        <th>Name</th>
        <th>Role</th>
        <th>Actions</th>
    </tr>
    <?php
    $stmt = $db->query("SELECT * FROM Users");
    while ($user = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>
                <td>{$user['name']}</td>
                <td>{$user['role']}</td>
                <td>
                    <a href='edit_user.php?id={$user['user_id']}'>Edit</a> |
                    <a href='delete_user.php?id={$user['user_id']}'>Delete</a>
                </td>
              </tr>";
    }
    ?>
</table>

<?php include 'includes/footer.php'; ?>

