data4chart.unshift(['date','警告', '復帰', 'エラー', { role: 'annotation' } ]);
google.charts.load('current', {packages: ['corechart', 'bar']});

window.addEventListener('load', function() {
  // HTMLの文書が完全に描画されたときに実行する処理
  setTimeout(function() {
    google.charts.setOnLoadCallback(drawMultSeries);
  }, 250);
});

function drawMultSeries() {
    const data = google.visualization.arrayToDataTable(data4chart);

    const chartElement = document.getElementById('chart');

    const chartWidth = chartElement.clientWidth;
    console.log(chartWidth)
    const chartHeight = chartElement.clientHeight;

    const chart = new google.visualization.ColumnChart(chartElement);

      let options = {
        hAxis: {
          format: 'h:mm a',
          viewWindow: {
            min: [7, 30, 0],
            max: [17, 30, 0]
          }
        },
        colors: ['#ffc107', '#17a2b8', '#dc3545',],
        'width': chartWidth,
        'height': chartHeight,
      };

      chart.draw(data, options);
    }

// ReSizeイベント
window.addEventListener('resize', function() {
  setTimeout(function() {
    google.charts.setOnLoadCallback(drawMultSeries);
  }, 100);
});
