<?php

namespace app\controllers;

use yii\web\Controller;
use yii\web\Response;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\Cors;
use yii;

class RestApiController extends Controller
{
    public function init() {
        $this->enableCsrfValidation = false;
    }

    /**
     * @param yii\base\Action $action
     * @return bool
     * @throws yii\web\BadRequestHttpException
     */
    public function beforeAction($action): bool
    {
        yii::$app->response->format = Response:: FORMAT_JSON;

        return parent::beforeAction($action);
    }

    /**
     * @return array
     */
    public function behaviors(): array
    {
        return parent::behaviors() + [
            "authenticator" => [
                "class" => HttpBearerAuth::class,
                "except" => ["sign-up", "sign-up-confirm", "sign-in"],
            ],
        ];
    }
}