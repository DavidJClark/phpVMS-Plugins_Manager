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
<table class="tablesorter">
    <tr>
        <th align="left">Plugin</th>
        <th align="left">Version</th>
        <th align="left">Published</th>
        <th align="left">Author</th>
        <th align="left">Details</th>
        <th align="left">Developer's Page</th>
        <th align="left">Date Installed</th>
        <th align="left">Install/Uninstall</th>
    </tr>
<?php
    if(!empty($plugins))
    {
        foreach($plugins as $plugin)
        {
            echo '<tr>';
            
            if(empty($plugin->plugin))
            {
                echo '<td colspan="7">'.$plugin->file.' Is An Invalid Plugin - Missing Config File.</td>';
                echo '<td>';
                echo '<a class="button" href="'.SITE_URL.'/admin/index.php/plugins/delete/'.$plugin->file.'"';
                echo 'onclick="return confirm(\'Are you sure you want to delete this plugin?\')"';
                echo '>Delete Plugin</a>';
                echo '</td>';
            }
            else
            {
                $datefile = 'modules/Plugins/uploads/'.$plugin->file.'/installdate.txt';
                    if(file_exists($datefile))  {
                        $filedate = file('modules/Plugins/uploads/'.$plugin->file.'/installdate.txt');
                        $installdate = date(DATE_FORMAT, $filedate[0]);
                    }
                    else    {
                        $installdate = ' ';
                    }
                echo '<td>'.$plugin->plugin.'</td>';
                echo '<td>'.$plugin->version.'</td>';
                echo '<td>'.$plugin->published.'</td>';
                echo '<td>'.$plugin->author.'</td>';
                echo '<td><a href="'.SITE_URL.'/admin/index.php/plugins/get_plugin/'.$plugin->file.'">Details</a></td>';
                echo '<td><a href="'.$plugin->link.'" target="_blank">Developer\'s Page</a></td>';
                echo '<td>'.$installdate.'</td>';
                echo '<td>';
            
            if(!file_exists('modules/Plugins/uploads/'.$plugin->file.'/uninstall.txt'))
                {
                    echo '<a class="button" href="'.SITE_URL.'/admin/index.php/plugins/install/'.$plugin->file.'">Install Plugin</a>';
                    echo '<a class="button" href="'.SITE_URL.'/admin/index.php/plugins/delete/'.$plugin->file.'"';
                    echo 'onclick="return confirm(\'Are you sure you want to delete this plugin?\')"';
                    echo '>Delete Plugin</a>';
                }
                else
                {
                    echo '<a class="button" href="'.SITE_URL.'/admin/index.php/plugins/uninstall/'.$plugin->file.'"';
                    echo 'onclick="return confirm(\'Are you sure you want to uninstall this plugin? It will remove all the related files and any database tables associated with it.\')"';
                    echo '>Uninstall Plugin</a>';
                }  
            
            echo '</td>';
            }
            echo '</tr>';
        }
    }
    else
    {
        echo '<tr><td colspan="7">No Plugins Uploaded</td></tr>';
    }
?>
</table>