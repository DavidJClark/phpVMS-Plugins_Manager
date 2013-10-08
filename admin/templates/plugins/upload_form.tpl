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

    echo '<b>This Version of phpVMS is using the ';
    if($phptemplate == TRUE) {echo '.php';} else {echo '.tpl';}
    echo ' template extension. Be sure you are choosing the correct module format.</b>';
?>
<h3>Upload Plugins From GitHub</h3>
<?php
    if($github != '')   {
        foreach($github as $key => $link)   {
            echo '<a href="'.adminurl('/plugins/github_file/'.$key).'">'.$link[1].'</a><br />';
        }
    }
    else    {
        echo 'No Plugins Available<br />';
    }
?>
<br /><hr />
<h3>Upload A Plugin File</h3>
<form action="<?php echo SITE_URL?>/admin/index.php/plugins" method="post" enctype="multipart/form-data">
                <p>
                    <label for="file">Plugin to upload:</label><br />
                    <input type="file" size="70" name="zip_file" />
                </p>

                <p>
                    <input type="hidden" name="action" value="save_upload" />
                    <input type="submit" value="Upload Plugin" />
                </p>
</form>