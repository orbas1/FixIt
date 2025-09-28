/* Servicemen Chart */
var servicemenChart = {
    series: [{
        name: "",
        data: [41, 91, 40, 80, 62, 69, 91]
    }],
    stroke: {
        width: 3
    },
    chart: {
        height: 74,
        type: 'line',
        toolbar: {
            show: false,
        },
        zoom: {
            enabled: false
        }
    },
    xaxis: {
        show: false,
        labels: {
            show: false,
        }
    },

    yaxis: {
        show: false,
    },
    dataLabels: {
        enabled: false
    },
    grid: {
        show: false,
        padding: {
            top: -10,
            bottom: -10,
        },
    },
    markers: {
        size: 5,
    },
    tooltip: {
        enabled: false
    }
};
var trigoStrength = 3;
var iteration = 11;

function getNewData(baseval, yrange) {
    var newTime = baseval + 300000;
    return {
        x: newTime,
        y: Math.floor(Math.random() * (yrange.max - yrange.min + 1)) + yrange.min
    };
}
var servicemenChart = new ApexCharts(document.querySelector("#servicemen-chart"), servicemenChart);
servicemenChart.render();
/* Servicemen Chart */

/* Provider Chart */
var providerChart = {
    series: [{
        name: 'Online Sale',
        data: [120, 150, 250, 145, 130, 250, 200, 200, 300, 175, 155, 200]
    }],
    colors: ["#AD46FF"],
    chart: {
        type: 'area',
        height: 74,
        toolbar: {
            tools: {
                zoom: false,
                zoomin: false,
                zoomout: false,
                reset: false,
                pan: false,
                download: false,
            },
        },
    },
    tooltip: {
        enabled: false,
    },
    fill: {
        gradient: {
            opacityFrom: 0.4,
            opacityTo: 0.1,
        },
    },
    markers: {
        discrete: [{
                seriesIndex: 0,
                dataPointIndex: 1,
                fillColor: '#fff',
                strokeColor: "#AD46FF",
                size: 3,
                shape: "circle"
            },
            {
                seriesIndex: 0,
                dataPointIndex: 2,
                fillColor: '#fff',
                strokeColor: "#AD46FF",
                size: 3,
                shape: "circle"
            },
            {
                seriesIndex: 0,
                dataPointIndex: 3,
                fillColor: '#fff',
                strokeColor: "#AD46FF",
                size: 3,
                shape: "circle"
            },
            {
                seriesIndex: 0,
                dataPointIndex: 4,
                fillColor: '#fff',
                strokeColor: "#AD46FF",
                size: 3,
                shape: "circle"
            },
            {
                seriesIndex: 0,
                dataPointIndex: 5,
                fillColor: '#fff',
                strokeColor: "#AD46FF",
                size: 3,
                shape: "circle"
            },
            {
                seriesIndex: 0,
                dataPointIndex: 6,
                fillColor: '#fff',
                strokeColor: "#AD46FF",
                size: 3,
                shape: "circle"
            },
            {
                seriesIndex: 0,
                dataPointIndex: 7,
                fillColor: '#fff',
                strokeColor: "#AD46FF",
                size: 3,
                shape: "circle"
            },
            {
                seriesIndex: 0,
                dataPointIndex: 8,
                fillColor: '#fff',
                strokeColor: "#AD46FF",
                size: 3,
                shape: "circle"
            },
            {
                seriesIndex: 0,
                dataPointIndex: 9,
                fillColor: '#fff',
                strokeColor: "#AD46FF",
                size: 3,
                shape: "circle"
            },
            {
                seriesIndex: 0,
                dataPointIndex: 10,
                fillColor: '#fff',
                strokeColor: "#AD46FF",
                size: 3,
                shape: "circle"
            },
            {
                seriesIndex: 1,
                dataPointIndex: 1,
                fillColor: '#fff',
                strokeColor: "#AD46FF",
                size: 3,
                shape: "circle"
            },
            {
                seriesIndex: 1,
                dataPointIndex: 2,
                fillColor: '#fff',
                strokeColor: "#AD46FF",
                size: 3,
                shape: "circle"
            },
            {
                seriesIndex: 1,
                dataPointIndex: 3,
                fillColor: '#fff',
                strokeColor: "#AD46FF",
                size: 3,
                shape: "circle"
            },
            {
                seriesIndex: 1,
                dataPointIndex: 4,
                fillColor: '#fff',
                strokeColor: "#AD46FF",
                size: 3,
                shape: "circle"
            },
            {
                seriesIndex: 1,
                dataPointIndex: 5,
                fillColor: '#fff',
                strokeColor: "#AD46FF",
                size: 3,
                shape: "circle"
            },
            {
                seriesIndex: 1,
                dataPointIndex: 6,
                fillColor: '#fff',
                strokeColor: "#AD46FF",
                size: 3,
                shape: "circle"
            },
            {
                seriesIndex: 1,
                dataPointIndex: 7,
                fillColor: '#fff',
                strokeColor: "#AD46FF",
                size: 3,
                shape: "circle"
            },
            {
                seriesIndex: 1,
                dataPointIndex: 8,
                fillColor: '#fff',
                strokeColor: "#AD46FF",
                size: 3,
                shape: "circle"
            },
            {
                seriesIndex: 1,
                dataPointIndex: 9,
                fillColor: '#fff',
                strokeColor: "#AD46FF",
                size: 3,
                shape: "circle"
            },
            {
                seriesIndex: 1,
                dataPointIndex: 10,
                fillColor: '#fff',
                strokeColor: "#AD46FF",
                size: 3,
                shape: "circle"
            },
        ],
    },
    legend: {
        show: false,
    },
    stroke: {
        curve: 'stepline',
        width: 1,
    },
    dataLabels: {
        enabled: false,
    },
    grid: {
        show: false,

        padding: {
            top: -30,
            bottom: -25,
            left: -20,
            right: -20,
        },
    },
    xaxis: {
        lines: {
            show: false,
        }
    },
    yaxis: {
        labels: {
            show: false,
        }
    },
};
var providerChart = new ApexCharts(document.querySelector("#provider-chart"), providerChart);
providerChart.render();
/* Provider Chart */

