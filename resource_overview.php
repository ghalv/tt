<?php
include 'includes/db_connect.php';
include 'includes/header.php';

$current_week = isset($_GET['week']) ? (int)$_GET['week'] : date('W');

// Fetch users and their allocated percentages
$users = $db->query("SELECT * FROM Users")->fetchAll(PDO::FETCH_ASSOC);

echo "<h2>Resource Overview - Week {$current_week}</h2>";
echo "<table>
        <tr>
            <th>Developer</th>
            <th>Unallocated Time (%)</th>
        </tr>";

foreach ($users as $user) {
    // Fetch total allocation for each user for the current week
    $stmt = $db->prepare("SELECT SUM(allocated_percentage) FROM WeeklyAllocations WHERE week_number = :week AND user_id = :user_id");
    $stmt->execute([':week' => $current_week, ':user_id' => $user['user_id']]);
    $total_allocation = $stmt->fetchColumn() ?: 0;
    
    $unallocated_time = 100 - $total_allocation; // Calculate unallocated time
    echo "<tr>
            <td>{$user['name']}</td>
            <td>" . ($unallocated_time > 0 ? $unallocated_time : 0) . "%</td>
          </tr>";
}
echo "</table>";

include 'includes/footer.php';
?>

