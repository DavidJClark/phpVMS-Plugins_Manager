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

class Plugins extends CodonModule   {
    
    public function HTMLHead()
    {
            $this->set('sidebar', 'plugins/sidebar.php');
    }

    public function NavBar()
    {
        echo '<li><a href="'.SITE_URL.'/admin/index.php/plugins">Plugin Manager</a></li>';
    }
    
    public function index() {
        if($this->post->action !='')
        {
            if($this->post->action == 'save_upload') {
                $this->save_upload();
            }
            if($this->post->action == 'send_message') {
                $this->send_message();
            }
        }
        else
        {
            $this->uploaded();
        }
    }
    
    public function uploaded()  {
        $dirhandler = opendir("modules/Plugins/uploads/");
        $plugins = array();
        $i = 1;
        while ($file = readdir($dirhandler)) {

            // if $file isn't this directory or its parent 
            // add to the $files array
            if ($file != '.' && $file != '..' && $file != 'index.php' && $file != 'pluginlist.txt')
            {
                if(file_exists('modules/Plugins/uploads/'.$file.'/config.txt'))
                {
                    $info = file('modules/Plugins/uploads/'.$file.'/config.txt');
                    
                    foreach($info as $line)
                        {
                            $data = explode('=', $line);
                            $config[$i]->$data[0] = trim($data[1]);
                        }    

                        $config[$i]->file = $file;
                }
                else
                {
                    $config[$i]->file = $file;
                }
                $i++; 
            }   

        }
        closedir($dirhandler);        
        
        $this->set('plugins', $config);
        $this->show('plugins/header');
        $this->show('plugins/uploaded');
        $this->show('plugins/footer');
    }
    
    public function get_plugin($plugin) {
        
        $info = file('modules/Plugins/uploads/'.$plugin.'/config.txt');
        foreach($info as $line)
        {
            $data = explode('=', $line);
            $config->$data[0] = trim($data[1]);
        }
        
        //check to see if plugin is already installed
        if(file_exists('modules/Plugins/uploads/'.$plugin.'/uninstall.txt'))
        {$installed = TRUE;}
        else
        {$installed = FALSE;}
        
        $this->set('installed', $installed);
        $this->set('plugin', $plugin);
        $this->set('config', $config);        
        $this->set('path', 'modules/Plugins/uploads/'.$plugin.'/');
        $this->show('plugins/header');
        $this->show('plugins/plugin');
        $this->show('plugins/footer');
    }
    
    function ls($pattern="*", $folder="", $recursivly="", $options=array('return_files','return_folders')) {
        if($folder) {
            $current_folder = realpath('.');
            if(in_array('quiet', $options)) { // If quiet is on, we will suppress the 'no such folder' error
                if(!file_exists($folder)) return array();
            }

            if(!chdir($folder)) return array();
        }


        $get_files    = in_array('return_files', $options);
        $get_folders= in_array('return_folders', $options);
        $both = array();
        $folders = array();

        // Get the all files and folders in the given directory.
        if($get_files) $both = glob($pattern, GLOB_BRACE + GLOB_MARK);
        if($recursivly or $get_folders) $folders = glob("*", GLOB_ONLYDIR + GLOB_MARK);

        //If a pattern is specified, make sure even the folders match that pattern.
        $matching_folders = array();
        if($pattern !== '*') $matching_folders = glob($pattern, GLOB_ONLYDIR + GLOB_MARK);

        //Get just the files by removing the folders from the list of all files.
        $all = array_values(array_diff($both,$folders));

        if($recursivly or $get_folders) {
            foreach ($folders as $this_folder) {
                if($get_folders) {
                    //If a pattern is specified, make sure even the folders match that pattern.
                    if($pattern !== '*') {
                        if(in_array($this_folder, $matching_folders)) array_push($all, $this_folder);
                    }
                    else array_push($all, $this_folder);
                }

                if($recursivly) {
                    // Continue calling this function for all the folders
                    $deep_items = $this->ls($pattern, $this_folder, $recursivly, $options); # :RECURSION:
                    foreach ($deep_items as $item) {
                        array_push($all, $this_folder . $item);
                    }
                }
            }
        }

        if($folder) chdir($current_folder);
        return $all;
    }
    
