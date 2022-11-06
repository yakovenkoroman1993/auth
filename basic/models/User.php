<?php

namespace app\models;

use Exception;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;
use yii;

class User extends ActiveRecord implements IdentityInterface
{
    public static function tableName(): string
    {
        return "users";
    }

    public function rules(): array
    {
        return [
            [["email"], "required"]
        ];
    }

    /**
     * @param int|string $id
     * @return User|IdentityInterface|null
     */
    public static function findIdentity($id)
    {
        return static::findOne($id);
    }

    /**
     * @param mixed $token
     * @param null $type
     * @return User|IdentityInterface|null
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
//        throw new Exception("ERRRRORROR, $token");
        return static::findOne(['accessToken' => $token]);
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getAccessToken(): string {
        return $this->accessToken;
    }

    /**
     * @return string
     */
    public function getRegKey(): string {
        return $this->regKey;
    }

    /**
     * @throws Exception
     */
    public function getAuthKey()
    {
        throw new Exception("Not implemented");
    }

    /**
     * @param string $authKey
     * @throws Exception
     */
    public function validateAuthKey($authKey)
    {
        throw new Exception("Not implemented");
    }

    /**
     * @param bool $insert
     * @return bool
     * @throws yii\base\Exception
     */
    public function beforeSave($insert): bool
    {
        if (parent::beforeSave($insert)) {
            if ($this->isNewRecord) {
                $this->regKey = yii::$app->security->generateRandomString();
            }
            return true;
        }
        return false;
    }
}