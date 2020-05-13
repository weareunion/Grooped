<?php

namespace Grooped\API\gamemanager\GameCreator;

class GameCreator
{
    static function generateGameID(){
        $numbers = rand(1,99);
//        $files = [fopen(__DIR__."/../assets/adjectives.txt", "r"), fopen(__DIR__."/../assets/nouns.txt", 'r')];
        $adjectives = explode("\n",file_get_contents(__DIR__."/../assets/adjectives.txt"));
        $nouns = explode("\n",file_get_contents(__DIR__."/../assets/adjectives.txt"));
        return preg_replace("/[^A-Za-z0-9 ]/", '', ("" . $adjectives[rand(0, (sizeof($adjectives)-1))] . ucwords($nouns[rand(0, (sizeof($nouns)-1))]) . $numbers));
    }

}