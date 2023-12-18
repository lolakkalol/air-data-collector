<?php
function console_log($output, $with_script_tags = true) {
	$js_code = 'console.log(' . json_encode($output, JSON_HEX_TAG) .
	');';
	if ($with_script_tags) {
	$js_code = '<script>' . $js_code . '</script>';
	}
	echo $js_code;
}
?>

<?php
	if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on') {
		header("HTTP/1.0 404 Not Found");
		exit;
	}

	$ini_auth = parse_ini_file("../auth.ini", true);

	// We are authenticated
	// Open MySQL connection
	$servername    = $ini_auth['sql_auth']['servername'];
	$username      = $ini_auth['sql_auth']['username'];
	$password      = $ini_auth['sql_auth']['password'];
	$database_name = $ini_auth['sql_auth']['database_name'];
	$port          = $ini_auth['sql_auth']['port'];
	
	// Create MySQL connection fom PHP to MySQL server
	$connection = mysqli_connect(
								$servername, $username,
								$password, $database_name,
								$port
								);

	// Check connection
	if (mysqli_connect_errno()) {
		die("MySQL connection failed: " . $connection->connect_error);
	}

	$date = date("Y-m-d H:i:s", time() - 86400);	

	// Get DATA
    //$Location = mysqli_real_escape_string($connection, $_POST['Location']);
    //$sql = 'SELECT CO2.Timestamp, CO2.PPM FROM CO2 WHERE CO2.Timestamp > "2023-09-13 08:01:51" AND CO2.Timestamp < "2023-09-14 08:01:51" AND CO2.Sensor = "MHZ19B"';
	$sql = 'SELECT CO2.Timestamp, CO2.PPM FROM CO2 WHERE CO2.Timestamp > "' . $date . '" AND CO2.Sensor = "MHZ19B"';
    $result = $connection->query($sql);

	$MHZ19B = array();
	while($row = mysqli_fetch_assoc($result)) {
		$row["y"] = $row["PPM"];
		unset($row["PPM"]);
		$row["x"] = strtotime($row["Timestamp"])*1000;
		unset($row["Timestamp"]);
        $MHZ19B[] = $row;
    }

	$sql = 'SELECT CO2.Timestamp, CO2.PPM FROM CO2 WHERE CO2.Timestamp > "' . $date . '" AND CO2.Sensor = "ENS160"';
    $result = $connection->query($sql);

	$ENS160 = array();
	while($row = mysqli_fetch_assoc($result)) {
		$row["y"] = $row["PPM"];
		unset($row["PPM"]);
		$row["x"] = strtotime($row["Timestamp"])*1000;
		unset($row["Timestamp"]);
        $ENS160[] = $row;
    }

	$sql = 'SELECT Temperature.Timestamp, Temperature.Celsius FROM Temperature WHERE Temperature.Timestamp > "' . $date . '" AND Temperature.Sensor = "AHT2x"';
    $result = $connection->query($sql);

	$AHT2x = array();
	while($row = mysqli_fetch_assoc($result)) {
		$row["y"] = $row["Celsius"];
		unset($row["Celsius"]);
		$row["x"] = strtotime($row["Timestamp"])*1000;
		unset($row["Timestamp"]);
        $AHT2x[] = $row;
    }

	//console_log($MHZ19B[0]);
     
    ?>

    <!DOCTYPE HTML>
    <html>
    <head>
	<link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
	<link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
	<link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
	<link rel="manifest" href="/site.webmanifest">
	<link rel="mask-icon" href="/safari-pinned-tab.svg" color="#5bbad5">
	<meta name="msapplication-TileColor" content="#da532c">
	<meta name="theme-color" content="#ffffff">
    <script>
    window.onload = function () {
     
    var CO2Chart = new CanvasJS.Chart("CO2Chart", {
		theme: "dark2", // "light1", "light2", "dark1", "dark2"
		zoomEnabled: true,
    	animationEnabled: true,
    	title:{
    		text: "CO2 in PPM in the last 24h"
    	},
    	axisY: {
    		title: "CO2 PPM",
    		suffix: "ppm",
			crosshair: {
			enabled: true
			}
    	},
		axisX: {
			valueFormatString: "DDD HH:mm",
			crosshair: {
			enabled: true,
			snapToDataPoint: true
			}
		},
		toolTip:{
		shared:true
		}, 
		legend:{
			cursor:"pointer",
			verticalAlign: "bottom",
			horizontalAlign: "left",
			dockInsidePlotArea: true,
			itemclick: toogleDataSeries
		},
    	data: [{
			showInLegend: true,
			name: "MHZ19B",
			markerType: "square",
    		type: "line",
    		markerSize: 5,
    		xValueFormatString: "YYYY-MM-DD HH:mm:ss",
    		yValueFormatString: "# ppm",
    		xValueType: "dateTime",
    		dataPoints: <?php echo json_encode($MHZ19B, JSON_NUMERIC_CHECK); ?>
    	},
		{
			type: "line",
			showInLegend: true,
			lineDashType: "dash",
			name: "ENS160",
    		type: "line",
    		markerSize: 5,
    		xValueFormatString: "YYYY-MM-DD HH:mm:ss",
    		yValueFormatString: "# ppm",
    		xValueType: "dateTime",
    		dataPoints: <?php echo json_encode($ENS160, JSON_NUMERIC_CHECK); ?>
    	}]
    });

    CO2Chart.render();

	var TempChart = new CanvasJS.Chart("TemperatureChart", {
		theme: "dark2", // "light1", "light2", "dark1", "dark2"
		zoomEnabled: true,
    	animationEnabled: true,
    	title:{
    		text: "Temperature in the last 24h"
    	},
    	axisY: {
    		title: "Celcius",
    		suffix: "\xB0C",
			crosshair: {
			enabled: true
			}
    	},
		axisX: {
			valueFormatString: "DDD HH:mm",
			crosshair: {
			enabled: true,
			snapToDataPoint: true
			}
		},
		toolTip:{
		shared:true
		}, 
		legend:{
			cursor:"pointer",
			verticalAlign: "bottom",
			horizontalAlign: "left",
			dockInsidePlotArea: true,
			itemclick: toogleDataSeries
		},
    	data: [{
			showInLegend: true,
			name: "AHT2x",
			markerType: "square",
    		type: "line",
    		markerSize: 5,
    		xValueFormatString: "YYYY-MM-DD HH:mm:ss",
    		yValueFormatString: "#.# \xB0C",
    		xValueType: "dateTime",
    		dataPoints: <?php echo json_encode($AHT2x, JSON_NUMERIC_CHECK); ?>
    	}]
    });

	TempChart.render();

	function toogleDataSeries(e){
	if (typeof(e.dataSeries.visible) === "undefined" || e.dataSeries.visible) {
		e.dataSeries.visible = false;
	} else{
		e.dataSeries.visible = true;
	}
	CO2Chart.render();
	}
     
    }
    </script>
    </head>
    <body>
    <div id="CO2Chart" style="height: 370px; width: 100%;"></div>
	<div id="TemperatureChart" style="height: 370px; width: 100%;"></div>
    <script src="https://cdn.canvasjs.com/canvasjs.min.js"></script>
    </body>
    </html>