<?php

class Models { 
    
    public static function autoload($modelName) {
        if(file_exists("Models/model.".$modelName.".php")) {
            require "Models/model.".$modelName.".php";
            //echo "Model/model.".$modelName.".php\n";
        }
    }
}

spl_autoload_register(array('Models', 'autoload'));