<?php
include 'includes/db_connect.php';
include 'includes/header.php';

// Fetch project demands and total allocations per project for the current week
$current_week = isset($_GET['week']) ? (int)$_GET['week'] : date('W');
$projects = $db->query("SELECT * FROM Projects")->fetchAll(PDO::FETCH_ASSOC);
$chart_data = [];

foreach ($projects as $project) {
    // Calculate total allocation for this project
    $stmt = $db->prepare("SELECT SUM(allocated_percentage) FROM WeeklyAllocations WHERE week_number = :week AND project_id = :project_id");
    $stmt->execute([':week' => $current_week, ':project_id' => $project['project_id']]);
    $total_allocation = $stmt->fetchColumn() ?: 0;

    // Append to data for Chart.js
    $chart_data[] = [
        'project' => $project['name'],
        'demand' => $project['demand_percentage'],
        'allocation' => $total_allocation
    ];
}
?>

<h2>Statistics - Week <?php echo $current_week; ?></h2>
<canvas id="demandAllocationChart" width="400" height="200"></canvas>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('demandAllocationChart').getContext('2d');
const data = <?php echo json_encode($chart_data); ?>;

new Chart(ctx, {
    type: 'bar',
    data: {
        labels: data.map(item => item.project),
        datasets: [
            {
                label: 'Demand (%)',
                data: data.map(item => item.demand),
                backgroundColor: 'rgba(255, 99, 132, 0.5)',
            },
            {
                label: 'Allocation (%)',
                data: data.map(item => item.allocation),
                backgroundColor: 'rgba(54, 162, 235, 0.5)',
            }
        ]
    },
    options: {
        scales: {
            y: { beginAtZero: true, max: 300 } // Adjust max based on demand scale
        }
    }
});
</script>

<p>
    <a href="?week=<?php echo $current_week - 1; ?>">Previous Week</a> |
    <a href="?week=<?php echo $current_week + 1; ?>">Next Week</a>
</p>

<?php include 'includes/footer.php'; ?>

