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

### Just YouTube?

While this project was created to power a quiz made up of YouTube videos, technically the question and result URLs can be anything (not just YouTube videos). Still, this project is really most useful when your quiz content lives on a third-party domain you don't have full control over, like YouTube (unless you happen to work there). Also, this project assumes that each question and result lives at separate URLs, like YouTube videos. But there's no reason that this project wouldn't work with say, Tumblr posts or Vimeo videos, as long as there's a place to put the answer links.

## Make your own quiz

### Requirements

 - A server running PHP reachable via the internet

### Setup

 - Copy all the [Quiz Maker files](https://github.com/peeinears/youtube-quiz-maker/blob/master/quiz-maker) to your server
 - Fill in [quiz.json](https://github.com/peeinears/youtube-quiz-maker/blob/master/quiz-maker/quiz.json) with the metadata for your quiz (more on that below)
 - Change `COOKIE_NAME` in [config.php](https://github.com/peeinears/youtube-quiz-maker/blob/master/quiz-maker/config.php) to something unique
 - Navigate to /debug.php on your site to make sure everything's setup correctly
 - Copy answer URLs from /debug.php to use in corresponding answer links or annotations

### Quiz Metadata

[The quiz metadata file](https://github.com/peeinears/youtube-quiz-maker/blob/master/quiz-maker/quiz.json) is a [JSON](http://www.json.org/) file that gives the Quiz Maker code all the information about _your_ quiz.

It includes:

 - Question keys
 - Question URLs
 - Answer keys
 - How each answer weighs in on the final result calculation
 - Result keys
 - Result URLs

#### Format

The quiz metadata JSON contains two main sub-objects: `"questions"` and `"results"`. The `"questions"` object contains the URL and answers for each question. Each answer also has data about how it affects the final result calculation (more on that below). The `"results"` object contains the URL of each result.

Here's an example for an "Are you a cat or a dog?" quiz:

```json
{

  "questions": {

    "what-do-you-say": {
      "url": "http://[question 1 URL]",
      "answers": {
        "meow": { "cat": 3 },
        "woof": { "dog": 3 },
        "hello": { "cat": 1, "dog": 1 },
        "nothing": { "cat": 1, "dog": -1 }
      }
    },

    "number-of-lives": {
      "url": "http://[question 2 URL]",
      "answers": {
        "one": { "dog": 3 },
        "nine": { "cat": 3 }
      }
    }

  },

  "results": {
    "cat": "http://[URL for cat result]",
    "dog": "http://[URL for dog result]"
  }

}
```

#### Question / Answer / Result Keys

Keys are just strings that are used to uniquely identify questions, answers and results in your quiz. Keys should be short yet descriptive of the thing they're representing. Keys should be lowercase and contain no spaces or punctuation except for hyphens. For example, for a question that asks, "How tall are you?", you might name that key "how-tall-are-you" or better, simply just "height".

#### Weights and Final Result Calculation

Each result has a __score__. The scores are all 0 at the start of the quiz. In the quiz metadata, each answer has information about how it will affect the results' scores if answered, or __weights__. At the end of the quiz, each answer's weights are added to the result scores, and the result with the highest score is chosen to be the final result.

To continue our "Cat or Dog?" example, let's say we chose "nothing" for the first question and "one" for the second. The weights for "nothing" are `{ "cat": 1, "dog": -1 }`, which means we add `1` to the score for result "cat" and subtract `1` from the score for result "dog". Now "cat" has `1` and "dog" has `-1`.  Then we add the weights for answer "one" in the second question, which are just `{ "dog": 3 }`. We add `3` to "dog" to give us `2` and leave "cat" alone at `1`. So "dog" wins 2-1, the final result is Dog.

## Authors

Idea, guidance and support from [Mike Rugnetta](https://twitter.com/mikerugnetta) and the folks at [PBS Idea Channel](https://www.youtube.com/user/pbsideachannel).

Code by Ian Pearce / [@peeinears](https://github.com/peeinears).

## License

MIT
