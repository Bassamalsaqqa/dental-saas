<?php

declare(strict_types=1);
namespace App\Services;

use CodeIgniter\Database\BaseConnection;
use DomainException;

/** Explicit clinic reads; public Bearer requests never borrow a session settings cache. */
final class ClinicSchedulingSettings
{
    private BaseConnection $db;
    public function __construct(BaseConnection $db) { $this->db=$db; }

    public function get(int $clinicId,string $key,$default=null)
    {
        if ($clinicId<=0) throw new DomainException('Clinic context required.');
        $row=$this->db->table('settings')->where(['clinic_id'=>$clinicId,'setting_key'=>$key])->get()->getRowArray();
        if (!$row) return $default;
        $value=$row['setting_value'];
        if ($row['setting_type']==='json') {
            $decoded=json_decode($value,true);
            if (json_last_error()!==JSON_ERROR_NONE) throw new DomainException('Invalid clinic scheduling configuration.');
            return $decoded;
        }
        return $value;
    }
    public function hours(int $clinicId): array
    {
        $start=$this->get($clinicId,'working_hours_start','09:00');
        $end=$this->get($clinicId,'working_hours_end','17:00');
        $days=$this->get($clinicId,'working_days',['monday','tuesday','wednesday','thursday','friday']);
        $valid=['monday','tuesday','wednesday','thursday','friday','saturday','sunday'];
        if (!self::validTime($start)||!self::validTime($end)||$start>=$end||!is_array($days)||array_diff($days,$valid)) throw new DomainException('Invalid clinic working hours.');
        return ['start'=>$start,'end'=>$end,'days'=>$days];
    }
    public function timezone(int $clinicId): \DateTimeZone
    {
        $name=$this->get($clinicId,'timezone','Asia/Jerusalem');
        try { return new \DateTimeZone($name); }
        catch (\Throwable $e) { throw new DomainException('Invalid clinic timezone.'); }
    }
    public function chairs(int $clinicId): array
    {
        $global=$this->db->table('settings')->where('clinic_id',null)->where('setting_key','default_booking_chair_max')->get()->getRowArray();
        $max=max(1,min(3,(int)($global['setting_value']??3)));
        $count=min($max,max(1,(int)$this->get($clinicId,'booking_chair_count',1)));
        $names=$this->get($clinicId,'booking_chair_names',[]);
        if (!is_array($names)) $names=[];
        $result=[];
        foreach($names as $name) {
            if(!is_scalar($name)) throw new DomainException('Invalid legacy booking configuration.');
            $clean=trim((string)$name); if($clean!=='') $result[]=$clean;
        }
        for($i=count($result);$i<$count;$i++) $result[]='Doctor '.($i+1);
        return array_slice($result,0,$count);
    }

    public static function validTime($value): bool
    {
        return is_string($value)&&preg_match('/^(?:[01][0-9]|2[0-3]):[0-5][0-9]$/D',$value)===1;
    }
    public static function validDate($value): bool
    {
        if (!is_string($value)||!preg_match('/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/D',$value,$m)) return false;
        return checkdate((int)$m[2],(int)$m[3],(int)$m[1]);
    }
    public static function positiveInt($value,int $max=2147483647): ?int
    {
        if (!is_int($value)&&!is_string($value)) return null;
        $raw=(string)$value;
        if (!preg_match('/^[1-9][0-9]*$/D',$raw)||strlen($raw)>strlen((string)$max)||($raw>(string)$max&&strlen($raw)===strlen((string)$max))) return null;
        $id=(int)$raw; return $id<=$max?$id:null;
    }
}
