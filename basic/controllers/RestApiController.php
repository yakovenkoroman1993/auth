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
        $methods = ["GET", "POST", "PUT", "PATCH", "DELETE", "HEAD", "OPTIONS"];
        return parent::behaviors() + [
            "authenticator" => [
                "class" => HttpBearerAuth::class,
                "except" => ["sign-up", "sign-up-confirm", "sign-in"],
            ],
            "corsFilter"  => [
                "class" => Cors::class,
                "cors"  => [
                    "Origin"                           => yii::$app->params["permittedClientHostUrls"],
                    "Access-Control-Request-Method"    => $methods,
                    'Access-Control-Allow-Methods'     => $methods,
                    'Allow'                            => $methods,
                    "Access-Control-Allow-Credentials" => true,
                    "Access-Control-Allow-Headers"     => ["Authorization", "Origin", "X-Requested-With", "Content-Type", "Accept", "Version"],
                    'Access-Control-Request-Headers'   => ['*'],
                    "Access-Control-Max-Age"           => 86400,
                    "Access-Control-Allow-Origin"      => ['*'],

                ],
            ],
        ];
    }
}