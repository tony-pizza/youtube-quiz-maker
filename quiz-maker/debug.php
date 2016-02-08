<!doctype html>
<html>
<head>
<title>Debug</title>
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
include 'quiz.php';

$quiz = new Quiz();

function show_error($msg) {
  echo '<p class="error">' . $msg . '</p>';
}

if (json_decode(file_get_contents(QUIZ_DATA_PATH), true) === null) {
  show_error("unable to read JSON file");
  exit;
}

if ($quiz->get_questions_data() === null) {
  show_error('"questions" key missing from JSON');
  exit;
}

if ($quiz->get_results_data() === null) {
  show_error('"questions" key missing from JSON');
  exit;
}

foreach (array_keys($quiz->get_questions_data()) as $question) {
  if ($quiz->get_question_url($question) === null) {
    show_error('missing URL for question "' . $question . '"');
  }

  $answers = $quiz->get_answers_data($question);

  if (!$answers) {
    show_error('missing answers for question "' . $question . '"');
  }

  foreach (array_keys($answers) as $answer) {
    $score_data = $quiz->get_answer_data($question, $answer);
    if ($score_data === null) {
      show_error('missing scoring data for answer "' . $answer . '" in  question "' . $question . '"');
    }

    foreach ($score_data as $result => $weight) {
      if (!$quiz->get_results_data($result)) {
        show_error('result "' . $result . '" in scoring data for answer "' . $answer . '" in  question "' . $question . '" does not appear in final results');
      }
    }
  }
}

foreach (array_keys($quiz->get_results_data()) as $result) {
  if (!$quiz->get_result_url($result)) {
    show_error('result "' . $result . '" is missing a URL');
  }
}

?>

<h2>CURRENT COOKIE DATA</h2>
<?php

$cookie = $quiz->get_cookie();
if (!$cookie) {
  echo 'Cookie is null';
} else {
  echo '<ul>';
  foreach ($cookie as $question => $answer) {
    echo '<li>"' . $question . '": "' . $answer . '"</li>';
  }
  echo '</ul>';
}

?>

<h2>QUESTIONS</h2>
<ul>
<?php

$quiz_base_url = dirname("http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");

foreach (array_keys($quiz->get_questions_data()) as $question) {
  echo '<li>';
    echo '"' . $question . '":';
    echo '<ul>';
      echo '<li>url: <a href="' . $quiz->get_question_url($question) . '">' . $quiz->get_question_url($question) . '</a></li>';
      echo '<li>';
        echo 'answers:';
        echo '<ul>';
          foreach (array_keys($quiz->get_answers_data($question)) as $answer) {
            $url = $quiz_base_url . '?q=' . $question . '&a=' . $answer;
            echo '<li>"' . $answer . '": <a href="' . $url . '">' . $url . '</a></li>';
          }
        echo '</ul>';
      echo '</li>';
    echo '</ul>';
  echo '</li>';
}

?>
</ul>

<h2>RESULTS</h2>
<ul>
<?php

foreach (array_keys($quiz->get_results_data()) as $result) {
  $url = $quiz->get_result_url($result);
  echo '<li>';
    echo '"' . $result . '": <a href="' . $url . '">' . $url . '</a>';
  echo '</li>';
}

?>
</ul>

</body>
</html>
