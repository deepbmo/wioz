# introduction
일하면서 도움이 될 만한 코드와 개념 정리

<br />

# contents
- [비트연산](#bitwise-operation)
- [서버주소](#ozim)

<br />
<br />
<br />

<a name="bitwise-operation"></a>

# 비트연산
비트연산자로 데이터를 저장하고 확인하는 법

### 저장
`|` 연산을 이용해 더해서 저장한다.
```
1 | 2 | 4 = 7
1 | 1 = 1
```

### 확인
`&` 연산을 이용해 확인할 수 있다.
```
7 & 2 = 2
7 & 8 = 0
```

### query 활용
```php
$lists = $this->db()->select($this->table->program);
if ($type == 'info') $lists->where('type & 4 = 0'); // 포함되지 않은 것
else $lists->where('type & 4 > 0'); // 포함된 것
$lists = $lists->get();
```

<br />

<a name="ozim"></a>

# 서버주소
개발 서버와 운영 서버 주소 확인하는 법

### 분기
```php
if (strpos($_SERVER['HTTP_HOST'],'ozim.kr') > 0 == true) {
  $photo = 'http://'.$_SERVER['HTTP_HOST'].$member->photo;
} else {
  $photo = 'https://'.$_SERVER['HTTP_HOST'].$member->photo;
}
```

<br />