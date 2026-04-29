const ctx = document.getElementById("transactionChart");

new Chart(ctx, {
  type: "line",
  data: {
    labels: [
      "Apr 18",
      "Apr 19",
      "Apr 20",
      "Apr 21",
      "Apr 22",
      "Apr 23",
      "Apr 24",
    ],
    datasets: [
      {
        label: "Transaction Volume",
        data: [3200, 4500, 3800, 6200, 7100, 8900, 12450],
        borderColor: "#064420",
        backgroundColor: "rgba(6, 68, 32, 0.12)",
        borderWidth: 3,
        tension: 0.42,
        fill: true,
        pointRadius: 5,
        pointHoverRadius: 7,
        pointBackgroundColor: "#d9a928",
        pointBorderColor: "#064420",
        pointBorderWidth: 2,
      },
    ],
  },
  options: {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: {
        labels: {
          color: "#064420",
          font: {
            weight: "700",
          },
        },
      },
    },
    scales: {
      x: {
        ticks: {
          color: "#66756c",
        },
        grid: {
          color: "rgba(15, 23, 42, 0.06)",
        },
      },
      y: {
        beginAtZero: true,
        ticks: {
          color: "#66756c",
        },
        grid: {
          color: "rgba(15, 23, 42, 0.06)",
        },
      },
    },
  },
});
