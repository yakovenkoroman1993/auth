<?php

use yii\db\Schema;
use yii\db\Migration;

/**
 * Handles the creation of table `{{%users}}`.
 */
class m221102_193644_create_users_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('users', [
            'id' => $this->primaryKey(),
            'email' => $this->string()->notNull()->unique(),
            'firstName' => $this->string()->null(),
            'lastName' => $this->string()->null(),
            'regKey' => $this->string()->null(),
            'password' => $this->string()->null(),
            'accessToken' => $this->string()->null(),
            'accessTokenExpiresAt' => $this->timestamp()->null(),
            'enabled' => $this->boolean()->defaultValue(false),
            'role' => "ENUM('admin', 'user') DEFAULT 'user'",
            'createdAt' => $this->timestamp()->defaultExpression('NOW()'),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('users');
    }
}
