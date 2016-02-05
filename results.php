<!doctype html>
<html>
<head>
<title>Results</title>
<meta name=viewport content="width=device-width,initial-scale=1">
<style type="text/css">
body {
  font-family: monospace;
  line-height: 1.7;
  margin: 20px;
}
ul {
  padding-left: 0px;
  list-style-type: none;
}
ul ul {
  padding-left: 20px;
}
.error {
  background: lightpink;
  padding: 10px 20px;
}
</style>
</head>
<body>

<?php

include 'config.php';

$data = Array();

$csv = fopen(RESULTS_CSV_PATH, 'r');

if ($csv === FALSE) {
  error_log('unable to open results CSV at ' . RESULTS_CSV_PATH);
  exit;
}

$header = fgetcsv($csv);

$total = 0;
while (($row = fgetcsv($csv)) !== FALSE) {
  // assume first column is time, start $i at 1
  for ($i = 1; $i < count($row); $i++) {
    $data[$header[$i]][$row[$i]]++;
  }
  $total++;
}

fclose($csv);

echo '<h2>Total completed quizzes: ' . $total . '</h2>';

echo '<ul>';
foreach ($data as $name => $counts) {
  echo '<li>' . $name;
  echo '<ul>';
  foreach ($counts as $entry => $count) {
    echo '<li>' . $entry . ': ' . $count . ' (' . number_format($count / $total * 100, 1) . '%)</li>';
  }
  echo '</ul>';
  echo '</li>'; }
echo '</ul>';

?>

</body>
</html>
