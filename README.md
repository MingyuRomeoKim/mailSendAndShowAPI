
# Mail 단건 전송 API 

- Mail Send To Apache Kafka
- Mail List Show From DB
- Mail Detail List show From DB

# description
- 필수 로직들만 push 함.
- 라라벨 8version 이상 사용
- model의 database table은 유동적으로 사용하고자 하는것들로 체인지
- Eloquent ORM Relationship은 사용할 수 없음 (모델의 동적 바인딩을 고려)
- 모델 설계는 같은 테이블 명의 앞글자만 다른 수많은 테이블이 생성됨에 따라 Eloquent ORM을 사용하는것이 비 효율적이라고 생각하여 동적 바인딩 처리로 모델 구현.