    public function install($plugin)    {       
        error_reporting(0);
        $failure = FALSE;
        $failures = array();
        $installed = array();
        $uninstall = array();
        $folders = array();
        $sqltables = array();
        $directories = array();
        $tables = array();
        
        $_files = $this->ls('*', 'modules/Plugins/uploads/'.$plugin.'/', TRUE, array('return_files'));
        $_folders = $this->ls('*', 'modules/Plugins/uploads/'.$plugin.'/', TRUE, array('return_folders'));       
       
        // Create all new folders
        foreach($_folders as $folder){            
            $_folder = SITE_ROOT.$folder; 
            if(!file_exists($_folder)){
                if(mkdir($_folder, 0755)){
                    $folders[] = $_folder;
                } else {
                    $failure = TRUE;
                    $failures[] = 'Failure Creating '.$_folder.' Directory';
                }
            }             
        }       
       
        foreach($_files as $file){            
            $ext = pathinfo($file, PATHINFO_EXTENSION);
            
            if($ext == 'sql'){
                // Take care of any SQL files
                $sqldata = $this->readSQLFile('modules/Plugins/uploads/'.$plugin.'/'.$file, TABLE_PREFIX);
                
		foreach($sqldata as $sql) {
                    if($failure == TRUE){continue;}
                    DB::query($sql['sql']);                    
                    //set status message
                    if(DB::error() != '') {
                        $this->set('sqlstatus', 'SQL File/Database Error.');
                        $failures[] = 'SQL File/Database Error.';
                        $failure = TRUE;
                    } else {
                        if(!in_array($sql['tablename'].'*sql', $sqltables)){
                            $sqltables[] = $sql['tablename'].'*sql';
                            $tables[] = 'Imported '.$sql['tablename'].' Into Database Successfully';
                        }                        
                    }
                }
            } elseif( $ext == 'txt'){
                //Text files skipped for now
                
            } else {
                // Copy files from current location into proper location                
                $_newfile = '../'.$file;
                $_oldfile = 'modules/Plugins/uploads/'.$plugin.'/'.$file;
                
                // Delete old file (helpful for upgrading)
                if(file_exists($_newfile)){
                    unlink($_newfile);                    
                }
                
                if(copy($_oldfile, $_newfile)){
                    $installed[] = 'File '.$file.' Installed Successfully.';
                    $uninstall[] = $_newfile;
                } else {
                    $installed[] = 'File '.$file.' Installation Failed.';
                    $failure = TRUE;
                    $failures[] = 'File '.$file.' Installation Failed.';
                }
            }         
            
        }
        
        //merge all file arrays
        if(isset($sqltables)){$uninstall = array_merge($sqltables, $uninstall);}
        if(isset($folders)){$uninstall = array_merge($uninstall, $folders);}
       
        //set install status message
        if($failure == FALSE){
            $status = 'Successful Installation.';
           
            //create uninstall file
            $deletefile = 'modules/Plugins/uploads/'.$plugin.'/uninstall.txt';
            $fh = fopen($deletefile, 'w');
            foreach($uninstall as $uni) {
                $line =$uni.'\n ';
                fwrite($fh, $uni.PHP_EOL);
            }
            fclose($fh);
            //end creating uninstall file
            
            //date installed file for reference
            $datefile = 'modules/Plugins/uploads/'.$plugin.'/installdate.txt';
            $fh = fopen($datefile, 'w');
            fwrite($fh, time());
            fclose($fh);
            
        } else {
            // INSTALLATION FAILED
            // Remove any database tables and files that were installed
            foreach($uninstall as $file) {
                //check if it is a sql table and drop it if it is
                $sqltable = explode('*', $file);
                if(isset($sqltable[1])) {
                    if(!isset($tables)){$tables = array();}
                    $table = explode('*', $file);
                    $query = 'DROP TABLE '.$table[0];
                    DB::query($query);
                } else {
                    if(is_dir(trim($file))) {
                        $directories[] = $file;
                    } else {
                        unlink(trim($file));
                    }
                }
            }
            // Remove installed folders
            if(isset($directories)) {
                for($i = count($directories); $i > 0; $i--){
                    if ($this->dir_is_empty($directories[$i-1])) {
                        rmdir($directories[$i-1]);
                    }
                }
            }
            //set status data for view file
            $status = 'Installation Failed.';
            //send failure messages to results screen
            $this->set('failures', $failures);
            //get plugin data for email to developer
            $info = file('modules/Plugins/uploads/'.$plugin.'/config.txt');
            foreach($info as $line) {
                $data = explode('=', $line);
                $config->$data[0] = trim($data[1]);
            }
           $this->set('config', $config);
           //end install failure
        }      
       
        if(isset($assets)){$this->set('assets', $assets);}
        $this->set('tables', $tables);
        $this->set('installed', $installed);
        $this->set('status', $status);
        $this->show('plugins/header');
        $this->show('plugins/result');
        $this->show('plugins/footer');
    }
    
