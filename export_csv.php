<?php
include 'includes/db_connect.php';

$current_week = $_GET['week'] ?? 1;

// Set headers to download the file as CSV
header('Content-Type: text/csv');
header('Content-Disposition: attachment;filename="week_'.$current_week.'_allocations.csv"');

// Output CSV headers
$output = fopen('php://output', 'w');
fputcsv($output, ['Developer', 'Project', 'Allocation (%)']);

// Fetch and output data
$stmt = $db->prepare("SELECT Users.name AS developer, Projects.name AS project, WeeklyAllocations.allocated_percentage 
                      FROM WeeklyAllocations
                      JOIN Users ON WeeklyAllocations.user_id = Users.user_id
                      JOIN Projects ON WeeklyAllocations.project_id = Projects.project_id
                      WHERE week_number = :week");
$stmt->execute([':week' => $current_week]);

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    fputcsv($output, $row);
}

fclose($output);
exit();
?>

