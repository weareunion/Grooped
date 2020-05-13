<?php
require_once "API/config.php";
\Moycroft\API\helper\IMPORT("API.grooped.*");
\Grooped\API\persistence\Tokens\Tokens::auto();

//echo \Grooped\API\persistence\Tokens\Tokens::getName();
echo \Grooped\API\gamemanager\GameCreator\GameCreator::generateGameID();
//\Grooped\API\stack\Stack\Stack::create("Test Game fuck Set", "This is a test shit description", 2, 5,
//    [
//            0 => [
//                    "R 1 @ Q 1", "R 1 bitch @ Q 2", "R 1 ' @ Q 3"
//            ],
//            1 => [
//                "R 2 @ Q 1", "R 2 @ Q 2", "R 2 @ Q 3", "R 2 @ Q 4"
//            ]
//        ]
//);

//$object = \Grooped\API\stack\Stack\Stack::open("CF83BF45-38D6-4134-A87D-F66A7C84C0C9");
//$object->delete();
//echo "<br>";
//var_dump($object);
//echo "Filter result: <br>";
//echo (\Grooped\API\stack\Stack\Stack::is_string_NSFW("Hello Nigger         !         what's up?", true)['cleaned_string']);
?>
<head>

</head>
<body>

<!--    REACT CONTAINER   -->

    <div id="CONTAINER">
<!--        --><?// echo var_dump(\Grooped\API\stack\Stack\Stack::is_string_NSFW("What the fu'ck is one? Where are se", true));?>
    </div>

<!-- IMPORT REACT DEVELOPMENT VERSION-->

    <script src="https://unpkg.com/react@16/umd/react.development.js" crossorigin></script>
    <script src="https://unpkg.com/react-dom@16/umd/react-dom.development.js" crossorigin></script>
    <script src="https://unpkg.com/babel-standalone@6/babel.min.js"></script>

<!-- SET UP REACT -->

    <script src="cdn/js/setup.js"></script>

<!-- LOAD REACT COMPONENTS -->

<!--    <script src="cdn/js/test_component.js" type="text/babel"></script>-->

</body>
