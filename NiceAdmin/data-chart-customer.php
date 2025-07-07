<!DOCTYPE html>
<html>
<head>
<title>Sale Report</title>

<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
<script src="https://canvasjs.com/assets/script/canvasjs.min.js"></script>

</head>
<body>
    <div id="chartContainer3" style="width: 800px; height: 380px;">
        
    </div>

    <script type="text/javascript">
        $(document).ready(function () {

           // $.getJSON("pages/charts/data.php", function (result) {
              $.getJSON("data-customer.php", function (result) {
                var chart = new CanvasJS.Chart("chartContainer3", {
                    animationEnabled: true,
                    exportEnabled: true,
                    theme: "light1",
                    //backgroundColor: "#D5F5E3",
                    title:{
                        text: "สรุปยอดสั่งซื้อสินค้า ปี 2024"
                    },
                    axisY: {
		                    title: "จำนวนเงิน"
	                  },
                    axisX: {
		                    title: "เดือน"
	                  },
                    data: [
                        {   type: "column",
                            dataPoints: result
                        }
                    ]
                });

                chart.render();
            });
        });
    </script>

</body>
</html>