Chart.defaults.font.size = 14;

const total_orders_monthly_data = {
  labels: months,
  datasets: [{
    label: 'Total Orders Monthly',
    backgroundColor: '#00b9f1',
    borderColor: '#00b9f1',
    data: orders,
    tension: 0.2
  }]
};

const hottest_items_data = {
  labels: tattoos,
  datasets: [{
    label: 'Most Ordered Tattoos',
    data: quantities,
    backgroundColor: colors,
    hoverOffset: 4
  }]
};

const total_sales_monthly_data = {
  labels: months,
  datasets: [{
    label: 'Total Sales Monthly (₱)',
    data: sales,
    backgroundColor: [
      'rgba(255, 99, 132, 0.2)',
      'rgba(255, 159, 64, 0.2)',
      'rgba(255, 205, 86, 0.2)',
      'rgba(75, 192, 192, 0.2)',
      'rgba(54, 162, 235, 0.2)',
      'rgba(153, 102, 255, 0.2)'
    ],
    borderColor: [
      'rgb(255, 99, 132)',
      'rgb(255, 159, 64)',
      'rgb(255, 205, 86)',
      'rgb(75, 192, 192)',
      'rgb(54, 162, 235)',
      'rgb(153, 102, 255)'
    ],
    borderWidth: 1
  }]
};

const total_orders_monthly_config = {
  type: 'line',
  data: total_orders_monthly_data,
  options: {}
};

const hottest_items_config = {
  type: 'doughnut',
  data: hottest_items_data,
};

const total_sales_monthly_config = {
  type: 'bar',
  data: total_sales_monthly_data,
  options: {
    scales: {
      y: {
        beginAtZero: true
      }
    }
  },
};

const total_orders_monthly = new Chart(
  document.getElementById('monthly_orders'),
  total_orders_monthly_config
);

const hottest_items = new Chart(
  document.getElementById('hottest_items'),
  hottest_items_config
);

const total_sales_monthly = new Chart(
  document.getElementById('monthly_sales'),
  total_sales_monthly_config
);