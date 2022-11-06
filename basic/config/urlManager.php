<?php

return [
    'enablePrettyUrl' => true,
    'showScriptName' => false,
    'rules' => [
        [
            'class' => 'yii\rest\UrlRule',
            'controller' => 'user',
        ],
        'user/sign-up/confirm' => 'user/sign-up-confirm',
        'user' => 'user/index',
    ],
];