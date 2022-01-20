<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

// use Illuminate\Support\Collection;

class QADashboard extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'question:dashboard';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Simple Question and Answer forum.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Create a new question
     */
    public function create_question()
    {
        // Creating the new Question.
        echo "Creating a new Question \n";
        $question = "";

        while ($question == "") {
            $question = $this->ask('Please enter a Question:');
        }
        $answer = "";
        while ($answer == "") {
            $answer = $this->ask('Please enter an Answer:');
        }

        DB::table('question_answer')->insert([
            'question' => $question,
            'answer' => $answer,
        ]);
    }

    /**
     * List all the questions
     */
    public function list_questions()
    {
        // Listing all the Questions.
        echo "Listing all Questions: \n";

        $lists = DB::table('question_answer')->pluck('question');
        $count = 0;
        foreach ($lists as $question) {
            $count++;
            echo $count . " " . $question . "\n";
        }

    }

    /**
     * Practice the Question Answer
     */
    public function practice_questions()
    {
        echo "Practice: \n";
        echo "Enter the Question id or type exit to leave. \n\n";

        $question_id = "";

        // While the user has not entered exit, show all the questions.
        while ($question_id != 'exit') {
            $lists = DB::table('question_answer')->get();
            $count = 0;
            $correct_answer = 0;
            foreach ($lists as $question) {
                $count++;
                if ($question->user_answer == "") {
                    $answer_status = "Not Answered";
                } else if ($question->answer == $question->user_answer) {
                    $correct_answer++;
                    $answer_status = "Correct";
                } else {
                    $answer_status = "Incorrect";
                }
                // Print Question with correct answer status.
                echo $question->id . " " . $question->question . ":" . $answer_status . "\n";
            }

            // Show progress.
            $progress = (100 * $correct_answer) / $count;
            echo "\n Progress: " . $progress . "\n";

            // Get the user answer.
            $question_id = $this->ask('Enter the Question id or type exit to leave.');
            if ($question_id != "exit") {
                $question = $lists->firstWhere("id", "=", $question_id);
                if ($question->answer == $question->user_answer) {
                    echo "Already answered the Question. \n";
                } else {
                    $answer = "";
                    while ($answer == "") {
                        $answer = $this->ask($question->question);
                    }
                    // store user's answer in the database.
                    DB::table('question_answer')
                        ->where('id', $question_id)
                        ->update(['user_answer' => $answer]);
                }
            }
        }
    }

    /**
     * Show Answer Status
     */
    public function stats_questions()
    {
        echo "Stats: \n";

        $lists = DB::table('question_answer')->get();
        $count = 0;
        $totalAnswered = 0;
        $correct_answer = 0;
        foreach ($lists as $question) {
            $count++;
            if ($question->answer == $question->user_answer) {
                $correct_answer++;
            }
            if ($question->user_answer) {
                $totalAnswered++;
            }
        }
        // Get total number of questions
        echo "The total number of questions: " . $count . "\n";

        $percentAnswered = (100 * $totalAnswered) / $count;
        echo "% of questions that have an answer:" . $percentAnswered . "% \n";

        // Get % of questions that have a correct answer.

        $percentCorrect = (100 * $correct_answer) / $count;

        echo "% of questions that have a correct answer:" . $percentCorrect . "% \n";
    }

    /**
     * Reset All the Answers
     */
    public function reset_progress()
    {
        if ($this->confirm('Do you wish to reset the progress? [yes|no]')) {
            // Reset all the answers
            DB::table('question_answer')->update(array('user_answer' => null));
            echo "All values have been reset. You can start Fresh \n";
        }
    }

    /**
     * Execute the console command.
     * Do appropriate actions if valid input is provided
     * else exit
     */
    public function handle()
    {

        // Defining the user actions
        $menu_array = array(
            "1" => "create_question",
            "2" => "list_questions",
            "3" => "practice_questions",
            "4" => "stats_questions",
            "5" => "reset_progress",
        );

        do {
            $option = "";
            // Show the dashboard Options
            echo "\n Please select an Option to continue... \n";
            echo "1. Create a Question \n";
            echo "2. List all Questions \n";
            echo "3. Practice \n";
            echo "4. Stats \n";
            echo "5. Reset \n";
            echo "6. Exit \n";

            // Get the user input.
            $option = $this->ask('What do you want to do?');

            // Perform certain action according to the selected option.
            if (array_key_exists($option, $menu_array)) {
                $this->{$menu_array[$option]}();
            } else if ($option == "6") {
                echo "Exiting...";
            } else {
                echo "Invalid option! Exiting...";
            }
        } while (array_key_exists($option, $menu_array));

    }
}
