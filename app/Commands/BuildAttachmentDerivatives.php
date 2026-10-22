<?php
namespace App\Commands;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Database;
use App\Services\AttachmentDerivativeService;

/** Operator-triggered bounded batches; never invoked by upload, cron or deployment. */
class BuildAttachmentDerivatives extends BaseCommand
{
    protected $group='Maintenance';
    protected $name='attachments:derive';
    protected $description='Build private raster derivatives in explicit clinic/cursor batches; originals are retained.';
    protected $usage='attachments:derive --clinic ID [--after-id ID] [--limit 25]';
    public function run(array $params)
    {
        $clinic=CLI::getOption('clinic');$after=CLI::getOption('after-id')??'0';$limit=CLI::getOption('limit')??'25';
        foreach ([$clinic,$after,$limit] as $value) { if(!is_string($value)||!preg_match('/^[0-9]+$/D',$value)){CLI::error('Explicit numeric clinic/cursor/limit required.');return EXIT_ERROR;} }
        if((int)$clinic<1||(int)$limit<1||(int)$limit>100){CLI::error('Derivatives disabled or invalid explicit batch.');return EXIT_ERROR;}
        $db=Database::connect();$service=new AttachmentDerivativeService($db);
        if(CLI::getOption('report')!==null){CLI::write(json_encode($service->report((int)$clinic,(int)$after,(int)$limit)));return EXIT_SUCCESS;}
        if(!config('MediaDerivatives')->enabled){CLI::error('Derivative generation is disabled.');return EXIT_ERROR;}
        $rows=$db->table('file_attachments')->select('id')->where('clinic_id',(int)$clinic)->where('deleted_at',null)
            ->where('id >',(int)$after)->whereIn('mime_type',['image/jpeg','image/jpg','image/png','image/webp'])->orderBy('id','ASC')->limit((int)$limit)->get()->getResultArray();
        $last=(int)$after;$failed=0;
        foreach($rows as$row){$last=(int)$row['id'];try{foreach(['thumbnail','preview']as$kind)$service->build((int)$clinic,$last,$kind);}catch(\Throwable$e){$failed++;CLI::error('Attachment '.$last.' failed; original retained. '.get_class($e));}}
        CLI::write(json_encode(['clinic_id'=>(int)$clinic,'processed'=>count($rows),'failed'=>$failed,'next_after_id'=>$last,'retry'=>'Repeat the prior cursor to retry failures; existing derivatives are idempotent.']));
        return $failed?EXIT_ERROR:EXIT_SUCCESS;
    }
}
