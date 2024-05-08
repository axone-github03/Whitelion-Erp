var optionsBarChart = {
series: [{
name: 'Sales',
data: [34, 29, 32, 38, 39, 35, 36,34, 29, 32]
}],
chart: {
type: 'bar',
width: "100%",
height: 360,
},
theme: {
monochrome: {
enabled: true,
color: '#556ee6',
}
},
plotOptions: {
bar: {
columnWidth: '15%',
borderRadius: 2,
endingShape:"rounded",
radiusOnLastStackedBar: true,
colors: {
backgroundBarColors: ['#F2F4F6', '#F2F4F6', '#F2F4F6', '#F2F4F6', '#F2F4F6', '#F2F4F6', '#F2F4F6', '#F2F4F6', '#F2F4F6', '#F2F4F6'],
backgroundBarRadius: 10,
},
}
},
labels: [1, 2, 3, 4, 5, 6, 7,8,9,10],
xaxis: {
categories: ['01 Feb', '02 Feb', '03 Feb', '04 Feb', '05 Feb', '06 Feb', '07 Feb', '08 Feb', '09 Feb', '10 Feb'],
crosshairs: {
width: 2
},
},
tooltip: {
fillSeriesColor: false,
onDatasetHover: {
highlightDataSeries: false,
},
theme: 'light',
style: {
fontSize: '12px',
fontFamily: 'Inter',
},
y: {
formatter: function (val) {
return "$ " + val + "k"
}
}
},
};

var barChartEl = document.getElementById('bar-chart-apex');
if (barChartEl) {
var barChart = new ApexCharts(barChartEl, optionsBarChart);
barChart.render();
}


var options = {
    series: [76, 67, 61],
    chart: {
    height: 390,
    type: 'radialBar',
  },
  plotOptions: {
    radialBar: {
      offsetY: 0,
      startAngle: 0,
      endAngle: 270,
      hollow: {
        margin: 5,
        size: '30%',
        background: 'transparent',
        image: undefined,
      },
      dataLabels: {
        name: {
          show: false,
        },
        value: {
          show: false,
        }
      }
    }
  },
  colors: ['#1ab7ea', '#0084ff', '#39539E'],
  labels: ['Orders', 'Placed', 'Dispatched'],
  legend: {
    show: true,
    floating: true,
    fontSize: '16px',
    position: 'left',
    offsetX: 160,
    offsetY: 15,
    labels: {
      useSeriesColors: true,
    },
    markers: {
      size: 0
    },
    formatter: function(seriesName, opts) {
      return seriesName + ":  " + opts.w.globals.series[opts.seriesIndex]
    },
    itemMargin: {
      vertical: 3
    }
  },
  plotOptions: {
    radialBar: {
      offsetY: -30
    }
  },
  legend: {
    show: true,
    position: 'left',
    containerMargin: {
      right: 0
    }
  },
  theme: {
    monochrome: {
      enabled: false
    }
  },
  responsive: [{
    breakpoint: 480,
    options: {
      legend: {
          show: false
      }
    }
  }]
  };

  var chart = new ApexCharts(document.querySelector("#radial-bar-chart"), options);
  chart.render();