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

    if($installed == FALSE)
    {
        echo '<a class="button" href="'.SITE_URL.'/admin/index.php/plugins/install/'.$plugin.'">Install Plugin</a>';
    }
    else
    {
        echo '<a class="button" href="'.SITE_URL.'/admin/index.php/plugins/uninstall/'.$plugin.'">Uninstall Plugin</a>';
    }  
echo '<div style="border: 1px solid #000; background: #E8E8E8; padding: 0 20px 10px 20px; margin: 20px;">';

foreach($config as $key => $value)
{
    if($key == 'notify'){continue;}
    if($key == 'plugin')
    {echo '<h1>'.ucfirst($value).'</h1>';}
    elseif($key == 'link')
    {echo 'Developer Link - <a href="'.$value.'" target="_blank">'.$value.'</a><br />';}
    else
    {echo ucfirst($key).' - '.$value.'<br />';}
}
echo '</div>';

//display readme file
$filename = $path.'readme.txt';
if(file_exists($path.'readme.txt'))  {
    $readmetext = file_get_contents($path.'readme.txt');
}
elseif (file_exists($path.'readme.md')) {
    $readmetext = file_get_contents($path.'readme.md');
}
else    {
    $redmetext = 'Unavailable';
}
echo '<hr />';
echo '<div style="border: 1px solid #000; background: #E8E8E8; padding: 0 20px 10px 20px; margin: 20px;">';
echo '<h1>Plugin Details</h1>';
$text = htmlentities($readmetext);
echo nl2br($text);
echo '</div>';
?>