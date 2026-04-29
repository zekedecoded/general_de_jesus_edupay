// CLOCK
setInterval(() => {
  document.getElementById("time").innerText = new Date().toLocaleTimeString();
}, 1000);

// CHART
const ctx = document.getElementById("chart");

new Chart(ctx, {
  type: "line",
  data: {
    labels: ["Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun"],
    datasets: [
      {
        label: "Top-Ups",
        data: [200, 400, 300, 500, 600, 400, 700],
        borderColor: "#16a34a",
        tension: 0.4,
      },
      {
        label: "Payments",
        data: [100, 300, 200, 400, 500, 300, 600],
        borderColor: "#2563eb",
        tension: 0.4,
      },
    ],
  },
});
