<?php
include 'includes/db_connect.php';
include 'includes/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $customer = $_POST['customer'];
    $stmt = $db->prepare("INSERT INTO Projects (name, customer) VALUES (:name, :customer)");
    $stmt->execute([':name' => $name, ':customer' => $customer]);
    echo "<p>Project added successfully!</p>";
}
?>

<h2>Project Management</h2>

<form method="POST" action="">
    <label for="name">Project Name:</label>
    <input type="text" id="name" name="name" required>
    <label for="customer">Customer:</label>
    <input type="text" id="customer" name="customer" required>
    <button type="submit">Add Project</button>
</form>

<h3>Current Projects</h3>
<table>
    <tr>
        <th>Project Name</th>
        <th>Customer</th>
        <th>Actions</th>
    </tr>
    <?php
    $stmt = $db->query("SELECT * FROM Projects");
    while ($project = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>
                <td>{$project['name']}</td>
                <td>{$project['customer']}</td>
                <td>
                    <a href='edit_project.php?id={$project['project_id']}'>Edit</a> |
                    <a href='delete_project.php?id={$project['project_id']}'>Delete</a>
                </td>
              </tr>";
    }
    ?>
</table>

<?php include 'includes/footer.php'; ?>

