<?php
include 'includes/db_connect.php';
include 'includes/header.php';

// Set the current week as the default if no week is specified in the URL
$current_week = isset($_GET['week']) ? (int)$_GET['week'] : date('W');

// Fetch users and projects
$users = $db->query("SELECT * FROM Users")->fetchAll(PDO::FETCH_ASSOC);
$projects = $db->query("SELECT * FROM Projects")->fetchAll(PDO::FETCH_ASSOC);

// Check if form is submitted to save allocations and demand
$error_message = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sum allocations to prevent over-allocation
    $overAllocatedUsers = [];
    foreach ($_POST['allocated_percentage'] as $user_id => $allocations) {
        $totalAllocation = array_sum($allocations);  // Sum all allocations for this user

        if ($totalAllocation > 100) {
            $overAllocatedUsers[] = $user_id;
        }
    }

    // Show error if over-allocated; else proceed to save allocations
    if (!empty($overAllocatedUsers)) {
        $error_message = "Warning: One or more users are over-allocated (>100%). Please adjust allocations.";
    } else {
        // Save demand input for each project in WeeklyDemand table
        foreach ($_POST['demand_percentage'] as $project_id => $demand_percentage) {
            $stmt = $db->prepare("INSERT INTO WeeklyDemand (week_number, project_id, demand_percentage)
                                  VALUES (:week, :project_id, :demand_percentage)
                                  ON CONFLICT(week_number, project_id)
                                  DO UPDATE SET demand_percentage = :demand_percentage");
            $stmt->execute([
                ':week' => $current_week,
                ':project_id' => $project_id,
                ':demand_percentage' => (int)$demand_percentage
            ]);
        }

        // Save allocations for each user/project in WeeklyAllocations table
        foreach ($_POST['allocated_percentage'] as $user_id => $allocations) {
            foreach ($allocations as $project_id => $allocated_percentage) {
                $allocated_percentage = (int)$allocated_percentage;

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
        }
        echo "Allocations and demands saved successfully!";
    }
}

// Fetch saved demand for each project and set as default
$weeklyDemand = [];
$stmt = $db->prepare("SELECT project_id, demand_percentage FROM WeeklyDemand WHERE week_number = :week");
$stmt->execute([':week' => $current_week]);
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $weeklyDemand[$row['project_id']] = $row['demand_percentage'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Weekly Allocation View</title>
    <link rel="stylesheet" href="assets/styles.css">
    <script src="assets/scripts.js"></script>
</head>
<body>
    <h2>Weekly Allocation View - Week <?php echo $current_week; ?></h2>

    <?php if ($error_message): ?>
        <div class="error-message"><?php echo $error_message; ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <input type="hidden" name="week_number" value="<?php echo $current_week; ?>">

        <table>
            <tr>
                <th>Developer</th>
                <?php foreach ($projects as $project): ?>
                    <th>
                        <?php echo htmlspecialchars($project['customer']); ?><br>
                        <label>Demand:</label>
                        <input type="number" name="demand_percentage[<?php echo $project['project_id']; ?>]"
                               value="<?php echo $weeklyDemand[$project['project_id']] ?? 0; ?>"
                               min="0" max="300" step="5" required>
                    </th>
                <?php endforeach; ?>
            </tr>

            <?php foreach ($users as $user): ?>
                <tr class="user-row">
                    <td><?php echo htmlspecialchars($user['name']); ?></td>
                    <?php foreach ($projects as $project): ?>
                        <td>
                            <?php
                            $stmt = $db->prepare("SELECT allocated_percentage FROM WeeklyAllocations WHERE week_number = :week AND user_id = :user_id AND project_id = :project_id");
                            $stmt->execute([
                                ':week' => $current_week,
                                ':user_id' => $user['user_id'],
                                ':project_id' => $project['project_id']
                            ]);
                            $allocation = $stmt->fetchColumn() ?: 0;
                            ?>
                            
                            <select class="allocation-dropdown" name="allocated_percentage[<?php echo $user['user_id']; ?>][<?php echo $project['project_id']; ?>]">
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

            <tr class="summary-row">
                <th>Total Allocated</th>
                <?php foreach ($projects as $project): ?>
                    <?php
                    $stmt = $db->prepare("SELECT SUM(allocated_percentage) FROM WeeklyAllocations WHERE week_number = :week AND project_id = :project_id");
                    $stmt->execute([':week' => $current_week, ':project_id' => $project['project_id']]);
                    $total_allocation = $stmt->fetchColumn() ?: 0;
                    $demand = $weeklyDemand[$project['project_id']] ?? 0;
                    $difference = $total_allocation - $demand;
                    ?>
                    <td>
                        <?php echo $total_allocation; ?>%
                        <?php if ($difference !== 0): ?>
                            <span class="difference <?php echo $difference < 0 ? 'under' : 'over'; ?>">
                                (<?php echo $difference < 0 ? 'Under' : 'Over'; ?>: <?php echo abs($difference); ?>%)
                            </span>
                        <?php endif; ?>
                    </td>
    <?php endforeach; ?>
</tr>
        </table>

        <button type="submit">Save Allocations and Demands</button>
    </form>

    <p>
        <a href="?week=<?php echo $current_week - 1; ?>">Previous Week</a> |
        <a href="?week=<?php echo date('W'); ?>">Current Week</a> |
        <a href="?week=<?php echo $current_week + 1; ?>">Next Week</a>
    </p>

<?php include 'includes/footer.php'; ?>

</body>
</html>
