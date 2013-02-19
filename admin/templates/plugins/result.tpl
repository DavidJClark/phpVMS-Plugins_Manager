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

echo '<div style="border: 1px solid #000; background: #E8E8E8; padding: 0 20px 20px 20px; margin: 20px;">';
echo '<h2>Plugin Installation Status</h2>';
echo $status;
        if(isset($failures)) {
            echo '<br /><br /><b>Error Messages Are Shown Below - Please Forward These Messages To The Plugin Developer</b>';
        }
echo '</div><hr />';

    if(isset($failures))
    {
        echo '<div style="border: 1px solid #000; background: #E8E8E8; padding: 0 20px 20px 20px; margin: 20px;">';
        echo '<h2>Install Failures</h2>';
        foreach($failures as $failure)
        {
            echo $failure.'<br />';
        }
        echo '</div>';
        echo '<hr />';
        
        //send message to developer option
        
        echo '<div style="border: 1px solid #000; background: #E8E8E8; padding: 0 20px 20px 20px; margin: 20px;">';
        echo '<h2>Send Message To Developer</h2>';
        //set email message variables
        $to = 'To: '.$config->author.' ('.$config->email.')<br /><br />';
        
        $subject = $config->plugin.' (Version '.$config->version.') Installation Failed.';
        $message = 'The following errors were encountered during installation;<br /><br />';
        foreach($failures as $failure)
        {
            $message .= $failure.'<br />';
        }
        $message .= '<br />Site Name: '.SITE_NAME.'<br />';
        $message .= 'Install URL: '.SITE_URL;
        //show message
        echo $to;
        echo 'From: '.SITE_NAME.' ('.ADMIN_EMAIL.')<br /><br />';
        echo 'Subject: '.$subject.'<br /><br />';
        echo 'Message: '.$message.'<br /><br />';
        //form to send message
        echo '<form action="'.SITE_URL.'/admin/index.php/plugins" method="post" enctype="multipart/form-data">';
        echo 'Additional Comments:<br />';
        echo '<textarea name="comments" cols="70" rows="6"></textarea><br /><br />';
        echo '<input type="hidden" name="subject" value="'.$subject.'" />';
        echo '<input type="hidden" name="message" value="'.$message.'" />';
        echo '<input type="hidden" name="to" value="'.$config->email.'" />';
        echo '<input type="hidden" name="action" value="send_message" />';
        echo '<input type="submit" class="button" value="Send Message" />';
        echo '</form>';
        echo '</div>';
        echo '<hr />';
    }
    else
    {
        if(isset($tables))
        {
            echo '<div style="border: 1px solid #000; background: #E8E8E8; padding: 0 20px 20px 20px; margin: 20px;">';
            echo '<h2>Database Files Installed</h2>';
            foreach($tables as $table)
            {
                echo $table.'<br />';
            }
            echo '</div><hr />';
        }

        echo '<div style="border: 1px solid #000; background: #E8E8E8; padding: 0 20px 20px 20px; margin: 20px;">';
        echo '<h2>Library Files Installed</h2>';
        foreach($installed as $install)
        {
            echo $install.'<br />';
        }
        echo '</div>';
        echo '<hr />';

        if(isset($assets))
        {
        echo '<div style="border: 1px solid #000; background: #E8E8E8; padding: 0 20px 20px 20px; margin: 20px;">';
        echo '<h2>Library Assets Installed</h2>';
        foreach($assets as $asset)
        {
            echo $asset.'<br />';
        }
        echo '</div>';
        }
    }
?>