/* Offline Payment Chart */
var offlinePaymentChart = {
    series: [{
        name: "Bounce Rate",
        data: [10, 5, 4, 8, 3, 4, 6, 4, 3, 9, 10, 5, 12, 14, 10, 12, 14, 14, 10, 12, 14, 20, 24, 14, 10,
            12, 10, 12, 14, 18, 18, 10, 12, 10, 8, 12, 10, 12, 14, 10, 8, 10, 12, 12, 14, 10, 14,
            12, 12, 10, 22, 12, 11, 8, 12, 8, 12, 14, 13, 16, 14, 14, 6, 10, 18, 8
        ],
    }, ],
    chart: {
        height: 73,
        type: "area",
        toolbar: {
            show: false
        },
        offsetX: 0,
        offsetY: 0
    },
    stroke: {
        width: 2,
        curve: "straight",
        lineCap: "butt",
    },
    colors: ["#fc564a"],
    fill: {
        type: "gradient",
        gradient: {
            shade: "light",
            type: "vertical",
            shadeIntensity: 0.1,
            inverseColors: true,
            opacityFrom: 0.5,
            opacityTo: 0.1,
            stops: [0, 100],
        },
    },
    dataLabels: {
        enabled: false,
    },
    tooltip: {
        enabled: false
    },
    title: {
        show: false,
    },
    grid: {
        show: false,
        padding: {
            top: -40,
            bottom: -30,
            left: -10,
            right: -10
        },
    },
    xaxis: {
        categories: undefined,
        labels: {
            show: false
        },
        axisBorder: {
            show: false
        },
        axisTicks: {
            show: false
        },
        tooltip: {
            enabled: false
        },
    },
    yaxis: {
        show: false,
        min: 0,
        max: 30,
    },
    markers: {
        hover: {
            sizeOffset: 4
        },
    },
    // responsive: [
    //     {
    //         breakpoint: 1501,
    //         options: {
    //             chart: {
    //                 height: 188
    //             }
    //         },
    //     },
    //     {
    //         breakpoint: 1200,
    //         options: {
    //             chart: {
    //                 height: 130
    //             }
    //         },
    //     },
    // ],
};
var offlinePaymentChart = new ApexCharts(document.querySelector("#offline-payment-chart"), offlinePaymentChart);
offlinePaymentChart.render();
/* Offline Payment Chart */

