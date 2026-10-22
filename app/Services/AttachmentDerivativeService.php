<?php
namespace App\Services;
use CodeIgniter\Database\BaseConnection;
use Config\Database;

/** Bounded raster derivatives. Originals and signed/document files are never modified. */
final class AttachmentDerivativeService
{
    private string $base;
    private PlanGuard $quota;
    /** Optional coordination seam; never sourced from an HTTP request. */
    public $beforePublicationFn = null;
    public function __construct(private ?BaseConnection $db = null, ?string $uploadRoot = null, ?PlanGuard $quota = null)
    {
        $this->db ??= Database::connect();
        $this->base = $uploadRoot ?? WRITEPATH . 'uploads';
        $this->quota = $quota ?? new PlanGuard($this->db);
    }
    private function path(int $clinicId, string $relative): string
    {
        $tenant = realpath($this->base . '/clinic_' . $clinicId);
        $base = realpath($this->base);
        $path = realpath($this->base . '/' . $relative);
        $expected = $base ? $base . DIRECTORY_SEPARATOR . 'clinic_' . $clinicId : '';
        $sameTenant = $tenant && (PHP_OS_FAMILY==='Windows' ? strcasecmp($tenant,$expected)===0 : $tenant===$expected);
        if (!$base || !$sameTenant || !$path || is_link($this->base . '/clinic_' . $clinicId)
            || !str_starts_with($path, $tenant . DIRECTORY_SEPARATOR) || !is_file($path)) { throw new \DomainException('Private tenant artifact unavailable.'); }
        return $path;
    }
    private function source(int $clinicId, int $id): array
    {
        if ($clinicId <= 0 || $id <= 0) { throw new \DomainException('Attachment context required.'); }
        $row=$this->db->table('file_attachments')->where('clinic_id',$clinicId)->where('id',$id)->where('deleted_at',null)->get()->getRowArray();
        if (!$row) { throw new \DomainException('Attachment unavailable.'); }
        $path=$this->path($clinicId,$row['file_path']);
        $bytes=filesize($path);
        if($bytes===false||$bytes>config('MediaDerivatives')->maxSourceBytes)throw new \DomainException('Source exceeds derivative read bounds.');
        $mime=(new \finfo(FILEINFO_MIME_TYPE))->file($path);
        if (!in_array($mime,['image/jpeg','image/png','image/webp'],true) || !in_array(strtolower($row['mime_type']),[$mime,$mime==='image/jpeg'?'image/jpg':$mime],true)) {
            throw new \DomainException('Only verified raster images can have derivatives.');
        }
        return ['row'=>$row,'path'=>$path,'mime'=>$mime,'hash'=>hash_file('sha256',$path)];
    }
    private function ready(): void
    {
        if (!config('MediaDerivatives')->enabled || !$this->db->tableExists('attachment_variants')) { throw new \DomainException('Media derivatives are not enabled and provisioned.'); }
    }
    public function lookup(int $clinicId,int $id,string $kind): ?array
    {
        $this->ready();
        if (!in_array($kind,['thumbnail','preview'],true)) { throw new \DomainException('Unsupported derivative.'); }
        $source=$this->source($clinicId,$id);
        $row=$this->db->table('attachment_variants')->where(['clinic_id'=>$clinicId,'attachment_id'=>$id,'kind'=>$kind,'source_sha256'=>$source['hash']])->get()->getRowArray();
        if (!$row) { return null; }
        $path=$this->path($clinicId,$row['relative_path']);
        if (!in_array($row['mime_type'],['image/webp','image/png'],true) || (new \finfo(FILEINFO_MIME_TYPE))->file($path)!==$row['mime_type']) { throw new \DomainException('Invalid derivative MIME.'); }
        if (filesize($path)!==(int)$row['file_size'] || !hash_equals($row['file_sha256'],hash_file('sha256',$path))) { throw new \DomainException('Derivative integrity check failed.'); }
        return $row+['absolute_path'=>$path];
    }
    /** Read-only metadata integrity report; available even while delivery is OFF. */
    public function report(int $clinicId,int $afterId=0,int $limit=25): array
    {
        if($clinicId<1||$afterId<0||$limit<1||$limit>100||!$this->db->tableExists('attachment_variants'))throw new \DomainException('Invalid derivative report context.');
        $rows=$this->db->table('attachment_variants')->where('clinic_id',$clinicId)->where('id >',$afterId)->orderBy('id','ASC')->limit($limit+1)->get()->getResultArray();
        $more=count($rows)>$limit;if($more)array_pop($rows);$items=[];
        foreach($rows as$row){$status='verified';try{$path=$this->path($clinicId,$row['relative_path']);if(filesize($path)!==(int)$row['file_size']||!hash_equals($row['file_sha256'],hash_file('sha256',$path)))$status='artifact_mismatch';
                $source=$this->source($clinicId,(int)$row['attachment_id']);if(!hash_equals($row['source_sha256'],$source['hash']))$status='source_changed';
            }catch(\Throwable$e){$status='unavailable_or_retired_parent';}
            $items[]=['id'=>(int)$row['id'],'attachment_id'=>(int)$row['attachment_id'],'kind'=>$row['kind'],'status'=>$status];
        }
        return ['items'=>$items,'has_more'=>$more,'next_after_id'=>$more?(int)$rows[count($rows)-1]['id']:null,'scope'=>'Metadata-backed artifacts only; no orphan adoption, repair or deletion.'];
    }

