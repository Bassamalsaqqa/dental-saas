<?php
/**
 * Manual frontend boundary/coverage gate. No application bootstrap, database or network.
 * php scripts/ci/frontend_rebuild_guard.php [--final]
 */
declare(strict_types=1);
$root=dirname(__DIR__,2);
$failures=[];
$git=static function(array $args)use($root):array{
    $process=proc_open(array_merge(['git','-c','core.safecrlf=false'],$args),[0=>['pipe','r'],1=>['pipe','w'],2=>['pipe','w']],$pipes,$root,null,['bypass_shell'=>true]);
    if(!is_resource($process))throw new RuntimeException('Git could not start');
    fclose($pipes[0]);$output=stream_get_contents($pipes[1]);$error=stream_get_contents($pipes[2]);fclose($pipes[1]);fclose($pipes[2]);
    return [proc_close($process),trim((string)$output),trim((string)$error)];
};
$lock=json_decode((string)file_get_contents($root.'/docs/UI_REBUILD_LOCK.json'),true,512,JSON_THROW_ON_ERROR);
$base=$lock['verified_remote_release']['commit']??'';
if(!preg_match('/^[a-f0-9]{40}$/',$base))throw new RuntimeException('Invalid release pin');
$protected=$lock['protected_baseline_git_objects']['objects']??[];
foreach($protected as $path=>$expected){
    [$code,$actual]=$git(['rev-parse',$base.':'.$path]);
    if($code!==0||$actual!==$expected)$failures[]='Protected release pin differs: '.$path;
}
if(!$protected)$failures[]='No protected source pins';
[$code,$changed]=$git(array_merge(['diff','--name-only',$base,'--'],array_keys($protected)));
if($code!==0||$changed!=='')$failures[]='Protected working/staged source differs: '.$changed;
[$code,$newProtected]=$git(array_merge(['ls-files','--others','--exclude-standard','--'],array_keys($protected)));
if($code!==0||$newProtected!=='')$failures[]='Untracked protected source: '.$newProtected;
$handle=fopen($root.'/docs/UI_REBUILD_COVERAGE.csv','r');
$header=fgetcsv($handle,0,',','"','\\');$rows=[];$statuses=[];$pending=[];$seen=[];
while(($line=fgetcsv($handle,0,',','"','\\'))!==false){
    if(count($line)!==count($header)){$failures[]='Malformed coverage row';continue;}
    $row=array_combine($header,$line);$path=$row['view_path'];
    if(isset($seen[$path]))$failures[]='Duplicate coverage entry: '.$path;
    $seen[$path]=true;$rows[$path]=$row;$state=$row['status'];$statuses[$state]=($statuses[$state]??0)+1;
    if($state==='REMOVED_HISTORICAL_ADDITION'){
        if(file_exists($root.'/'.$path))$failures[]='Removed historical view is present: '.$path;
        continue;
    }
    if(!is_file($root.'/'.$path)){$failures[]='Tracked frontend view is missing: '.$path;continue;}
    if(!in_array($state,['OWNER_ACCEPTED_ANCHOR','IMPLEMENTED_VERIFIED','PRESERVE_EXPLICIT_EXCEPTION','VERIFIED_NONVISUAL_DEPENDENCY','VERIFIED_UNUSED'],true))$pending[]=$path;
    if(in_array($state,['OWNER_ACCEPTED_ANCHOR','IMPLEMENTED_VERIFIED','IMPLEMENTED_PENDING_ACCEPTANCE','VERIFIED_NONVISUAL_DEPENDENCY'],true)){
        $source=(string)file_get_contents($root.'/'.$path);
        if(preg_match('~assets/(?:css|js)/(?:v4-|warm-|tailwind(?:\\.css)?)~i',$source))$failures[]='Rejected visual asset import in converted view: '.$path;
        if(preg_match('~\$studioIcon\s*\(\s*[\x27\x22](?:arrow|chevron|down|logout)[\x27\x22]~',$source))$failures[]='Arrow icon call in converted view: '.$path;
        if(str_contains($source,"lang('V4."))$failures[]='Retired translation dependency: '.$path;
    }
}
fclose($handle);
$views=0;
foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root.'/app/Views',FilesystemIterator::SKIP_DOTS)) as $file){
    if(!$file->isFile())continue;$views++;
    $relative=str_replace('\\','/',substr($file->getPathname(),strlen($root)+1));
    if(!isset($seen[$relative]))$failures[]='Unregistered view: '.$relative;
}
$workflowDir=$root.'/.github/workflows';
if(is_dir($workflowDir)){
    foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($workflowDir,FilesystemIterator::SKIP_DOTS)) as $file){
        if($file->isFile())$failures[]='GitHub Actions file present: '.$file->getFilename();
    }
}
$isFinal=in_array('--final',$argv,true);
if($isFinal){
    foreach($rows as $path=>$row){
        if(in_array($row['status'],['REMOVED_HISTORICAL_ADDITION','VERIFIED_NONVISUAL_DEPENDENCY','PRESERVE_EXPLICIT_EXCEPTION'],true))continue;
        if(!$row['acceptance_evidence']||str_contains($row['route_and_state_mapping'],'PENDING'))$failures[]='Missing final route/state evidence: '.$path;
    }
    if($pending)$failures[]='Frontend campaign has '.count($pending).' unfinished view entries';
}
ksort($statuses);
echo json_encode(['scope'=>$isFinal?'final_readiness':'source_boundaries_and_inventory','release'=>$base,'view_files_present'=>$views,'ledger_entries'=>count($rows),'statuses'=>$statuses,'unfinished_views'=>count($pending),'campaign_complete'=>!$pending&&!$failures,'failures'=>$failures],JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES).PHP_EOL;
exit($failures?1:0);
