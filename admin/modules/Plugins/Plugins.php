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
            $this->set('sidebar', 'plugins/sidebar.tpl');
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
            if ($file != '.' && $file != '..' && $file != 'index.php')
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
        
       $files = $this->ls('*', 'modules/Plugins/uploads/'.$plugin.'/', TRUE, array('return_files'));
       
       //set array variables
       $installed = array();
       $uninstall = array();
       $folders = array();
       
       
       foreach($files as $file)
       {
           //get file extension
           $extension = substr(strrchr($file,'.'),1);
           
           //it's a sql file - lets insert it into the database
           if($extension == 'sql')
           {
               $sqltables = array();
               
               $sqldata = $this->readSQLFile('modules/Plugins/uploads/'.$plugin.'/'.$file, TABLE_PREFIX);

		foreach($sqldata as $sql) {
                    if($failure == TRUE){continue;}
                        DB::query($sql['sql']);
                        $sqltables[] = $sql['tablename'].'*sql';
                        //set status message
                        if(DB::error() != '')
                            {
                                $this->set('sqlstatus', 'SQL File/Database Error.');
                                $failures[] = 'SQL File/Database Error.';
                                $failure = TRUE;
                            }
                            else
                            {
                                $this->set('sqlstatus', 'Imported '.$file.' Into Database Successfully');
                            }
                }
            }
           
           //it's a text file
           elseif($extension == 'txt')
           {
               //should not have to do anything with this - maybe in the future
           }
           
           //it's a library file or asset - lets put it into the install
           else
           {
               $parts = explode('\\', $file);
               
               if($parts[0] == 'core')
               {
                   //it's a class file - probably data
                   if($parts[1] == 'common')
                   {
                        if(copy('modules/Plugins/uploads/'.$plugin.'/'.$file, '../'.$file))
                        {
                            //file copied
                            $uninstall[] = '../'.$file;
                        }
                        else
                        {
                            //copy failed
                            $failure = TRUE;
                            $failures[] = 'Failure Moving '.$file;
                        }
                   }
                   //it's a module folder file
                   elseif($parts[1] == 'modules')
                   {
                    //it's a plugin asset or directory
                    if($parts[3] == 'assets')
                       {
                           if(!file_exists('../core/modules/'.$parts[2].'/assets'))
                           {
                               if(mkdir('../core/modules/'.$parts[2].'/assets', 0755))
                               {
                                   //successful directory creation
                                   $assets = array();
                               }
                               else
                               {
                                   //failed creating directory
                                   $failure = TRUE;
                                   $failures[] = 'Error Creating Assets Directory.';
                               }
                           }
                           if(copy('modules/Plugins/uploads/'.$plugin.'/'.$file, '../'.$file))
                           {
                               //success copying file
                               $assets[] = 'Asset File '.$parts[4].' Installed Successfully.';
                               $uninstall[] = '../'.$file;
                           }
                           else
                           {
                               //failure copying file
                               $assets[] = 'Asset File '.$parts[4].' Installation Failed.';
                               $failure = TRUE;
                               $failures[] = 'Asset File '.$parts[4].' Installation Failed.';
                           }
                       }
                       //it's a plugin library file or directory
                       else
                       {
                           if(!file_exists('../core/modules/'.$parts[2]))
                           {
                               if(mkdir('../core/modules/'.$parts[2], 0755))
                               {        
                                   //success creating directory 
                                   $folders[] = '../core/modules/'.$parts[2];
                               }
                               else
                               {
                                   //failed to create directory
                                   $failure = TRUE;
                                   $failures[] = 'Failure Creating '.$parts[2].' Module Directory';
                               }
                               //copy file
                               if(copy('modules/Plugins/uploads/'.$plugin.'/'.$file, '../'.$file))
                               {
                                   //success copying file
                                   $installed[] = 'File '.$parts[3].' Installed Successfully.';
                                   $uninstall[] = '../'.$file;
                               }
                               else
                               {
                                   //failed to copy file
                                   $installed[] = 'File '.$parts[3].' Installation Failed.';
                                   $failure = TRUE;
                                   $failures[] = 'File '.$parts[3].' Installation Failed.';
                               }
                           }
                           else
                           {
                                if(copy('modules/Plugins/uploads/'.$plugin.'/'.$file, '../'.$file))
                                {
                                    //success copying file
                                    $installed[] = 'File '.$parts[3].' Installed Successfully.';
                                    $uninstall[] = '../'.$file;
                                }
                                else
                                {
                                   //failed to copy file
                                   $installed[] = 'File '.$parts[3].' Installation Failed.';
                                   $failure = TRUE;
                                   $failures[] = 'File '.$parts[3].' Installation Failed.';
                                }
                           }
                       }
                   }
                   //it's a template file or directory
                   elseif($parts[1] == 'templates')
                   {
                       if(is_dir('modules/Plugins/uploads/'.$plugin.'/'.$file)){continue;}

                       //check for template directory
                       if(!file_exists('../core/templates/'.$parts[2]))
                       {
                           if(mkdir('../core/templates/'.$parts[2], 0755))
                           {
                               //succsess creating directory 
                               $folders[] = '../core/templates/'.$parts[2];
                               $installed[] = 'Directory '.$parts[2].' Created Successfully.';
                           }
                           else
                           {
                               //error creating directory
                               $failure = TRUE;
                               $failures[] = 'Creating '.$parts[2].' Template Directory Failed.';
                           }
                       }
//                       else
//                       {
                           //copy template files
                           if(copy('modules/Plugins/uploads/'.$plugin.'/'.$file, '../'.$file))
                           {
                               //success copying file
                               $installed[] = 'File '.$parts[3].' Installed Successfully.';
                               $uninstall[] = '../'.$file;
                           }
                           else
                           {
                               //error copying file
                               $installed[] = 'File '.$parts[3].' Installation Failed.';
                               $failure = TRUE;
                               $failures[] = 'File '.$parts[3].' Installation Failed.';
                           }
//                       }
                   }
                   
               }
               
           }
        }
       
        //merge all file arrays
       if(isset($sqltables)){$uninstall = array_merge($sqltables, $uninstall);}
       if(isset($folders)){$uninstall = array_merge($uninstall, $folders);}
       
       //set install status message
       if($failure == FALSE)
       {
           $status = 'Successful Installation.';
           
            //create uninstall file
           $deletefile = 'modules/Plugins/uploads/'.$plugin.'/uninstall.txt';
           $fh = fopen($deletefile, 'w');
           foreach($uninstall as $uni)
           {
               $line =$uni.'\n ';
               fwrite($fh, $uni.PHP_EOL);
           }
           fclose($fh);
           //end creating uninstall file
       }
       else
       {
           //install failed
           //remove any database tables, directories, and files that were installed
           foreach($uninstall as $file)
            {
                //check if it is a sql table and drop it if it is
                $sqltable = explode('*', $file);
                if(isset($sqltable[1]))
                {
                    if(!isset($tables)){$tables = array();}
                    $table = explode('*', $file);
                    $query = 'DROP TABLE '.$table[0];
                    DB::query($query);
                }
                else
                {
                    if(is_dir(trim($file)))
                    {
                        $directories[] = $file;
                    }
                    else
                    {
                        unlink(trim($file));
                    }
                }
            }
            if(isset($directories))
            {
            foreach($directories as $directory)
                {
                    if(file_exists(trim($directory).'/assets'))
                    {
                        rmdir(trim($directory).'/assets');
                    }
                    rmdir(trim($directory));
                }
            }
           //set status data for view file
           $status = 'Installation Failed.';
           //send failure messages to results screen
           $this->set('failures', $failures);
           //get plugin data for email to developer
           $info = file('modules/Plugins/uploads/'.$plugin.'/config.txt');
            foreach($info as $line)
            {
                $data = explode('=', $line);
                $config->$data[0] = trim($data[1]);
            }
           $this->set('config', $config);
           //end install failure
       }
       
       
       if(isset($assets)){$this->set('assets', $assets);}
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
        
        foreach($files as $file)
        {
            //check if it is a sql table and drop it if it is
            $sqltable = explode('*', $file);
            if(isset($sqltable[1]))
            {
                if(!isset($tables)){$tables = array();}
                $table = explode('*', $file);
                $query = 'DROP TABLE '.$table[0];
                DB::query($query);
                $tables[] = $table[0];
                if(DB::error() != '')
                {
                    $failure = TRUE;
                    $failmessages[] = 'Error Dropping Database Table '.$table[0].'. Remove Manually.';
                }
            }
            else
            {
                if(is_dir(trim($file)))
                {
                    $directories[] = trim($file);
                }
                else
                {
                    unlink(trim($file));
                    $messages[] = 'Removed File '.trim($file);
                }
            }
        }
        
        //remove the directories
        foreach($directories as $directory)
        {
            if(file_exists(trim($directory).'/assets'))
            {
                rmdir(trim($directory).'/assets');
                $messages[] = 'Removed Directory '.trim($directory).'/assets';
            }
            rmdir($directory);
            $messages[] = 'Removed Directory '.trim($directory);
        }
        
        unlink('modules/Plugins/uploads/'.$plugin.'/uninstall.txt');
        $messages[] = 'Removed uninstall token';
        
        if($failure == TRUE)($this->set('failmessages', $failmessages));
        if(isset($tables)){$this->set('sqltables', $tables);}
        $this->set('messages', $messages);
        $this->show('plugins/header');
        $this->show('plugins/uninstall');
        $this->show('plugins/footer');
        
    }
    
    public function upload()    {
        $this->show('plugins/header');
        $this->show('plugins/upload_form');
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