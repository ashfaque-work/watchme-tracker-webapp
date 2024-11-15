options = {
    chart: {
        height: 380,
        type: "bar",
        toolbar: {
            show: !1
        }
    },
    plotOptions: {
        bar: {
            dataLabels: {
                position: "bottom"
            }
        }
    },
    dataLabels: {
        enabled: 0,
        formatter: function (e) {
            return e + "%"
        },
        offsetY: -20,
        style: {
            fontSize: "12px",
            colors: ["#304758"]
        }
    },
    colors: ["#2a76f4"],
    series: [{
        name: "Activity",
        data: [5.3, 3.1, 4, 10.1, 4, 3.6, 3.2, 2.3, 1.4, .8, .5, .2]
    }],
    xaxis: {
        categories: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
    },
    fill: {
        gradient: {
            enabled: !1,
            shade: "light",
            type: "horizontal",
            shadeIntensity: .25,
            gradientToColors: void 0,
            inverseColors: !0,
            opacityFrom: 1,
            opacityTo: 1,
            stops: [50, 0, 100, 100]
        }
    },
    yaxis: {
        axisBorder: {
            show: !1
        },
        axisTicks: {
            show: !1
        },
        labels: {
            show: 1,
            formatter: function (e) {
                return e
            }
        }
    },
    grid: {
        row: {
            colors: ["#afb7d21a", "transparent"],
            opacity: .2
        },
        borderColor: "#f1f3fa"
    }
};
(chart = new ApexCharts(document.querySelector("#reportchart"), options)).render();