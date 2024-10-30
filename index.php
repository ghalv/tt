<?php
include 'includes/db_connect.php';
include 'includes/header.php';

$current_week = $_GET['week'] ?? 1; // Default to week 1 if no week is specified

// Fetch users and their active projects
$users = $db->query("SELECT * FROM Users")->fetchAll(PDO::FETCH_ASSOC);
$projects = $db->query("SELECT * FROM Projects")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'];
    $project_id = $_POST['project_id'];
    $allocated_percentage = $_POST['allocated_percentage'];
    
    // Insert or update allocation
    $stmt = $db->prepare("INSERT INTO WeeklyAllocations (week_number, user_id, project_id, allocated_percentage) 
                          VALUES (:week, :user_id, :project_id, :allocated_percentage)
                          ON CONFLICT(week_number, user_id, project_id) 
                          DO UPDATE SET allocated_percentage = :allocated_percentage");
    $stmt->execute([
        ':week' => $current_week,
        ':user_id' => $user_id,
        ':project_id' => $project_id,
        ':allocated_percentage' => $allocated_percentage
    ]);
}

?>

<h2>Weekly Allocation View - Week <?php echo $current_week; ?></h2>

<form method="POST" action="">
    <input type="hidden" name="week_number" value="<?php echo $current_week; ?>">
    
    <table>
        <tr>
            <th>Developer</th>
            <?php foreach ($projects as $project): ?>
                <th>
                    <?php echo htmlspecialchars($project['customer']); ?><br>
                    <label>Demand:</label>
                    <input type="number" name="demand_percentage[<?php echo $project['project_id']; ?>]" min="0" max="300" step="5" required>
                </th>
            <?php endforeach; ?>
        </tr>
        
        <?php foreach ($users as $user): ?>
            <tr>
                <td><?php echo htmlspecialchars($user['name']); ?></td>
                <?php foreach ($projects as $project): ?>
                    <td>
                        <?php
                        // Fetch allocation for this user/project/week
                        $stmt = $db->prepare("SELECT allocated_percentage FROM WeeklyAllocations WHERE week_number = :week AND user_id = :user_id AND project_id = :project_id");
                        $stmt->execute([
                            ':week' => $current_week,
                            ':user_id' => $user['user_id'],
                            ':project_id' => $project['project_id']
                        ]);
                        $allocation = $stmt->fetchColumn();
                        ?>
                        
                        <select name="allocated_percentage[<?php echo $user['user_id']; ?>][<?php echo $project['project_id']; ?>]">
                            <?php for ($i = 0; $i <= 100; $i += 5): ?>
                                <option value="<?php echo $i; ?>" <?php echo ($allocation == $i) ? 'selected' : ''; ?>>
                                    <?php echo $i; ?>%
                                </option>
                            <?php endfor; ?>
                        </select>
                    </td>
                <?php endforeach; ?>
            </tr>
        <?php endforeach; ?>
    </table>
    
    <button type="submit">Save Allocations</button>
</form>

<p>
    <a href="?week=<?php echo $current_week - 1; ?>">Previous Week</a> |
    <a href="?week=<?php echo $current_week + 1; ?>">Next Week</a>
</p>

<p><a href="export_csv.php?week=<?php echo $current_week; ?>">Export Current Week as CSV</a></p>
<?php include 'includes/footer.php'; ?>

