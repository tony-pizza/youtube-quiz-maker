<?php

include 'config.php';
include 'quiz.php';

$quiz = new Quiz();
$quiz->process($_GET[QUESTION_QUERY_PARAM], $_GET[ANSWER_QUERY_PARAM]);

?>
