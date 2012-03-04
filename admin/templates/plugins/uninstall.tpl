<?php
//simpilotgroup addon module for phpVMS virtual airline system
//
//this module is licenced under the following license:
//Creative Commons Attribution Non-commercial Share Alike (by-nc-sa)
//To view full icense text visit http://creativecommons.org/licenses/by-nc-sa/3.0/
//
//@author David Clark (simpilot)
//@link http://www.simpilotgroup.com
//@copyright Copyright (c) 2012, David Clark
//@license http://creativecommons.org/licenses/by-nc-sa/3.0/

    if(isset($failmessages))
    {
        var_dump($failmessages);
    }



    if(isset($sqltables))
    {
        echo '<div style="border: 1px solid #000; background: #E8E8E8; padding: 0 20px 20px 20px; margin: 20px;">';
        echo '<h2>Database Tables</h2>';
        foreach($sqltables as $table)
        {
            echo 'Dropped Database Table '.$table.'<br />';
        }
        echo '</div><hr />';  
    }


    echo '<div style="border: 1px solid #000; background: #E8E8E8; padding: 0 20px 20px 20px; margin: 20px;">';
    echo '<h2>Plugin Directories And Files</h2>';
    foreach($messages as $message)
    {
        echo $message.'<br />';
    }
    echo '</div>';
?>