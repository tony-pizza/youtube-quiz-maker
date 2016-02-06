YouTube Quiz Maker
==================

YouTube Quiz Maker is some PHP code that helps you create personality quizzes administered entirely through YouTube.

It was originally built for the lovely folks at [PBS Idea Channel](https://www.youtube.com/user/pbsideachannel) to use for their episode on personality quizzes. [Check it out!](https://www.youtube.com/watch?v=rI4kdTFTZfc)

### Whateven is a personality quiz administered entirely through YouTube?

TL;DR: Just take the Idea Channel quiz [here](https://www.youtube.com/watch?v=rI4kdTFTZfc) and you will see!

A personality quiz is a series of multiple-choice questions that, when answered, leads to one result (of many possible results) determined by the combination of answers that were chosen. (For an example of the format, see the [Which Rihanna Album Are You?](http://www.buzzfeed.com/kelleydunlap/which-rihanna-album-are-you) quiz on BuzzFeed.)

The YouTube part means that each question is posed in a YouTube video where you can express your answer by clicking video annotations or links in the video description. After each answer, you're immediately forwarded to the following question (or your final result if you've answered all questions). Each possible final result can be its own video as well.

### How does it work?

When you choose each answer by clicking an annotation or link, you're briefly taken to a site running the YouTube Quiz Maker code. Each answer URL is unique and contains a key for the answer you've chosen and for the question you're answering. The quiz code will record your answer in your cookies and immediately redirect you to the following question. If you've reached the end of the quiz, your previous answers will be retrieved from your cookies and all of your answers will be used to calculate your final result, to which you'll be immediately redirected.

## Make your own quiz

### Requirements

 - A server running PHP reachable via the internet

### Setup

 - Put all the files on your server
 - Fill in [quiz.json](https://github.com/peeinears/youtube-quiz-maker/blob/master/quiz.json) with the metadata for your quiz (more on that below)
 - Change `COOKIE_NAME` in [config.php](https://github.com/peeinears/youtube-quiz-maker/blob/master/config.php) to something unique
 - Navigate to /debug.php on your site to make sure everything's setup correctly
 - Copy answer URLs from /debug.php to use in corresponding answer links or annotations
