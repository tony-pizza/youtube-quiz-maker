<?php

// The Quiz class contains all the business logic

class Quiz {

  public $question;
  public $answer;
  public $result;

  // The main method to call to process a new answer
  public function process($question, $answer) {
    $this->question = $question;
    $this->answer = $answer;

    $this->validate_answer();
    $this->record_answer();
    $this->handle_redirect();
  }


// Helper methods for retrieving quiz config data

  public function get_quiz_data() {
    // Only parse data JSON once, store in property for reuse
    if ($this->_quiz_data === null) {
      $this->_quiz_data = json_decode(file_get_contents(QUIZ_DATA_PATH), true);

      if ($this->_quiz_data === null) {
        $this->log_error_and_exit("unable to read JSON file");
      }
    }

    return $this->_quiz_data;
  }

  public function get_questions_data() {
    return $this->get_quiz_data()['questions'];
  }

  public function get_results_data() {
    return $this->get_quiz_data()['results'];
  }

  public function get_result_url($result) {
    return $this->get_results_data()[$result];
  }

  public function get_question_data($question) {
    return $this->get_questions_data()[$question];
  }

  public function get_question_url($question) {
    return $this->get_question_data($question)['url'];
  }

  public function get_answers_data($question) {
    return $this->get_question_data($question)['answers'];
  }

  public function get_answer_data($question, $answer) {
    return $this->get_answers_data($question)[$answer];
  }


  public function validate_answer() {
    if ($this->question === null) {
      $this->log_error_and_exit('missing question');
    }

    if ($this->answer === null) {
      $this->log_error_and_exit('missing answer');
    }

    // If there's no answer data for the question/answer pair, log an error and exit
    if ($this->get_answer_data($this->question, $this->answer) === null) {
      $this->log_error_and_exit('unable to find answer with key "' . $this->answer . '" for question with key "' . $this->question . '"');
    }
  }


// Cookie stuff
 
  // We use the cookie to keep track of a user's answers during quiz-taking.
  // The cookie is a serialized JSON object of question/answer data representing
  //   a user's answers thus far (ex: { "fav-color": "blue", "fav-animal": "goat" }).

  // Helper method for fetching and deserializing the cookie
  // The cookie is cached in a local $_cookie property
  public function get_cookie() {
    if ($this->_cookie === null) {
      if ($_COOKIE[COOKIE_NAME]) {
        // Deserialize cookie JSON
        if (get_magic_quotes_gpc()) {
          $_COOKIE[COOKIE_NAME] = stripslashes($_COOKIE[COOKIE_NAME]);
        }
        $this->_cookie = json_decode($_COOKIE[COOKIE_NAME], true);
      } else {
        // No cookie yet, initialize one
        $this->_cookie = Array();
      }
    }

    return $this->_cookie;
  }

  // Helper method for serializing and setting the cookie
  public function set_cookie($cookie) {
    $this->_cookie = $cookie;
    setcookie(COOKIE_NAME, json_encode($this->_cookie), time() + COOKIE_TTL);
  }

  // Adds the current question/answer to the cookie
  public function record_answer() {
    $cookie = $this->get_cookie();

    // Add answer to cookie data
    $cookie[$this->question] = $this->answer;

    $this->set_cookie($cookie);
  }


  // Redirects to either next question or final result
  public function handle_redirect() {
    $next_question_key = $this->get_next_question();
    if ($next_question_key !== null) {
      $this->redirect_to_question($next_question_key);
    } else {
      // If there's no next question, assume quiz is complete
      $this->finish_quiz();
    }
  }

  // Finish quiz routine: determine result, log data, redirect to result
  public function finish_quiz() {
    $this->score_answer();
    $this->record_result();
    $this->redirect_to_result();
  }

  // Add up all the result weights for the answers and calculate result
  public function score_answer() {
    $score = Array();
    $cookie = $this->get_cookie();
    foreach ($cookie as $question => $answer) {
      foreach ($this->get_answer_data($question, $answer) as $result => $weight) {
        $score[$result] += $weight;
      }
    }

    // Choose the result with the highest score
    // If tie, just pick first one
    // Set in $result property
    $this->result = array_keys($score, max($score))[0];
  }

  // Records a user's answers and result in a CSV
  public function record_result() {
    $question_keys = array_keys($this->get_questions_data());

    // If CSV file doesn't exist, create it and add header row
    if (!file_exists(RESULTS_CSV_PATH)) {
      $header = array_merge(['time'], $question_keys);
      array_push($header, 'result');
      $this->add_row_to_results_csv($header);
    }

    $row = [gmdate('Y-m-d h:i:s')];
    $cookie = $this->get_cookie();
    foreach ($question_keys as $question_key) {
      array_push($row, $cookie[$question_key]);
    }
    array_push($row, $this->result);
    $this->add_row_to_results_csv($row);
  }

  // Redirect to URL for user's result
  public function redirect_to_result() {
    $url = $this->get_result_url($this->result);

    if (!$url) {
      $this->log_error_and_exit('result URL missing for key "' . $this->result . '"');
    }

    $this->redirect_to($url);
  }

  // Redirect to URL for a given question
  public function redirect_to_question($question) {
    $url = $this->get_question_url($question);
    if ($url === null) {
      $this->log_error_and_exit('question with key "' . $question . '" has no URL');
    }
    $this->redirect_to($url);
  }

  // Helper method for redirecting
  public function redirect_to($url) {
    header("Location: " . $url);
    exit;
  }

  // Get the question following the one just answered
  // Null if quiz is complete
  public function get_next_question() {
    $questions = $this->get_questions_data();
    reset($questions);
    do {
      if (key($questions) === $this->question) {
        next($questions);
        return key($questions);
      }
    } while (next($questions));
    return null;
  }

  // Helper method for fatal errors
  public function log_error_and_exit($error) {
    $this->log_error($error);
    exit;
  }

  // Helper method for formatting useful error messages
  public function log_error($error) {
    error_log('(' . $_SERVER['REMOTE_ADDR'] . ' - '  . $_SERVER['REQUEST_URI'] . '): ' . $error . ' -- COOKIE: ' . $_COOKIE[COOKIE_NAME]);
  }

  // Helper method for safely adding a row to the results CSV
  // Locks the file temporarily to prevent simultaneous writes
  public function add_row_to_results_csv($row) {
    $csv = fopen(RESULTS_CSV_PATH, 'a');
    if (flock($csv, LOCK_EX)) {
      fputcsv($csv, $row);
      flock($csv, LOCK_UN);
    } else {
      $this->log_error('unable to get file lock for results CSV');
    }
    fclose($csv);
  }

}

?>