/* Verified Chart */
var verifiedChart = {
    series: [{
        name: "New Packages",
        data: [1.5, 3, 1.9, 2.6, 3.6, 3.4, 2.8],
    }, ],
    chart: {
        height: 73,
        type: "bar",
        toolbar: {
            show: false,
        },
        dropShadow: {
            enabled: true,
            enabledOnSeries: undefined,
            top: 24,
            left: 0,
            blur: 6,
            color: "#27AF4D",
            opacity: 0.5,
        },
    },
    plotOptions: {
        bar: {
            borderRadius: 6,
            columnWidth: "30%",
            borderRadiusApplication: "end",
        },
    },
    dataLabels: {
        enabled: false,
    },
    xaxis: {
        show: false,
        categories: [],
        labels: {
            show: false,
        },
        axisBorder: {
            show: false,
        },
        axisTicks: {
            show: false,
        },
        tooltip: {
            enabled: false,
        },
    },
    tooltip: {
        enabled: false
    },
    yaxis: {
        show: false,
        axisBorder: {
            show: false,
        },
        axisTicks: {
            show: false,
        },
    },
    grid: {
        show: false,
        padding: {
            top: -20,
            bottom: -20,
        },
    },
    colors: ["#27AF4D"],
    fill: {
        type: "gradient",
        gradient: {
            shade: "light",
            type: "vertical",
            gradientToColors: ["#27AF4D"],
            opacityFrom: 0.98,
            opacityTo: 0.85,
            stops: [0, 100],
        },
    },
};
var verifiedChart = new ApexCharts(document.querySelector("#verified-chart"), verifiedChart);
verifiedChart.render();
/* Verified Chart */

/* Online Payment Chart */
var onlinePaymentChart = {
    series: [{
        name: "Month",
        data: [0, 15, 15, 10, 10, 20, 20, 25, 25],
    }, ],
    chart: {
        type: "area",
        height: 73,
        toolbar: {
            show: false,
        },
    },
    stroke: {
        curve: "straight",
        width: 3,
    },
    xaxis: {
        type: "category",
        categories: ["jan", "feb", "mar", "apr", "may", "jun", "july", "aug", "sep", "oct"],
        labels: {
            show: false,
        },
        axisBorder: {
            show: false,
        },
        axisTicks: {
            show: false,
        },
        tooltip: {
            enabled: false,
        },
    },
    grid: {
        show: false,
        padding: {
            top: -20,
            bottom: -20,
            left: -10,
            right: -10
        },
    },
    yaxis: {
        show: false,
    },
    dataLabels: {
        enabled: false,
    },
    markers: {
        discrete: [{
            seriesIndex: 0,
            dataPointIndex: 7,
            fillColor: "#27AF4D",
            strokeColor: "#fff",
            size: 6,
            sizeOffset: 3,
        }, ],
        hover: {
            size: 5,
            sizeOffset: 0,
        },
    },
    colors: ["#27AF4D"],
    tooltip: {
        enabled: false
    },
    fill: {
        type: "gradient",
        gradient: {
            shade: "light",
            type: "vertical",
            shadeIntensity: 0.1,
            inverseColors: true,
            opacityFrom: 0.4,
            opacityTo: 0,
            stops: [0, 200],
        },
    },
};
var onlinePaymentChart = new ApexCharts(document.querySelector("#onlinePayment-chart"), onlinePaymentChart);
onlinePaymentChart.render();
/* Online Payment Chart */

