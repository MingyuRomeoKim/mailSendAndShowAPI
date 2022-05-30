<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Symfony\Component\Routing\Annotation\Route;


/**
 * RD_KAFKA 관련 상수 정의
 * https://github.com/edenhill/librdkafka/blob/master/src/rdkafka.h
 *
 * @author mingyu@mt.co.kr
 * @since 2022.05.16
 */
class SingleMailController extends Controller
{
    public $responseData;

    public function __construct()
    {
        // 결과값 선언
        $this->responseData = [
            'code' => _ERROR_CODE_['DEFAULT'], // 결과 코드 (-1:알 수 없는 문제로 실패 / 1:성공)
            'data' => array('message' => _ERROR_MESSAGE_['DEFAULT']),
        ];
    }

    public function checkValidationByEmails($emails, $type)
    {
        $return = true;
        if(is_array($emails) && count($emails) > 0) {
            foreach ($emails as $key => $value) {
                // email은 필수 값이라 무조건 검사
                if(empty($value['email']) || !$value['email']) {
                    $this->responseData['code'] = _ERROR_CODE_['EMAIL_'.$type];
                    $this->responseData['data'] = _ERROR_MESSAGE_['EMAIL_'.$type];
                    $return = false;
                } else {
                    // email 값이 있다면 email 형식이 맞는지 정규식으로 체크
                    if (preg_match("/^[_\.0-9a-zA-Z-]+@([0-9a-zA-Z][0-9a-zA-Z-]+\.)+[a-zA-Z]{2,6}$/i", $value['email']) == false) {
                        $this->responseData['code'] = _ERROR_CODE_['EMAIL_'.$type];
                        $this->responseData['data'] = _ERROR_MESSAGE_['EMAIL_'.$type];
                        $return = false;
                    }
                }
                // name은 선택 값이지만 key값이 있다면 null체크
                if(array_key_exists('name',$value)) {
                    if(empty($value['name']) || !$value['name']) {
                        $this->responseData['code'] = _ERROR_CODE_['EMAIL_NAME'];
                        $this->responseData['data'] = array('message' => _ERROR_MESSAGE_['EMAIL_NAME']);
                        $return = false;
                    }
                }
            }
        }
        return $return;
    }

    #[Route('/api/mail/single/send')]
    public function send(Request $request)
    {
        // 1차 유효성 검사
        $this->responseData = makePostValidation($request->post(),[
            'partner_key' => ['required'], // 업체 키
            'category' => ['required'], // 업체 내에서 각 기능별로 전송한 메일을 구분하기 위한 값
            'from' => ['required','array'],
            'from.email'=>['required'], // 발신자 메일 주소
            'to' => ['required','array'], // 수신자 메일 주소
            'cc' => ['nullable','array'], // 참조 메일 주소
            'bcc' => ['nullable','array'], // 숨은참조 메일 주소
            'title' => ['required'], // 메일 제목
            'content_type' => ['required'], // 전송할 메일 본문 형식
            'content' => ['required'], // 메일 본문
        ],$this->responseData);

        if($this->responseData['code'] < _ERROR_CODE_['DEFAULT'] ) return response()->json($this->responseData);

        // 유효성검사에서 얻은 responseData의 partnerType 삭제
        unset($this->responseData['partnerType']);

        // 2차 유효성 검사 - By Emails
        if(!$this->checkValidationByEmails($request->post('to'),'TO')) return response()->json($this->responseData);
        if(!$this->checkValidationByEmails($request->post('cc'),'CC')) return response()->json($this->responseData);
        if(!$this->checkValidationByEmails($request->post('bcc'),'BCC')) return response()->json($this->responseData);

        // Kafka 전송 데이터 설정
        if($request->post('partner_key'))
            $dataArray['partner_key'] = $request->post('partner_key') ;
        if($request->post('category'))
            $dataArray['category'] = $request->post('category') ;
        if($request->post('reservation_time'))
            $dataArray['reservation_time'] = $request->post('reservation_time') ;
        if($request->post('title'))
            $dataArray['title'] = $request->post('title') ;
        if($request->post('content'))
            $dataArray['content'] = $request->post('title') ;
        if($request->post('content_type'))
            $dataArray['content_type'] = $request->post('content_type');
        if($request->post('from'))
            $dataArray['from'] = $request->post('from');
        if($request->post('to'))
            $dataArray['to'] = $request->post('to');
        if($request->post('cc'))
            $dataArray['cc'] = $request->post('cc');
        if($request->post('bcc'))
            $dataArray['bcc'] = $request->post('bcc');
        if($request->post('file_name'))
            $dataArray['file_name'] = $request->post('file_name');

        $dataJson = json_encode($dataArray);

        // Kafka Config 초기화.
        $conf = new \RdKafka\Conf();
        $conf->set('metadata.broker.list', 'your_kafka_domain:port');

        // Kafka Producer 초기화 및 사용.
        $producer = new \RdKafka\Producer($conf);
        $topic = $producer->newTopic(_KAFKA_TOPICS_['SINGLE_MAILE']);
        $topic->produce(RD_KAFKA_PARTITION_UA, 0,$dataJson);
        $producer->poll(0);
        $result = $producer->flush(10000);

        /** Success */
        // RD_KAFKA_RESP_ERR_NO_ERROR = 0, result = 0
        if (RD_KAFKA_RESP_ERR_NO_ERROR === $result) {
            $this->responseData['code'] = _SUCCESS_CODE_['DEFAULT'];
            $this->responseData['data']['message'] = _SUCCESS_MESSAGE_['DEFAULT'];
        }
        return response()->json($this->responseData);
    }
}
