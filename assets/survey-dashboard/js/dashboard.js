document.addEventListener("DOMContentLoaded", function () {
    if (!window.dashboardData) return;

    const ctx = document.getElementById("finalBucketChart");
    if (!ctx) return;

    new Chart(ctx, {
        type: "doughnut",
        data: {
            labels: window.dashboardData.labels,
            datasets: [{
                data: window.dashboardData.values
            }]
        }
    });
});