/* Review Chart */
var reviewChart = {
    series: [{
        name: "Month",
        data: [10, 20, 20, 15, 15, 10, 10, 20, 20, 10, 10, 20, 20, 15, 15, 20, 20],
    }, ],
    chart: {
        type: "area",
        height: 73,
        toolbar: {
            show: false,
        },
    },
    stroke: {
        curve: "smooth",
        width: 1,
    },
    tooltip: {
        enabled: false
    },
    xaxis: {
        type: "category",
        categories: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "July", "Aug", "Sep", "Oct", "Nov", "Dec", "Jan",
            "Feb", "Mar", "Apr", "May", "Jun", "July"
        ],
        labels: {
            show: false,
        },
        axisBorder: {
            show: false,
        },
        axisTicks: {
            show: false,
        },
        tooltip: {
            enabled: false,
        },
    },
    grid: {
        show: false,
        padding: {
            top: -20,
            bottom: -30,
            left: -5,
            right: -5
        },
    },
    yaxis: {
        show: false,
    },
    tooltip: {
        enabled: false
    },
    dataLabels: {
        enabled: false,
    },
    markers: {
        hover: {
            sizeOffset: 4,
        },
    },
    colors: ["#6a727a"],
    fill: {
        type: "gradient",
        gradient: {
            shade: "light",
            type: "vertical",
            shadeIntensity: 0.1,
            inverseColors: true,
            opacityFrom: 0.5,
            opacityTo: 0,
            stops: [0, 100],
        },
    },
};
var reviewChart = new ApexCharts(document.querySelector("#review-chart"), reviewChart);
reviewChart.render();
/* Review Chart */

/* Customers Chart */
var customersChart = {
    series: [{
        name: "New Clients",
        data: [10, 16, 40, 20, 30, 70, 40, 80, 80, 120, 80, 30, 80, 120, 60, 160, 60, 65, 10],
    }, ],
    chart: {
        type: "area",
        height: 73,
        toolbar: {
            show: false,
        },
    },
    stroke: {
        show: true,
        curve: "smooth",
        lineCap: "butt",
        width: 2,
    },
    xaxis: {
        type: "category",
        labels: {
            show: false,
        },
        axisBorder: {
            show: false,
        },
        axisTicks: {
            show: false,
        },
        tooltip: {
            enabled: false,
        },
    },
    tooltip: {
        enabled: false
    },
    grid: {
        show: false,
        padding: {
            top: -20,
            bottom: -20,
            left: -10,
            right: -10
        },
    },
    yaxis: {
        show: false,
    },
    dataLabels: {
        enabled: false,
    },
    colors: ["#7366FF"],
    fill: {
        type: "gradient",
        gradient: {
            shade: "light",
            type: "vertical",
            shadeIntensity: 1,
            inverseColors: false,
            opacityFrom: 0.8,
            opacityTo: 0,
            stops: [0, 100],
        },
    },
};
var customersChart = new ApexCharts(document.querySelector("#customers-chart"), customersChart);
customersChart.render();
/* Customers Chart */

/* Service Chart */
var serviceChart = {
    series: [{
        name: "Monthly Expense",
        data: [0, 6, 30, 10, 20, 60, 30, 70, 70, 110, 70, 20, 70, 110, 50, 150, 50, 55, 0],
    }, ],
    chart: {
        type: "area",
        height: 68,
        toolbar: {
            show: false,
        },
    },
    stroke: {
        show: true,
        curve: "smooth",
        lineCap: "butt",
        width: 1,
    },
    xaxis: {
        type: "category",
        labels: {
            show: false,
        },
        axisBorder: {
            show: false,
        },
        axisTicks: {
            show: false,
        },
        tooltip: {
            enabled: false,
        },
    },
    grid: {
        show: false,
        padding: {
            top: -30,
            bottom: -35,
            left: -5,
            right: -5
        },
    },
    yaxis: {
        show: false,
    },
    dataLabels: {
        enabled: false,
    },
    tooltip: {
        enabled: false
    },
    colors: ["#AD46FF"],
    fill: {
        type: "gradient",
        gradient: {
            shade: "light",
            type: "vertical",
            shadeIntensity: 1,
            inverseColors: true,
            opacityFrom: 0.8,
            opacityTo: 0,
            stops: [0, 100],
        },
    },
};
var serviceChart = new ApexCharts(document.querySelector("#service-chart"), serviceChart);
serviceChart.render();
/* Service Chart */


/* Revenue Chart */

/* Revenue Chart */