<?php
include 'includes/db_connect.php';

// Check if a user ID is provided
if (isset($_GET['id'])) {
    $user_id = $_GET['id'];

    // Fetch user data
    $stmt = $db->prepare("SELECT * FROM Users WHERE user_id = :id");
    $stmt->execute([':id' => $user_id]);
    $user = $stmt->fetch();

    // Update user data if form is submitted
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = $_POST['name'];
        $role = $_POST['role'];
        $update_stmt = $db->prepare("UPDATE Users SET name = :name, role = :role WHERE user_id = :id");
        $update_stmt->execute([':name' => $name, ':role' => $role, ':id' => $user_id]);
        header('Location: users.php');
        exit;
    }
} else {
    echo "User ID not provided";
    exit;
}
?>

<!-- Edit User Form -->
<h2>Edit User</h2>
<form method="POST" action="">
    <label for="name">Name:</label>
    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
    <label for="role">Role:</label>
    <select id="role" name="role" required>
        <option value="teamlead" <?php if ($user['role'] == 'teamlead') echo 'selected'; ?>>Teamlead</option>
        <option value="devs" <?php if ($user['role'] == 'devs') echo 'selected'; ?>>Devs</option>
        <option value="leveranseansvarlig" <?php if ($user['role'] == 'leveranseansvarlig') echo 'selected'; ?>>Leveranseansvarlig</option>
        <option value="eksterne" <?php if ($user['role'] == 'eksterne') echo 'selected'; ?>>Eksterne</option>
    </select>
    <button type="submit">Update User</button>
</form>
