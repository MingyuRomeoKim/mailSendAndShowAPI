<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PartnerInfo extends Model
{
    use HasFactory;

    protected $table = 'PARTNER_INFO'; // 테이블
    protected $primaryKey = 'IDX'; // 기본키
    const CREATED_AT = 'REG_DATE';
    public $timestamps = false;

}