    public function build(int $clinicId,int $id,string $kind): array
    {
        $this->ready();
        if (!in_array($kind,['thumbnail','preview'],true)) { throw new \DomainException('Unsupported derivative.'); }
        if ($existing=$this->lookup($clinicId,$id,$kind)) { return $existing; }
        if ((int)$this->db->transDepth !== 0) { throw new \RuntimeException('Derivative publication requires its own transaction.'); }
        $source=$this->source($clinicId,$id);$cfg=config('MediaDerivatives');
        $info=getimagesize($source['path']);
        if (!$info || $info[0]<1 || $info[1]<1 || $info[0]*$info[1]>$cfg->maxSourcePixels || filesize($source['path'])>$cfg->maxSourceBytes) { throw new \DomainException('Source exceeds safe derivative decoding bounds.'); }
        $limit=ini_get('memory_limit');$bytes=(int)$limit;
        if (preg_match('/^([0-9]+)([KMG])$/i',$limit,$m)) { $bytes=(int)$m[1]*(1024**(stripos('KMG',$m[2])+1)); }
        if ($bytes>0 && memory_get_usage(true)+$info[0]*$info[1]*12+16777216>$bytes) { throw new \DomainException('Insufficient bounded decode memory.'); }
        $load=match($source['mime']){'image/jpeg'=>'imagecreatefromjpeg','image/png'=>'imagecreatefrompng','image/webp'=>'imagecreatefromwebp'};
        if (!function_exists($load)) { throw new \DomainException('Raster decoder unavailable.'); }
        $image=$load($source['path']);if(!$image)throw new \RuntimeException('Raster decoding failed.');
        $output=null;$temp=null;
        try {
            // EXIF affects presentation only; original bytes and metadata remain unchanged.
            if ($source['mime']==='image/jpeg') {
                if (!function_exists('exif_read_data')) { throw new \DomainException('JPEG orientation reader unavailable.'); }
                $exif=@exif_read_data($source['path']);$orientation=(int)($exif['Orientation']??1);
                if(!in_array($orientation,range(1,8),true))throw new \DomainException('Invalid image orientation.');
                if(in_array($orientation,[2,4,5,7],true))imageflip($image,IMG_FLIP_HORIZONTAL);
                $angle=match($orientation){3,4=>180,6,7=>-90,5,8=>90,default=>0};
                if($angle){$rotated=imagerotate($image,$angle,0);if(!$rotated)throw new \RuntimeException('Orientation failed.');imagedestroy($image);$image=$rotated;}
            }
            $width=imagesx($image);$height=imagesy($image);$bound=$kind==='thumbnail'?480:1600;
            $scale=min(1,$bound/$width,2048/$height);$w=max(1,(int)round($width*$scale));$h=max(1,(int)round($height*$scale));
            $output=imagecreatetruecolor($w,$h);imagealphablending($output,false);imagesavealpha($output,true);
            if(!imagecopyresampled($output,$image,0,0,0,0,$w,$h,$width,$height))throw new \RuntimeException('Resampling failed.');
            $directory=$this->base.'/clinic_'.$clinicId.'/variants';
            if(is_link($directory)||(!is_dir($directory)&&!mkdir($directory,0700,true)))throw new \RuntimeException('Private derivative directory unavailable.');
            $tenant=realpath($this->base.'/clinic_'.$clinicId);$resolved=realpath($directory);
            $expectedVariant=$tenant.DIRECTORY_SEPARATOR.'variants';
            $sameVariant=$resolved&&(PHP_OS_FAMILY==='Windows'?strcasecmp($resolved,$expectedVariant)===0:$resolved===$expectedVariant);
            if(!$tenant||!$sameVariant)throw new \DomainException('Unsafe variant directory.');
            $webp=function_exists('imagewebp');$extension=$webp?'webp':'png';$mime=$webp?'image/webp':'image/png';
            $name=$id.'_'.$kind.'_'.$source['hash'].'.'.$extension;$final=$resolved.'/'.$name;
            $temp=$resolved.'/.'.bin2hex(random_bytes(16)).'.tmp';
            if(!($webp?imagewebp($output,$temp,80):imagepng($output,$temp,6)))throw new \RuntimeException('Derivative encode failed.');
            if(!hash_equals($source['hash'],hash_file('sha256',$source['path'])))throw new \DomainException('Source changed during processing.');
            $hash=hash_file('sha256',$temp);$size=filesize($temp);
            $handle=fopen($temp,'r+b');if(!$handle)throw new \RuntimeException('Derivative verification unavailable.');
            if(function_exists('fsync')&&!fsync($handle)){fclose($handle);throw new \RuntimeException('Derivative sync failed.');}fclose($handle);
            if(is_callable($this->beforePublicationFn)){($this->beforePublicationFn)();}
            if(!$this->db->transBegin())throw new \RuntimeException('Derivative publication transaction unavailable.');
            try {
                $clinic=$this->db->query('SELECT id FROM '.$this->db->escapeIdentifiers($this->db->prefixTable('clinics')).' WHERE id=? FOR UPDATE',[$clinicId])->getRowArray();
                $parent=$this->db->query('SELECT id FROM '.$this->db->escapeIdentifiers($this->db->prefixTable('file_attachments')).' WHERE clinic_id=? AND id=? AND deleted_at IS NULL FOR UPDATE',[$clinicId,$id])->getRowArray();
                if(!$clinic||!$parent)throw new \DomainException('Derivative parent no longer available.');
                if($winner=$this->lookup($clinicId,$id,$kind)){
                    if(!$this->db->transCommit())throw new \RuntimeException('Equivalent derivative transaction failed.');
                    return $winner;
                }
                $allowed=$this->quota->assertQuota($clinicId,'storage_gb',(int)$size,['entity_type'=>'attachment_variant','entity_id'=>$id]);
                if(is_array($allowed)&&empty($allowed['allowed']))throw new \DomainException('Derivative storage quota denied.');
                if(!@link($temp,$final)&&(!is_file($final)||!hash_equals($hash,hash_file('sha256',$final))))throw new \RuntimeException('Verified no-overwrite publication failed.');
            $record=['clinic_id'=>$clinicId,'attachment_id'=>$id,'kind'=>$kind,'source_sha256'=>$source['hash'],'file_sha256'=>$hash,'relative_path'=>'clinic_'.$clinicId.'/variants/'.$name,'mime_type'=>$mime,'file_size'=>$size,'width'=>$w,'height'=>$h,'created_at'=>date('Y-m-d H:i:s')];
            // Race winners are equivalent only after the complete stored artifact is verified.
            if(!$this->db->table('attachment_variants')->insert($record)) {
                throw new \RuntimeException('Derivative metadata publication failed; retain artifact for reconciliation.');
            }
            if($this->db->transStatus()===false||!$this->db->transCommit())throw new \RuntimeException('Derivative commit failed; retain published artifact for reconciliation.');
            } catch(\Throwable $e) { if((int)$this->db->transDepth>0)$this->db->transRollback(); throw $e; }
            return $this->lookup($clinicId,$id,$kind)??throw new \RuntimeException('Published derivative unavailable.');
        } finally {if($output)imagedestroy($output);imagedestroy($image);if($temp&&is_file($temp))unlink($temp);}
    }
}
