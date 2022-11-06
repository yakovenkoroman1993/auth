<?php

namespace app\controllers;

use app\models\User;
use DateInterval;
use DateTime;
use DateTimeZone;
use yii;

class UserController extends RestApiController
{
//    private const ACCESS_TOKEN_EXPIRES_IN_SIGN_IN = "PT24H"; // hours
    private const ACCESS_TOKEN_EXPIRES_IN_SIGN_IN = "PT30M";
    private const ACCESS_TOKEN_EXPIRES_IN_SIGN_UP = "PT5M"; // minutes

    /**
     * @return array
     */
    public function actionIndex(): array
    {
        $currentUser = yii::$app->user;

        return [
            "userId" => $currentUser->getId(),
            "isGuest" => $currentUser->isGuest,
            "currentUser" => $currentUser->identity->attributes
        ];
    }

    /**
     * @return array
     */
    public function actionSignUp(): array
    {
        $request = yii::$app->request;
        $requestData = $request->post();
        $userAlreadyExists = User::find()->where(["email" => $requestData["email"]])->count() > 0;
        if ($userAlreadyExists) {
            return [
                "successfully" => false,
                "message" => "User already exists with such email"
            ];
        }

        $user = new User();
        $user->attributes = $requestData;
        if(!$user->validate()) {
            return [
                "successfully" => false,
                "message" => $user->getErrors()
            ];
        }

        $user->save();
        $clientOrigin = $request->getOrigin();
        $confirmUrl = "$clientOrigin/sign-up/confirm?key=". $user->getRegKey();

        yii::$app->mailer
            ->compose()
            ->setFrom(yii::$app->params["emailFrom"])
            ->setTo($user->attributes["email"])
            ->setSubject("Confirm your created account")
            ->setTextBody("")
            ->setHtmlBody('Click by <a href="'. $confirmUrl .'">link</a> in order to finish registration.')
            ->send();

        return [
            "successfully" => true,
            "message" => "User is created successfully. Confirm registration by link sent to your email"
        ];
    }

    /**
     * @return array
     * @throws \Throwable
     * @throws yii\base\Exception
     * @throws yii\db\StaleObjectException
     */
    public function actionSignUpConfirm(): array
    {
        $security = yii::$app->security;
        $requestData = yii::$app->request->post();
        $user = User::find()
            ->where([
                "enabled" => false,
                "regKey" => $requestData["regKey"]
            ])
            ->one();
        if (!$user) {
            return [
                "successfully" => false,
                "message" => "User doesn't exist"
            ];
        }
        $now = new DateTime();
        $createdAt = new DateTime($user->attributes["createdAt"]);
        $expiresAt = $createdAt->add(new DateInterval(static::ACCESS_TOKEN_EXPIRES_IN_SIGN_UP));
        if ($expiresAt < $now) {
            $user->delete();
            return [
                "successfully" => false,
                "message" => "Registration link was expired"
            ];
        }

        $user["enabled"] = true;
        $user["regKey"] = null;
        $user["password"] = $security->generatePasswordHash($requestData["password"]);
        if(!$user->validate())
        {
            return [
                "successfully" => false,
                "message" => $user->getErrors()
            ];
        }

        $user->save();

        return [
            "successfully" => true,
            "message" => "User is activated successfully"
        ];
    }

    /**
     * @throws yii\base\Exception
     */
    public function actionSignIn(): array
    {
        $errorResponse = [
            "successfully" => false,
            "message" => "Authentication failed"
        ];
        $security = yii::$app->security;
        $currentUser = yii::$app->user;
        $requestData = yii::$app->request->post();
        $identity = User::findOne([ "email" => $requestData["email"]]);
        if (!$identity) {
            return $errorResponse;
        }
        if (!$security->validatePassword(
            $requestData["password"],
            $identity->attributes["password"])
        ) {
            return $errorResponse;
        }

        $successfully = $currentUser->login($identity);
        if (!$successfully) {
            return $errorResponse;
        }

        $accessToken = $security->generateRandomString();
        $accessTokenExpiresAt = (new DateTime())
            ->setTimezone(new DateTimeZone("UTC"))
            ->add(new DateInterval(static::ACCESS_TOKEN_EXPIRES_IN_SIGN_IN))
            ->format(yii::$app->params["dateTimeFormat"])
        ;

        $user = $currentUser->identity;
        $user["accessToken"] = $accessToken;
        $user["accessTokenExpiresAt"] = $accessTokenExpiresAt;
        if(!$user->validate())
        {
            $errorResponse["message"] .= ". Reason: Invalid user";
            return $errorResponse;
        }

        $user->save();

        return [
            "successfully" => true,
            "message" => "Authentication success",
            "currentUser" => $user->attributes
        ];
    }
}