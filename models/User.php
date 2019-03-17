<?php
namespace app\models;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

class User extends ActiveRecord implements IdentityInterface
{

    public static function tableName()
    {
        return 'tbl_user';
    }
    
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['phone'], 'required'],
            [['phone'], 'unique'],
            ['phone', 'match', 'pattern' => '/^\+?[1-9]\d{1,14}$/'],
            [['otp'], 'string','min'=>4,'max'=>10]
        ];
    }
    
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'phone' => 'Phone Number',
            'otp' => 'OTP',
            'created_on' => 'Created On'
        ];
    }
    
    public function getUsername(){
        return $this->phone;
    }

    /**
     * Finds an identity by the given ID.
     *
     * @param string|int $id
     *            the ID to be looked for
     * @return IdentityInterface|null the identity object that matches the given ID.
     */
    public static function findIdentity($id)
    {
        return static::findOne($id);
    }

    /**
     * Finds an identity by the given token.
     *
     * @param string $token
     *            the token to be looked for
     * @return IdentityInterface|null the identity object that matches the given token.
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne([
            'auth_key' => $token
        ]);
    }

    /**
     *
     * @return int|string current user ID
     */
    public function getId()
    {
        return $this->id;
    }
    /**
     * Finds user by phone
     *
     * @param string $phone
     * @return static|null
     */
    public static function findByPhone($phone)
    {
        return static::findOne([
            'phone' => $phone
        ]);
    }
    
    /**
     *
     * @return string current user auth key
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     *
     * @param string $authKey
     * @return bool if auth key is valid for current user
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }
    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return bool if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return ($this->otp === $password && $this->otp_expire >= time());
    }
    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($this->isNewRecord) {
                $this->auth_key = \Yii::$app->security->generateRandomString();
            }
            return true;
        }
        return false;
    }
}