// scripts.js
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('select[name^="allocated_percentage"]').forEach(select => {
        select.addEventListener('change', function () {
            const userId = this.closest('tr').querySelector('input[name="user_id"]').value;
            const projectId = this.closest('td').getAttribute('data-project-id');
            const allocatedPercentage = this.value;
            const weekNumber = document.querySelector('input[name="week_number"]').value;
            
            // AJAX call to save allocation
            fetch('save_allocation.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    week_number: weekNumber,
                    user_id: userId,
                    project_id: projectId,
                    allocated_percentage: allocatedPercentage
                })
            })
            .then(response => response.text())
            .then(data => {
                console.log("Allocation saved:", data);
            })
            .catch(error => console.error("Error saving allocation:", error));
        });
    });
});