    //send failure message to developer
    protected function send_message()   {
        $message = $this->post->message;
        $message .= '<br /><br />Additional Comments.<br /><br />';
        $message .= DB::escape($this->post->comments);
        
        Util::SendEmail($this->post->to, $this->post->subject, $message, SITE_NAME, ADMIN_EMAIL);
        $this->show('plugins/header');
        $this->show('plugins/message_sent');
        $this->show('plugins/footer');
    }
    
    //remove plugin from system
    public function uninstall($plugin)    {
        
        $failure = FALSE;
        $messages = array();
        
        $files = file('modules/Plugins/uploads/'.$plugin.'/uninstall.txt');
        
        foreach($files as $file) {
            //check if it is a sql table and drop it if it is
            $sqltable = explode('*', $file);
            if(isset($sqltable[1])) {
                if(!isset($tables)){$tables = array();}
                $table = explode('*', $file);
                $query = 'DROP TABLE '.$table[0];
                DB::query($query);
                $tables[] = $table[0];
                if(DB::error() != '') {
                    $failure = TRUE;
                    $failmessages[] = 'Error Dropping Database Table '.$table[0].'. Remove Manually.';
                }
            } else {
                if(is_dir(trim($file))) {
                    $directories[] = trim($file);
                } else {
                    unlink(trim($file));
                    $messages[] = 'Removed File '.trim($file);
                }
            }
        }
        
        //remove the directories
        if(isset($directories)) {
            for($i = count($directories); $i > 0; $i--){
                if ($this->dir_is_empty($directories[$i-1])) {
                    rmdir($directories[$i-1]);
                    $messages[] = 'Removed Folder '.trim($directories[$i-1]);
                } else {
                    $failmesages[] = 'Error Removing Folder '.trim($directories[$i-1]).' - Folder NOT empty.';
                }
            }
        }    
        
        unlink('modules/Plugins/uploads/'.$plugin.'/uninstall.txt');
        $messages[] = 'Removed uninstall token';
        unlink('modules/Plugins/uploads/'.$plugin.'/installdate.txt');
        $messages[] = 'Removed install date token';
        
        if($failure == TRUE)($this->set('failmessages', $failmessages));
        if(isset($tables)){$this->set('sqltables', $tables);}
        $this->set('messages', $messages);
        $this->show('plugins/header');
        $this->show('plugins/uninstall');
        $this->show('plugins/footer');
    }
    
    public function force_new_listing() {
        $this->get_new_listing();
        $this->set('message', '<div id="success">New Plugin Listing Downloaded.</div>');
        $this->uploaded();
    }
    
    public function get_new_listing()   {
        
        $target_url = 'https://raw.github.com/DavidJClark/phpVMS-PluginsList/master/plugins.txt';
        
        // make the cURL request
        $ch = curl_init();
        $fp = fopen("modules/Plugins/uploads/pluginlist.txt", "w");
        curl_setopt($ch, CURLOPT_URL,$target_url);
        curl_setopt($ch, CURLOPT_FAILONERROR, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        curl_exec($ch);
        fclose($fp);
    }
    
    public function upload()    {
        
        if(time()-filemtime('modules/Plugins/uploads/pluginlist.txt') > 604800)    {
            $this->get_new_listing();
        }
        
        $filename = "modules/Plugins/uploads/pluginlist.txt";
        $handle = fopen($filename, "rb");
        $lines = fread($handle, filesize($filename));
        fclose($handle);
       
        $lines = explode("\n", $lines);
        foreach($lines as $line)    {
            $github[] = explode('+', $line);
        }
        $TemplateExtension = new TemplateSet();
        if($TemplateExtension->tpl_ext == 'php') {
            $this->set('phptemplate', TRUE);
        }
        else    {
            $this->set('phptemplate', FALSE);
        }
        $this->set('github', $github);
        $this->show('plugins/header');
        $this->show('plugins/upload_form');
        $this->show('plugins/footer');
    }
    
    public function github_file($key)   {
        
        $filename = "modules/Plugins/uploads/pluginlist.txt";
        $handle = fopen($filename, "rb");
        $contents = fread($handle, filesize($filename));
        fclose($handle);
       
        $lines = explode("\n", $contents);
        foreach($lines as $line)    {
            $github[] = explode('+', $line);
        }
        $url = $github[$key][0];
        
            $target_url = 'https://github.com/'.$url;
            $userAgent = 'Googlebot/2.1 (http://www.googlebot.com/bot.html)';
            $file_zip = "newfile.zip";

            // make the cURL request to $target_url
            $ch = curl_init();
            $fp = fopen("$file_zip", "w"); 
            curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
            curl_setopt($ch, CURLOPT_URL,$target_url);
            curl_setopt($ch, CURLOPT_FAILONERROR, true);
            curl_setopt($ch, CURLOPT_HEADER,0); 
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_AUTOREFERER, true);
            curl_setopt($ch, CURLOPT_BINARYTRANSFER,true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 0);
            curl_setopt($ch, CURLOPT_FILE, $fp);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

            curl_exec($ch);

            fclose($fp);
        
            $zip = new ZipArchive();
            $x = $zip->open($file_zip);
            //check if module already exists - do not allow it to be overwritten - use update function
            $fname = $zip->statIndex(0);
            if(file_exists("modules/Plugins/uploads/".$fname['name']))  {
                   $this->show('plugins/header');
                    $this->show('plugins/upload_alreadyexists');
                    $this->show('plugins/footer');
                    return;
                }
            if ($x === true) {
                    $zip->extractTo("modules/Plugins/uploads/");
                    $zip->close();

                    unlink($file_zip);
            }
                    
        $this->show('plugins/header');
        $this->show('plugins/upload_success');
        $this->show('plugins/footer');
    }
    
