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
?>
<form action="<?php echo SITE_URL?>/admin/index.php/plugins" method="post" enctype="multipart/form-data">
                <p>
                    <label for="file">Plugin to upload:</label><br />
                    <input type="file" name="zip_file" />
                </p>

                <p>
                    <input type="hidden" name="action" value="save_upload" />
                    <input type="submit" value="Upload Plugin" />
                </p>
</form>