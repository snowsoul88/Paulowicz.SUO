<?php

class Controllers {

    public static function getCurrentControllerName() {
        $ResourceUri = explode("/", $_SERVER['REQUEST_URI']);
        $ControllerName = $ResourceUri[1];
        
        if ($ControllerName === "") {
            return "index";
        } else {
            return $ControllerName;
        }
    }
}
