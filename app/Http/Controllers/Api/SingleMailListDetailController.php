<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Routing\Annotation\Route;

// Use Models
use App\Models\SingleMailList;


class SingleMailListDetailController extends Controller
{
    public $responseData;

    public function __construct()
    {
        // 결과값 선언
        $this->responseData = array (
            'code' => _ERROR_CODE_['DEFAULT'], // 결과 코드 (-1:알 수 없는 문제로 실패 / 1:성공)
            'data' => array(),
        );
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    #[Route('/api/mail/single/result-detail')]
    public function index(Request $request)
    {
        // 유효성 검사
        $this->responseData = makePostValidation($request->post(),[
            'partner_key' => ['required'], // 업체 키
            'category' => ['required'], // 업체 내에서 각 기능별로 전송한 메일을 구분하기 위한 값
            'uuid' => ['required'], // 아이템 고유값
        ],$this->responseData);

        if($this->responseData['code'] < _ERROR_CODE_['DEFAULT'] ) return response()->json($this->responseData);

        // 모델 정의
        $singleMailListModel = new SingleMailList;
        $singleMailListModel->bind('apiDB','MT_SINGLE_MAIL_LIST');

        // 동적 바인딩을 위한 테이블 앞의 매체 선언 및 유효성검사에서 얻은 responseData의 partnerType 삭제
        $mailList = $this->responseData['partnerType'].'_SINGLE_MAIL_LIST';
        $mailResult = $this->responseData['partnerType'].'_SINGLE_MAIL_RESULT';
        $mailFile = $this->responseData['partnerType'].'_SINGLE_MAIL_FILE';
        $mailContent = $this->responseData['partnerType'].'_SINGLE_MAIL_CONTENT';
        unset($this->responseData['partnerType']);

        // 데이터 추출
        try {
            $responseModelData = $singleMailListModel
                ->join($mailResult,"{$mailList}.IDX","=","{$mailResult}.LIST_IDX")
                ->leftJoin($mailFile,"{$mailList}.IDX","=","{$mailFile}.LIST_IDX")
                ->select([
                    "{$mailList}.UUID",
                    "{$mailList}.SEND_DATE",
                    "{$mailList}.END_DATE",
                    "{$mailList}.TITLE",
                    "{$mailList}.STATUS AS LIST_STATUS",
                    DB::raw("(SELECT CONTENT FROM {$mailContent} WHERE LIST_IDX = {$mailList}.IDX) as CONTENT"),
                    DB::raw("(SELECT TYPE FROM {$mailContent} WHERE LIST_IDX = {$mailList}.IDX) as CONTENT_TYPE"),
                    DB::raw("group_concat({$mailFile}.NAME separator ',' ) as FILENAME"),
                    DB::raw("group_concat({$mailResult}.TYPE separator ',' ) as TYPE"),
                    DB::raw("group_concat({$mailResult}.EMAIL separator ',' ) as EMAIL"),
                    DB::raw("group_concat(IFNULL({$mailResult}.NAME,'') separator ',' ) as NAME"),
                    DB::raw("group_concat({$mailResult}.IS_READ separator ',' ) as IS_READ"),
                    DB::raw("group_concat({$mailResult}.STATUS separator ',' ) as STATUS"),
                ])
                ->where("{$mailList}.CATEGORY",$request->post('category'))
                ->where("{$mailList}.UUID",hex2bin($request->post('uuid')))
                ->groupBy("{$mailList}.IDX")
                ->get();

            if(!$responseModelData->isEmpty()) {
                foreach ($responseModelData as $key => $val) {
                    // 1차 가공 데이터
                    $this->responseData['data'][$key]['uuid'] = bin2hex($val['UUID']);
                    $this->responseData['data'][$key]['send_date'] = $val['SEND_DATE'];
                    $this->responseData['data'][$key]['end_date'] = $val['END_DATE'];
                    $this->responseData['data'][$key]['title'] = $val['TITLE'];
                    $this->responseData['data'][$key]['file_name'] = $val['FILE_NAME'];
                    $this->responseData['data'][$key]['content'] = $val['CONTENT'];
                    $this->responseData['data'][$key]['content_type'] = $val['CONTENT_TYPE'];
                    $this->responseData['data'][$key]['status'] = $val['LIST_STATUS'];

                    // 2차 가공 데이터
                    $dumpEmail = explode(',',$val['EMAIL']);
                    $dumpType = explode(',',$val['TYPE']);
                    $dumpName = explode(',',$val['NAME']);
                    $dumpIsRead = explode(',',$val['IS_READ']);
                    $dumpStatus = explode(',',$val['STATUS']);
                    $x = $y = $z = 0;
                    for ($i = 0; $i < count($dumpEmail) ; $i++) {
                        switch ($dumpType[$i]) {
                            case 1 : // TO
                                $this->responseData['data'][$key]['to'][$x]['email'] =  $dumpEmail[$i];
                                $this->responseData['data'][$key]['to'][$x]['name'] =  $dumpName[$i];
                                $this->responseData['data'][$key]['to'][$x]['status'] =  $dumpStatus[$i];
                                $this->responseData['data'][$key]['to'][$x]['is_read'] =  $dumpIsRead[$i];
                                $x++;
                                break;
                            case 2 : // CC
                                $this->responseData['data'][$key]['cc'][$y]['email'] =  $dumpEmail[$i];
                                $this->responseData['data'][$key]['cc'][$y]['name'] =  $dumpName[$i];
                                $this->responseData['data'][$key]['cc'][$y]['status'] =  $dumpStatus[$i];
                                $this->responseData['data'][$key]['cc'][$y]['is_read'] =  $dumpIsRead[$i];
                                $y++;
                                break;
                            case 3 : // BCC
                                $this->responseData['data'][$key]['bcc'][$z]['email'] =  $dumpEmail[$i];
                                $this->responseData['data'][$key]['bcc'][$z]['name'] =  $dumpName[$i];
                                $this->responseData['data'][$key]['bcc'][$z]['status'] =  $dumpStatus[$i];
                                $this->responseData['data'][$key]['bcc'][$z]['is_read'] =  $dumpIsRead[$i];
                                $z++;
                                break;
                        }
                    }

                }
                $this->responseData['code'] = _SUCCESS_CODE_['DEFAULT'];
            } else {
                $this->responseData['code'] = _ERROR_CODE_['NO_DATA'];
                $this->responseData['data'] = array('message' => _ERROR_MESSAGE_['NO_DATA']);
            }
        } catch (\Exception $exception) {
            $this->responseData['data'] = array('message' => $exception->getMessage());
        }

        // 결과값 리턴
        return response()->json($this->responseData);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
