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
    protected $usage='attachments:derive --clinic ID [--after-id ID] [--limit 25] [--report]';
    public function run(array $params)
    {
        $clinic = $this->unsignedOption(CLI::getOption('clinic'), 1, min(PHP_INT_MAX, 4294967295));
        $after = $this->unsignedOption(CLI::getOption('after-id') ?? '0', 0, PHP_INT_MAX);
        $limit = $this->unsignedOption(CLI::getOption('limit') ?? '25', 1, 100);
        if ($clinic === null || $after === null || $limit === null) {
            CLI::error('Explicit bounded numeric clinic/cursor/limit required.');
            return EXIT_ERROR;
        }
        $report = CLI::getOption('report') !== null;
        if (!$report && !config('MediaDerivatives')->enabled) {
            CLI::error('Derivative generation is disabled.');
            return EXIT_ERROR;
        }
        $db = Database::connect();
        $service = new AttachmentDerivativeService($db);
        if ($report) {
            CLI::write(json_encode($service->report($clinic, $after, $limit), JSON_THROW_ON_ERROR));
            return EXIT_SUCCESS;
        }
        $rows=$db->table('file_attachments')->select('id')->where('clinic_id',(int)$clinic)->where('deleted_at',null)
            ->where('id >',(int)$after)->whereIn('mime_type',['image/jpeg','image/jpg','image/png','image/webp'])->orderBy('id','ASC')->limit((int)$limit)->get()->getResultArray();
        $last=(int)$after;$failed=0;
        foreach($rows as$row){$last=(int)$row['id'];try{foreach(['thumbnail','preview']as$kind)$service->build((int)$clinic,$last,$kind);}catch(\Throwable$e){$failed++;CLI::error('Attachment '.$last.' failed; original retained. '.get_class($e));}}
        CLI::write(json_encode(['clinic_id'=>(int)$clinic,'processed'=>count($rows),'failed'=>$failed,'next_after_id'=>$last,'retry'=>'Repeat the prior cursor to retry failures; existing derivatives are idempotent.']));
        return $failed?EXIT_ERROR:EXIT_SUCCESS;
    }

    /** Compare decimal strings before casting, so overflow cannot select another batch. */
    private function unsignedOption($value, int $minimum, int $maximum): ?int
    {
        if (!is_string($value) || !preg_match('/^[0-9]+$/D', $value)) {
            return null;
        }
        $digits = ltrim($value, '0');
        $digits = $digits === '' ? '0' : $digits;
        $bound = (string) $maximum;
        if (strlen($digits) > strlen($bound) || (strlen($digits) === strlen($bound) && strcmp($digits, $bound) > 0)) {
            return null;
        }
        $number = (int) $digits;
        return $number < $minimum ? null : $number;
    }
}
