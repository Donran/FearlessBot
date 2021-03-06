<?php
require_once "config.php";
$db = new mysqli(DB_HOST,DB_USERNAME,DB_PASSWORD,DB_NAME);
$db->set_charset("utf8mb4");

$bots = (isset($_GET['includebots'])) ? '' : "AND channel != '132026417725702145'";

$query = $db->prepare("SELECT MONTH(old_messages.date) month, YEAR(old_messages.date) year, COUNT(*)
FROM old_messages
WHERE server=? AND author=? $bots
GROUP BY year, month");
$query->bind_param('ss', $_GET['server'], $_GET['user']);
$query->execute();
$query->bind_result($month, $year, $count);

$counts = array();
while ($query->fetch()) {
    $counts[$month."/".$year] += $count;
}

$query2 = $db->prepare("SELECT MONTH(messages.date) month, YEAR(messages.date) year, COUNT(*)
FROM messages
WHERE server=? AND author=? $bots
GROUP BY year, month");
$query2->bind_param('ss', $_GET['server'], $_GET['user']);
$query2->execute();
$query2->bind_result($month, $year, $count);

while ($query2->fetch()) {
    $counts[$month."/".$year] += $count;
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Activity Report</title>
    <!--Load the AJAX API-->
    <script type="text/javascript" src="https://www.google.com/jsapi"></script>
    <script type="text/javascript">

        // Load the Visualization API and the piechart package.
        google.load('visualization', '1.0', {'packages':['corechart','bar']});

        // Set a callback to run when the Google Visualization API is loaded.
        google.setOnLoadCallback(drawChart);

        // Callback that creates and populates a data table,
        // instantiates the pie chart, passes in the data and
        // draws it.
        function drawChart() {

            // Create the data table.
            var data = new google.visualization.DataTable();
            data.addColumn('string', 'Month');
            data.addColumn('number', 'Messages');
            data.addRows([
                <?php
                foreach ($counts as $month => $amount) {
                    echo "['$month', $amount],";
                }
                ?>
            ]);

            // Set chart options
            var options = {'title':'Messages By Month <?php echo ($_GET['server']=='115332333745340416' && !isset($_GET['includebots'])) ? ' (excluding bots)' : ''; ?>'};

            // Instantiate and draw our chart, passing in some options.
            var chart = new google.visualization.ColumnChart(document.getElementById('chart_div'));
            chart.draw(data, options);
        }
    </script>
</head>

<body>
<!--Div that will hold the pie chart-->
<div id="chart_div" style="width: 800px;height:400px"></div>
</body>
</html>
