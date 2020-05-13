<?php

namespace Grooped\API\stack\Stack;

use Grooped\API\exceptions\Exception;
use Grooped\API\httptunnel\Tunnel;
use Grooped\API\persistence\Tokens\Tokens;
use Moycroft\API\internal\mysql\Connect;
use function Moycroft\API\helper\IMPORT;
use function Moycroft\API\internal\GUID\GUID;
use function Moycroft\API\internal\reporting\report\__error;
use function Moycroft\API\internal\reporting\report\__info;
use function Moycroft\API\internal\reporting\report\__returnHTTPException;

IMPORT("API.grooped.exceptions.*");
IMPORT("API.internal.reporting.*");
IMPORT("API.grooped.persistence.*");
IMPORT("API.internal.*");

require_once __DIR__ . "/../exceptions/exceptions.php";

class Stack
{
    //<editor-fold desc="Object Variables">
    public
        $stack_id,
        $temporary_stack_id,
        $game_id,
        $creator_persistence_id,
        $title,
        $description,
        $stack_data,
        $players_max,
        $players_min,
        $created_on,
        $creator_name,
        $creator_account_id,
        $search_terms,
        $verified,
        $content,
        $carbon;
    public $nsfw = false;
    //</editor-fold>

    // Static Functions

    /**
     * Creates a set
     * @param $title string Title of the set
     * @param $description string Description of the set
     * @param $player_min int Minimum player count
     * @param $player_max int Maximum player count
     * @param $questions array 2D array of rounds and questions. Format: [ 0 => ["Question 1 @ Round 0"]]
     * @param array $search_tags Array of searchable strings to find stack
     * @return bool If creation was successful
     * @throws \InvalidStack Throws if array structure fails
     * @throws \UnknownError Throws if, well, its a fuckin unknown error??? Yuh
     * @throws \UnregisteredAction Throws if persistence token does not exist or exists without name
     */
    static function create($title, $description, $player_min, $player_max, $questions, $search_tags=[]){
        //Start stack creation
        $nsfw = false;

        // Step 1:
        // Verify if the user is valid
        if (!Tokens::isRegistered() && !Tokens::getName()){
            throw new \UnregisteredAction(
                "User must have a named persistence token to use create stacks.",
                "Sorry, but in order to create a stack you must both allow cookies and register with a name.",
                "Looks like you're tryna ghost write a stack!");
        }


        // Step 2:
        // Find problems in set and verify structure
        $result = self::analyse_question_set($questions);

        // If the stack is not usable, throw an exception with a friendly message and a reason
        if (!$result['is_usable']){
            throw new \InvalidStack($result['reason'], $result['friendly'], "There was a problem with this set");
        }


        // Step 3:
        // Clean HTML and XML tags and detect NFSW in questions
        $current_round = 1;
        $current_question = 1;

        //Iterate through rounds
        foreach ($questions as $set){

            // Iterate through questions in rounds
            foreach ($set as $question){
                //<editor-fold desc="Logging">
                __info("[$current_round ROUND / $current_question QUESTION] Checking question for NSFW content.");
                //</editor-fold>
                //NFSW Detection
                if (self::is_string_NSFW($question)) {
                    $nsfw = true;
                    //<editor-fold desc="Logging">
                    __info("[$current_round ROUND / $current_question QUESTION] ... NSFW content found. Flagging set.", "warning");
                    //</editor-fold>
                }
                //<editor-fold desc="Logging">
                __info("[$current_round ROUND / $current_question QUESTION] Scrubbing strings...");
                //</editor-fold>
                //Tag Scrubbing
                $question = strip_tags($question);
                $current_question++;
            }

            $current_round++;
        }


        // Step 3:
        // Clean and prep search string for %LIKE% comparison
        $search_string = "";
        if ($search_tags != []){
            foreach ($search_tags as $search_tag) {
                // Clean and glue tags with comma
                $search_string .= strtolower(preg_replace("/[^A-Za-z0-9 ]/", '', $search_tag)) . ", ";
            }
        }

        // Step 4:
        // Encode " ' " with "|??|" to prevent SQL Injection
        //<editor-fold desc="Logging">
        __info("Encoding strings for submission to database...");
        //</editor-fold>
        foreach ($questions as &$set){
            foreach ($set as &$question){
                $question = str_replace("'", "|??|", $question);
            }
        }


        // Step 5:
        // Check for NSFW in title and description. If found, the set will not be flagged, but the words will be replaced.
        //<editor-fold desc="Logging">
        __info("Checking title and description for NSFW content...");
        //</editor-fold>
        if (self::is_string_NSFW($title)){
            $title = self::is_string_NSFW($title, true)['cleaned_string'];
            //<editor-fold desc="Logging">
            __info("... NSFW content found in title. Replacing word(s).", "warning");
            //</editor-fold>
        }
        if (self::is_string_NSFW($description)){
            $description = self::is_string_NSFW($description, true)['cleaned_string'];
            //<editor-fold desc="Logging">
            __info("... NSFW content found in description. Replacing word(s).", "warning");
            //</editor-fold>
        }

        //<editor-fold desc="Logging">
        __info("Finished checking elements. Prepping database...");
        __info("Generating GUID for stack...");
        //</editor-fold>


        // Step 6:
        // Create GUID for the set
        $GUID = GUID("API.grooped.Stack", "Stack generation", false);


        //<editor-fold desc="Logging">
        __info("...Done. Using GUID \"$GUID\".", "success");
        __info("Generating vertex row...");
        //</editor-fold>
        // Step 7:
        // Connect to database and create vertex row
        $connection = self::DBConnect();

        // Set flag to int if set is  NFSW
        $nsfw = ($nsfw == true) ? 1 : 0;

        // Encode " ' " with "|??|" to prevent SQL Injection in description and title
        $title = str_replace("'", "|??|", $title);
        $description = str_replace("'", "|??|", $description);

        // Insert row
        $connection->query("INSERT INTO moycroft_grooped.CARD_STACK_VERTEX_PUBLISHED 
                                        (TITLE, DESCRIPTION, CREATOR_NAME, CREATOR_PERSISTENCE_ID, CREATOR_ACCOUNT_ID, ROUNDS, PLAYERS_MIN, PLAYERS_MAX, SEARCH_TERMS, NSFW, STACK_ID, CREATED_ON, VERIFIED) 
                                VALUES 
                                       ('$title', '$description', '".Tokens::getName()."', '".Tokens::getCurrentPersistenceToken()."', '".Tokens::getAccountId()."', '".sizeof($questions)."', '$player_min', '$player_max', '$search_string', '$nsfw', '$GUID', NOW(), 0)");
        //<editor-fold desc="Logging">
        __info("...Done", "success");
        __info("Verifying on GUID ($GUID)");
        //</editor-fold>


        // Step 8:
        // Verify if row exists
        if (sizeof(self::DBConnect()->query("SELECT * FROM moycroft_grooped.CARD_STACK_VERTEX_PUBLISHED WHERE STACK_ID = '$GUID'", true)) == 0){
            throw new \UnknownError("Vertex creation failed due to an unknown error", "Something went wrong on our end while creating this stack.", "We're not sure what happened");
        }
        //<editor-fold desc="Logging">
        __info("... Record exists.", "success");
        __info("Transferring question set to published table...");
        //</editor-fold>


        // Step 9:
        // Create rows for question content storage
        $current_round = 0;
        $total_questions = 0;
        foreach ($questions as $round){
            $question_number = 0;
            foreach ($round as $question){
                self::DBConnect()->query("INSERT INTO moycroft_grooped.CARD_STACK_STORE_PUBLISHED (STACK_ID, QUESTION, ROUND, SEQUENCE) VALUES ('$GUID', '".str_replace("'", "|??|", $question)."', '$current_round', '$question_number')");
                $question_number++;
            }
            $current_round++;
        }
        //<editor-fold desc="Logging">
        __info("...Done", "success");
        __info("Verifying published table...");
        //</editor-fold>

        // Step 10:
        // Verify rows and size of rows
        $size_of_store = sizeof(self::DBConnect()->query("SELECT * FROM moycroft_grooped.CARD_STACK_STORE_PUBLISHED WHERE STACK_ID = '$GUID'", true));
        if ($size_of_store == 0){
            throw new \UnknownError("Question store creation failed due to an unknown error", "Something went wrong on our end while creating this stack.", "We're not sure what happened");
        }
        if ($size_of_store < $total_questions){
            //<editor-fold desc="Logging">
            __info("Not all questions were inserted. Reason: row count is less than question count", "warning");
            //</editor-fold>
            Tunnel::sendHTTPResponse([
                "pertaining_to" => "creation",
                'display_method' => "dialogue",
                "message" => "" . ($total_questions - $size_of_store) . " of your question" . (($total_questions - $size_of_store) == 1 ? "s" : "") . " was not published, and were not sure why.",
                "title" => "Some of your questions were not published",
                "target" => Tokens::getCurrentPersistenceToken()
            ], "error");
        }
        //<editor-fold desc="Logging">
        __info("...Done", "success");
        __info("Stack creation complete.", "success");
        //</editor-fold>
        return true;
    }

    /**
     * Creates loaded Stack object initialized from database
     *
     * @param $stack_id string GUID stack ID
     * @return Stack Initialized stack object
     * @throws \StackDoesNotExist Throws if the stack does not exists in record
     */
    static function open($stack_id){
        $object = new Stack();
        $object->load($stack_id);
        return $object;
    }

    //<editor-fold desc="Database load and unload Functions">
    /**
     * Loads stack from database and rebuilds object
     *
     * @param $stack_id string GUID Stack ID to pull from database
     * @throws \StackDoesNotExist Throws if the stack does not exists in record
     */
    function load($stack_id){
        $data = self::DBConnect()->query("SELECT * FROM moycroft_grooped.CARD_STACK_VERTEX_PUBLISHED WHERE STACK_ID='$stack_id';", true);
        if(sizeof($data) == 0){
            throw new \StackDoesNotExist("The stack ID does not have a vertex row, suggesting it does not exist.", "It seems like this question set was deleted or did not exist.", "This Question set does not exist");
        }

        $data = $data[0];
        $this->stack_id = $stack_id;
        $this->title = str_replace("|??|", "'", $data['TITLE']);
        $this->description = str_replace("|??|", "'", $data['DESCRIPTION']);
        $this->nsfw = ($data['NSFW'] == '1') ? 1 : 0;
        $this->creator_name = str_replace("|??|", "'", $data['CREATOR_NAME']);
        $this->creator_persistence_id = $data['CREATOR_PERSISTENCE_ID'];
        $this->creator_account_id = $data['CREATOR_ACCOUNT_ID'];
        $this->created_on = $data['CREATED_ON'];
        $this->search_terms = explode(',', $data['SEARCH_TERMS']);
        $this->players_max = $data['PLAYERS_MAX'];
        $this->players_min = $data['PLAYERS_MIN'];
        $this->verified = ($data['VERIFIED'] == '1') ? 1 : 0;
        $this->carbon = $data;

        // Get all the questions
        $batch = self::DBConnect()->query("SELECT * FROM moycroft_grooped.CARD_STACK_STORE_PUBLISHED WHERE STACK_ID='$stack_id';");
        $data = [];
        foreach ($batch as $row_section){
            $data[intval($row_section['ROUND'])][$row_section['SEQUENCE']] = str_replace("|??|", "'", $row_section['QUESTION']);
        }
        $this->content = $data;
    }

    /**
     * Reload object from database
    */
    function reload(){
        $this->load($this->stack_id);
    }
    //</editor-fold>

    //<editor-fold desc="Stack Modification Functions">
    /**
     * Delete stack
     */
    function delete(){
        // Delete vertex
        self::DBConnect()->query("DELETE FROM moycroft_grooped.CARD_STACK_VERTEX_PUBLISHED WHERE STACK_ID='$this->stack_id'");
        // Delete all questions
        self::DBConnect()->query("DELETE FROM moycroft_grooped.CARD_STACK_STORE_PUBLISHED WHERE STACK_ID='$this->stack_id'");
    }

    function edit_question($round, $sequence, $new_question){

    }

    function delete_question($round, $sequence){

    }

    function delete_round($round){

    }

    function move_question($round_from, $sequence_from, $round_to, $sequence_to){

    }
    //</editor-fold>

    //<editor-fold desc="Helper Functions">
    /**
     * Verifies structural integrity of array and ensures boundaries are kept
     *
     * @param $questions array 2D array with round => question order
     * @return array returns values:
     *                       is_usable - if the array is usable
     *                       reason - developer reason. not to be shown to end user
     *                       friendly - if exists, error can be shown to end user
     */
    private static function analyse_question_set($questions){
        if (!is_array($questions)){
            return [
                "is_usable" => false,
                "reason" => "Is not array",
                "friendly" => "Something went wrong on our side"
            ];
        }
        if (sizeof($questions) > GLOBAL_CONFIG['grooped']['stacks']['rounds']['max']){
            return[
                "is_usable" => false,
                "reason" => "Too many rounds",
                "friendly" => "It seems like you have too many rounds! You can have up to ".GLOBAL_CONFIG['grooped']['stacks']['rounds']['max']." rounds."
            ];
        }
        if (sizeof($questions) < GLOBAL_CONFIG['grooped']['stacks']['rounds']['min']){
            return[
                "is_usable" => false,
                "reason" => "Too little rounds",
                "friendly" => "It seems like you have too little rounds! You must have at least ".GLOBAL_CONFIG['grooped']['stacks']['rounds']['min']." rounds."
            ];
        }
        foreach ($questions as $question) {
            if (!is_array($question)){
                return [
                    "is_usable" => false,
                    "reason" => "Nested array is not present for one or more parent elements.",
                    "friendly"
                ];
            }
            if (sizeof($question) > GLOBAL_CONFIG['grooped']['stacks']['questions']['max']){
                return[
                    "is_usable" => false,
                    "reason" => "Too many questions",
                    "friendly" => "It seems like you have too many questions! You can have up to ".GLOBAL_CONFIG['grooped']['stacks']['questions']['max']." in a round."
                ];
            }
            if (sizeof($question) < GLOBAL_CONFIG['grooped']['stacks']['questions']['min']){
                return[
                    "is_usable" => false,
                    "reason" => "Too little questions",
                    "friendly" => "It seems like you have too little questions! You must have at least ".GLOBAL_CONFIG['grooped']['stacks']['questions']['min']." questions in a round."
                ];
            }
        }
        return [
            "is_usable" => true
        ];
    }

    /**
     * Scans list of banned words to find words to flag as NSFW
     *
     * @param $string String to be scanned
     * @param bool $replace If a word is found it will be replaced by ***
     * @return array|bool If in replace mode, array will be [is_nsfw, cleaned_string] if not, it will just be is_nsfw
     */
    static function is_string_NSFW($string, $replace=false){

        //Get list of banned words
        $naughty_words = explode("\n",file_get_contents(__DIR__."/../assets/filters/en"));

        // Explode and glue input string REMOVED
         $revised_string = $string;

         $symbol = [];
         $cur = 0;

         // Find punctuation & symbols and put them into an array
         for ($i = 0; $i < strlen($revised_string); $i++){
             if (ctype_punct($revised_string[$i])){
                 $symbol[$cur] = [
                     "where" => $i,
                     "what" => $revised_string[$i]
                 ];
             }
         }

         // Remove all symbols
        $string = preg_replace("/[^A-Za-z0-9 ]/", '', $string);

         //Replace gamer text
        $gamer_text_replace = [
            [0,'o'],
            [1, 'i'],
            [3,'e'],
            [4, 'a'],
            [5, 's'],
            [6, 'g'],
            [8, 'ate'],
        ];
        foreach ($gamer_text_replace as $gt){
            $string = str_replace($gt[0], $gt[1], $string);
        }

        //explode strings on punctuation and spaces
        $exploded_string = preg_split('/( |\?|!)/', $string);
        $is_nsfw = false;
        $final_string = "";

        //Iterate through words in string
        foreach ($exploded_string as $word){
            $new_word = $word;
            //Compare string word against banned words
            foreach ($naughty_words as $naughty_word) {
                //Make sure strings are comparable by changing case
                if (stristr(strtolower($word), $naughty_word)) {
                    if (!$replace){
                        //if not in replace mode, return true
                        return true;
                    }else{
                        //in replace mode

                        //Change nsfw flag
                        if (!$is_nsfw) $is_nsfw = true;

                        // keep first letter
                        $first_letter = $word[0];

                        // replace with stars
                        for ($i = 0; $i < strlen($word)-1; $i++){
                            $new_word[$i] = "*";
                        }

                        // reinsert first letter
                        $new_word[0] = $first_letter;

                    }
                }
            }
            // append words onto final string
            $final_string .= $new_word . " ";
        }
        if ($replace){
            //replace mode
            //reinsert symbols
            foreach ($symbol as $item) {
                $final_string = substr_replace($final_string, ' ', $item['where']+1);
                $final_string[$item['where']] = $item['what'];
            }
            return [
                "is_nsfw" => $is_nsfw,
                "cleaned_string" => $final_string
            ];
        }else{
            //not in replace mode, return status
            return $is_nsfw;
        }
    }

    /**
     * Create DB Connect object
    */
    static function DBConnect(){
        $connection = new Connect();
        $connection->connect('moycroft_grooped');
        return $connection;
    }
    //</editor-fold>

}
?>