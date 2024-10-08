<?php

use Illuminate\Support\Str;

if(!function_exists('timeAgo')){
    function timeAgo($timestamp){
        $timeDifference = time() - strtotime($timestamp);
        $seconds = $timeDifference;
        $minutes = round($timeDifference / 60);
        $hours = round($timeDifference / 3600);
        $days = round($timeDifference /  86400);
        if($seconds <= 60){
            return "a second ago";
        }elseif($minutes <= 60){
            return "$minutes m ago";
        }elseif($hours <= 24){
            return "$hours h ago";
        }else{
            return date('j M y', strtotime($timestamp));
        }
    }
}


/**
 * Truncate string
 */

 if(!function_exists('truncate')){
    function truncate($str, $limit = 16){
        return Str::limit($str, $limit, "....");
    }
 }