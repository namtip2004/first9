<!DOCTYPE html>
<html>
<head>
<title>Sale Report</title>

<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
<script src="https://canvasjs.com/assets/script/canvasjs.min.js"></script>

</head>
<body>
<div id="chartContainer1" style="width: 800px; height: 380px;">
        
    </div>

    <script type="text/javascript">
        $(document).ready(function () {

           // $.getJSON("pages/charts/data.php", function (result) {
              $.getJSON("data-best-sale.php", function (result) {
                var chart = new CanvasJS.Chart("chartContainer1", {
                    animationEnabled: true,
                    exportEnabled: true,
                    theme: "light1",
                    //backgroundColor: "#D5F5E3",
                    title:{
                        text: "สรุปยอดขายจำนวนสินค้า ปี 2024"
                    },
                    axisY: {
		                    title: "จำนวนสินค้าที่ขายได้"
	                  },
                    axisX: {
		                    title: "สินค้า"
	                  },
                    data: [
                        {   type: "column",
                            dataPoints: result
                        }
                    ]
                }); chart.render();
            });
        });
    </script>

</body>
</html>