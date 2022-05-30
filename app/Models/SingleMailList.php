<?php

namespace App\Models;

use App\Traits\BindsDynamically;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SingleMailList extends Model
{
    use HasFactory,BindsDynamically;
    /**
     * 추후 각 [채널타입_] 으로 시작하는 똑같은 항목의 table들이 생길 것을 참고하여
     * table이 추가 될 때마다 model을 추가하는 것 보다
     * BindsDynamically Traits를 통하여 동적으로 모델을 활용하기 위해 맵핑하지 않음.
     */
}
