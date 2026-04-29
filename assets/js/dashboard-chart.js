const ctx = document.getElementById("transactionChart");

new Chart(ctx, {
  type: "line",
  data: {
    labels: ["Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun"],
    datasets: [
      {
        label: "Transactions",
        data: [3000, 4000, 3500, 6000, 7000, 9000, 12000],
        borderColor: "#064420",
        backgroundColor: "rgba(6,68,32,0.1)",
        fill: true,
        tension: 0.4,
      },
    ],
  },
});
