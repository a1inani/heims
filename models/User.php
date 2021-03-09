<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;


class User extends ActiveRecord implements IdentityInterface
{
    const STATUS_DELETED = 0;
    const STATUS_INACTIVE = 8;
    const STATUS_ACTIVE = 9;

    public static function tableName()
    {
        return 'user';
    }

    public function rules()
    {
        return [
            [['first_name', 'last_name', 'username',  'role'], 'required'],
            [['created_at', 'updated_at', 'updated_by', 'status', 'role'], 'integer'],
            [['first_name', 'other_name', 'last_name', 'username', 'password', 'auth'], 'string', 'max' => 255],
            [['username'], 'email'],
            [['role'], 'exist', 'skipOnError' => true, 'targetClass' => Role::className(), 'targetAttribute' => ['role' => 'id']],
            [['updated_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['updated_by' => 'id']],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'first_name' => 'First Name',
            'other_name' => 'Other Name',
            'last_name' => 'Last Name',
			'isLogged' => 'Is Logged',
            'username' => 'Email',
            'contact_no' => 'Contact Number',
            'password' => 'Password Hash',
            'auth' => 'Auth',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
            'role' => 'Role',
            'status' => 'Status'
        ];
    }
    /**
     * {@inheritdoc}
     */
    public static function findIdentity($id)
    {
        return isset(self::$users[$id]) ? new static(self::$users[$id]) : null;
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        foreach (self::$users as $user) {
            if ($user['accessToken'] === $token) {
                return new static($user);
            }
        }
        return null;
    }

    /**
     * Finds user by username
     *
     * @param string $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username]);
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }
    
     public function validatePassword($password) {
        return Yii::$app->security->validatePassword($password . $this->auth, $this->password);
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthKey()
    {
        return $this->auth;
    }

    /**
     * {@inheritdoc}
     */
    public function validateAuthKey($authKey)
    {
        return $this->auth === $authKey;
    }

   
    public function getFullName()
    {
        return $this->first_name." ".$this->other_name." ".$this->last_name;
    }

    public static function userIsAllowedTo($right) {
        $session = Yii::$app->session;
        $rights = explode(',', $session['rights']);
        if (in_array($right, $rights)) {
            return true;
        }
        return false;
    }
    
     public function getCurrentUserID(){
        $identity = Yii::$app->user->identity;
        $id = Yii::$app->user->id;
        
        return $id;
    }

    /**
     * @param $password
     * @throws \yii\base\Exception
     */
    public function setPassword($password) {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password.$this->auth);
    }
}
