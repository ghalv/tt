document.addEventListener('DOMContentLoaded', function () {
    // AJAX Save Allocation on Change
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
                updateRemainingCapacity();  // Call update function after save
            })
            .catch(error => console.error("Error saving allocation:", error));
        });
    });

    // Dynamic Capacity Update
    document.querySelectorAll('select.allocation-dropdown').forEach(select => {
        select.addEventListener('change', updateRemainingCapacity);
    });

    function updateRemainingCapacity() {
        const userRows = document.querySelectorAll('.user-row');

        userRows.forEach(row => {
            let totalAllocated = 0;
            row.querySelectorAll('.allocation-dropdown').forEach(select => {
                totalAllocated += parseInt(select.value, 10);
            });

            const remainingCapacity = 100 - totalAllocated;

            row.querySelectorAll('.allocation-dropdown').forEach(select => {
                Array.from(select.options).forEach(option => {
                    option.disabled = parseInt(option.value, 10) > remainingCapacity;
                });
            });
        });
    }
});
