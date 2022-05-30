<?php


use App\Models\PartnerInfo;
use Illuminate\Support\Facades\Validator;

function makePostValidation(array $postData , array $validationCheckArray, array $responseData)
{
    // 유효성 체크
    $validatedData = Validator::make($postData, $validationCheckArray, _VALIDATION_ERROR_MESSAGE_);

    if($validatedData->fails()) {
        $responseData['code'] = _ERROR_CODE_['VALIDATION'];
        $responseData['data'] = $validatedData->errors();
    }

    // Partner_key 값의 유효성 체크
    if($responseData['code'] >= _ERROR_CODE_['DEFAULT']) {
        $partnerInfo = PartnerInfo::where('PARTNER_KEY',$postData['partner_key'])->where('USE_YN',1)->get();
        if(!$partnerInfo->first())  {
            $responseData['code'] = _ERROR_CODE_['PARTNER_KEY'];
            $responseData['data'] = array('message' => _ERROR_MESSAGE_['PARTNER_KEY']);
        }else {
            $responseData['partnerType'] = $partnerInfo[0]->PARTNER_TYPE;
        }
    }
    return $responseData;

}