    protected function save_upload() {

       if($_FILES["zip_file"]["name"]) {
	$filename = $_FILES["zip_file"]["name"];
	$source = $_FILES["zip_file"]["tmp_name"];
	$type = $_FILES["zip_file"]["type"];
 
	$name = explode(".", $filename);
	$accepted_types = array('application/zip', 'application/x-zip-compressed', 'multipart/x-zip', 'application/x-compressed');
	foreach($accepted_types as $mime_type) {
		if($mime_type == $type) {
			$okay = true;
			break;
		} 
	}
 
	if(strtolower(substr($filename, -3)) != 'zip') {
            $this->show('plugins/header');
            $this->show('plugins/upload_badextension');
            $this->show('plugins/footer');
	}
        else
        {
            $target_path = "modules/Plugins/uploads/".$filename;
            //check if plugin already exists
            if(file_exists("modules/Plugins/uploads/".substr($filename, 0, -4)))
            {
                $this->show('plugins/header');
                $this->show('plugins/upload_alreadyexists');
                $this->show('plugins/footer');
            }
            else
            {
                if(move_uploaded_file($source, $target_path)) {
                    $zip = new ZipArchive();
                    $x = $zip->open($target_path);
                    if ($x === true) {
                            $zip->extractTo("modules/Plugins/uploads/");
                            $zip->close();

                            unlink($target_path);
                    }
                    $this->show('plugins/header');
                    $this->show('plugins/upload_success');
                    $this->show('plugins/footer');
                }
                else 
                {	
                    $this->show('plugins/header');    
                    $this->show('plugins/upload_error');
                    $this->show('plugins/footer');
                }

            }
        }
        }

        }
        
        public function delete($dir) {
            $directory = 'modules/Plugins/uploads/'.$dir.'/';
            $this->rrdir($directory);
            $this->index();

        }
        
        public function dir_is_empty($dir) {
            if (!is_readable($dir)) return NULL;
            return (count(scandir($dir)) == 2);
        }
        
        public function rrdir($dir) { 
            if ($handle = opendir($dir))
                {
                        $array = array();
                    while (false !== ($file = readdir($handle))) {
                    if ($file != "." && $file != "..") {
                                        if(is_dir($dir.$file))
                                        {
                                                if(!@rmdir($dir.$file)) // Empty directory? Remove it
                                                {
                                        $this->rrdir($dir.$file.'/'); // Not empty? Delete the files inside it
                                                }
                                        }
                                        else
                                        {
                         @unlink($dir.$file);
                                        }
                        }
                }
                        closedir($handle);
                        @rmdir($dir);
                }
        }

        //Function from phpVMS install script
        function readSQLFile($file_name, $table_prefix = '') {

        $sqlLines = array();

        $sql = '';
        $sql_file = file($file_name);

        foreach($sql_file as $sql_line) {

            $sql .= trim($sql_line);

            if(substr_count($sql, ';') > 0) {

                $sql = trim($sql);

                # See if it's a comment?
                if($sql[0] == '-' && $sql[1] == '-') {
                    $sql = '';
                    continue;
                }

                if($sql == '') {
                    continue;
                }

                $sql = str_replace('phpvms_', $table_prefix, $sql);

            	preg_match("/`{$table_prefix}([A-Za-z_]*)`/", $sql, $matches);
            	$tablename = $matches[0];

                $sqlLines[] = array(
                    'tablename' => $tablename,
                    'sql' => $sql
                );
                
                $tablename = '';
                $sql = '';
            }
        }
        return $sqlLines;
    }
}