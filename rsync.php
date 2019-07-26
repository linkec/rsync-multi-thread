<?php
$rsync = new Rsync();
class Rsync
{
    protected $command = '';

    protected $thread = 0;

    protected $config = array(
        'multi-threads'=>2,
        'multi-daemon'=>false,
        'multi-level'=>1,
        'path'=>array()
    );

    public function __construct(){
        $threadFiles = glob( __DIR__ .'/'.'*.multi-rsync');
        foreach($threadFiles as $v){
            unlink($v);
        }
        $this->parseCommand();
        $this->getFolders();
        $this->scanDir($this->config['path'][1],0);
    }

    private function parseCommand(){
        global $argv;
        $_argv = $argv;
    
        //Remove php file arg
        array_shift($_argv);
    
        foreach($_argv as $v){
            //Parse multi arg.
            $prefix = substr($v,0,7);
            if($prefix=='--multi'){
                if(substr($v,0,15)=='--multi-threads'){
                    $this->config['multi-threads'] = intval(substr($v,16));
                }elseif(substr($v,0,13)=='--multi-level'){
                    $this->config['multi-level'] = intval(substr($v,14));
                }elseif(substr($v,0,15)=='--multi-daemon'){
                    $this->config['multi-daemon'] = true;
                }
            }elseif(substr($v,0,1)!='-'){
                if(empty($this->config['rsync'])){
                    $this->config['rsync'] = $v;
                }else{
                    $this->config['path'][] = $v;
                }
            }else{
                $this->command .= ' '.$v;
            }
        }
        if(count($this->config['path'])!=2){
            exit("Path Error.");
        }
    }

    private function getFolders(){
        $command = $this->config['rsync'].' -av --include="*/" --exclude="*" --progress '."{$this->config['path'][0]} {$this->config['path'][1]}";
        echo "Thread-Main::Rsync::getFolders".PHP_EOL;
        echo $command.PHP_EOL;
        $this->exec($command);
    }

    private function exec($cmd){
        exec($cmd);
        // $proc = popen($cmd, 'r');
        // while (!feof($proc))
        // {
        //     echo fread($proc, 4096);
        //     @ flush();
        // }
    }

    private function scanDir($dir,$level){
        if(is_dir($dir)){
            if ($dh = opendir($dir)) {
                $_level = $level+1;
                while (($file = readdir($dh)) !== false){
                    $_file = $dir."/".$file;
                    if($file!="." && $file!=".." && is_dir($_file)){

                        if($_level==$this->config['multi-level']){
                            echo $_level.PHP_EOL;
                            do{
                                $threadFiles = glob( __DIR__ .'/'.'*.multi-rsync');
                                $thread = count($threadFiles);
                                usleep(10000);
                            }while($thread>=$this->config['multi-threads']);
                            for($i=0;$i<$this->config['multi-threads'];$i++){
                                if(!file_exists(__DIR__ ."/$i.multi-rsync")){
                                    $threadId = $i;
                                    break;
                                }
                            }
                            touch(__DIR__ . "/$threadId.multi-rsync");
                            $this->forkOneThread($_file,$threadId);
                        }else{
                            $this->scanDir($_file,$_level);
                        }

                    }
                }
            }
        }
    }

    private function forkOneThread($folder,$thread){
        $pid = pcntl_fork();
        // For master process.
        if ($pid > 0) {
            // echo ("fork master ok $pid".PHP_EOL);
        } // For child processes.
        elseif (0 === $pid) {
            $st = microtime(true);
            echo ("Thread-$thread::start $folder".PHP_EOL);

            $src = $this->validDir(str_ireplace($this->config['path'][1],'',$folder),$this->config['path'][0]);
            $dest = $this->validDir($folder);
            $command = $this->config['rsync']." $src $dest".$this->command;
            echo $command.PHP_EOL;
            $this->exec($command);

            $et = microtime(true);
            echo ("Thread-$thread::finish $folder".PHP_EOL);
            unlink(__DIR__ . "/$thread.multi-rsync");
            exit(250);
        } else {
            exit("forkOneWorker fail".PHP_EOL);
        }
    }
    
    private function validDir($dir,$prefix=''){
        if(substr($dir,0,1)!='/'){
            $dir = '/'.$dir;
        }
        if(substr($dir,-1,1)!='/'){
            $dir .= '/';
        }
        return str_replace('//','/',$prefix.$dir);
    }
}
