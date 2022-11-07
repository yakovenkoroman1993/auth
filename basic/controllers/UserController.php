<?php

namespace app\controllers;

use app\models\User;
use DateInterval;
use DateTime;
use DateTimeZone;
use yii;

class UserController extends RestApiController
{
    private const ACCESS_TOKEN_EXPIRES_IN_SIGN_IN = "PT1H"; // hours
    private const ACCESS_TOKEN_EXPIRES_IN_SIGN_UP = "PT5M"; // minutes

    /**
     * @return array
     */
    public function actionIndex(): array
    {
        $currentUser = yii::$app->user;

        return $currentUser->identity->data();
    }

    /**
     * @return array
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionSave(): array {
        $currentUser = yii::$app->user;
        $request = yii::$app->request;
        $requestData = $request->post();
        $savingOfCurrentUser = !isset($requestData["id"]) ||
            (int) $requestData["id"] === $currentUser->getId();
        if ($savingOfCurrentUser) {
            $user = $currentUser->identity;
        } else {
            $user = User::findOne(["id" => $requestData["id"]]);
        }

        $user["firstName"] = $requestData["firstName"];
        $user["lastName"] = $requestData["lastName"];
        if (!empty($requestData["email"])) {
            $user["email"] = $requestData["email"];
        }
        if (!empty($requestData["role"])) {
            $user["role"] = $requestData["role"];
        }
        if (isset($requestData["enabled"])) {
            if ($savingOfCurrentUser &&
                !!$requestData["enabled"] !== !!$user["enabled"] &&
                !!$requestData["enabled"] === false
            ) {
                throw new yii\web\BadRequestHttpException("You can't disable yourself");
            }
            $user["enabled"] = !!$requestData["enabled"];
        }
        if(!$user->validate()) {
            return [
                "successfully" => false,
                "message" => $user->getErrors()
            ];
        }

        $user->save();

        return [
            "successfully" => true,
            "message" => "Your profile data has been saved successfully",
            "currentUser" => $currentUser->identity->data()
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
        $identity = User::findOne([
            "email" => $requestData["email"],
            "enabled" => true
        ]);
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
            "currentUser" => $user->data()
        ];
    }

    /**
     * @return array
     */
    public function actionAll(): array {
        return [
            "users" => User::find()
                ->select([
                    "id", "email", "firstName", "lastName",
                    "createdAt", "enabled", "role", "enabled"
                ])
                ->all()
        ];
    }

    /**
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionDelete(): array {
        $userId = (int)yii::$app->request->getQueryParam("id");
        if (!$userId) {
            throw new yii\web\BadRequestHttpException("Incorrect request");
        }
        if ($userId === yii::$app->user->getId()) {
            throw new yii\web\BadRequestHttpException("You can't remove yourself");
        }
        $user = User::findOne([
            "id" => $userId
        ]);
        if (!$user) {
            return [
                "successfully" => false,
                "message" => "Cannot be removed user with id $userId"
            ];
        }

        $user->delete();

        return [
            "successfully" => true,
            "message" => "Removed user with id $userId"
        ];
    }

    /**
     * @return array
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionClone(): array {
        $userId = (int)yii::$app->request->getQueryParam("id");
        if (!$userId) {
            throw new yii\web\BadRequestHttpException("Incorrect request");
        }
        $user = User::findOne([
            "id" => $userId
        ]);
        if (!$user) {
            return [
                "successfully" => false,
                "message" => "Cannot be cloned user with id $userId"
            ];
        }

        $clonedUser = new User();
        $data = $user->toArray();
        unset($data["id"]);
        unset($data["accessToken"]);
        unset($data["accessTokenExpiresAt"]);
        $clonedUser["email"] = "copy-" . time() . "-" . $data["email"];
        $clonedUser["firstName"] = $data["firstName"];
        $clonedUser["lastName"] = $data["lastName"];
        $clonedUser["enabled"] = $data["enabled"];
        $clonedUser["role"] = $data["role"];
        if(!$clonedUser->validate()) {
            return [
                "successfully" => false,
                "message" => $clonedUser->getErrors()
            ];
        }

        $clonedUser->save();
        $clonedUserId = $clonedUser["id"];

        return [
            "successfully" => true,
            "message" => "Cloned user with id $userId into $clonedUserId"
        ];
    }

    /**
     * @return array
     */
    public function actionSignOut(): array
    {
        $currentUser = yii::$app->user;
        $user = $currentUser->identity;
        $user["accessToken"] = null;
        $user["accessTokenExpiresAt"] = null;
        if(!$user->validate())
        {
            return [
                "successfully" => false,
                "message" => $user->getErrors()
            ];
        }
        $user->save();
        $currentUser->logout();

        return [
            "successfully" => true,
            "message" => "Logged out"
        ];
    }

}