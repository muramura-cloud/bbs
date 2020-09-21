<?php  

// バリデーションルールは外部ファイルで管理できる。
// バリデーションルールのキーを引数として受け取る関数を定義すれば良い。 
function get_validation_rules($input_keys)
{
    $validation_rules = [
        'title' => [
            'name'  => 'タイトル',
            'rules' => [
                'required' => true,
                'length'   => ['min' => 10, 'max' => 32],
            ],
        ],
        'message' => [
            'name'  => 'メッセージ',
            'rules' => [
                'required' => true,
                'length'   => ['min' => 10, 'max' => 200],
            ],
        ],
        'password' => [
            'name'  => 'パスワード',
            'rules' => [
                'pattern'  => ['regex' => '/^[0-9]{4}+$/', 'meaning' => '半角4桁の数字'],
            ],
        ],
    ];

    $rules = [];
    foreach ($validation_rules as $input_key => $validation_rule) {
        if (in_array($input_key,$input_keys)) {
            $rules[$input_key] = $validation_rule;
        }
    }

    return $rules;
}
$validation_rules = get_validation_rules($input_keys);
