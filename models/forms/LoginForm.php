<?php

namespace webvimark\modules\UserManagement\models\forms;

use app\models\Funcionario;
use app\models\PessoaFisica;
use webvimark\helpers\LittleBigHelper;
use webvimark\modules\UserManagement\models\User;
use webvimark\modules\UserManagement\UserManagementModule;
use yii\base\Model;
use Yii;

class LoginForm extends Model
{
    public $username;
    public $password;
    public $rememberMe = false;

    private $_user = false;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['username', 'password'], 'required'],
            ['rememberMe', 'boolean'],
            ['password', 'validatePassword'],

            ['username', 'validateIP'],
            ['username', 'validateFuncionario'],
        ];
    }

    public function validateFuncionario()
    {
        $user = $this->getUser();
        if(is_null($user)){
            return false;
        }
        if ($user->superadmin) {
            return true;
        }
        $pessoa = PessoaFisica::findOne(['cpf' => $user->username]);
        if (is_null($pessoa)) {
            $this->addError('username', UserManagementModule::t('front', "Funcionário não está cadastrado"));
            return false;
        }
        $funcionario = Funcionario::findOne(['pessoa_fisica_id' => $pessoa->id]);
        if (is_null($funcionario)) {
            $this->addError('username', UserManagementModule::t('front', "Funcionário não está cadastrado"));
            return false;
        }
        return true;
    }

    public function attributeLabels()
    {
        return [
            'username' => UserManagementModule::t('front', 'Username'),
            'password' => UserManagementModule::t('front', 'Password'),
            'rememberMe' => UserManagementModule::t('front', 'Remember me'),
        ];
    }

    /**
     * Validates the password.
     * This method serves as the inline validation for password.
     */
    public function validatePassword()
    {
        if (!Yii::$app->getModule('user-management')->checkAttempts()) {
            $this->addError('password', UserManagementModule::t('front', 'Too many attempts'));

            return false;
        }

        if (!$this->hasErrors()) {
            $user = $this->getUser();
            if (!$user || !$user->validatePassword($this->password)) {
                $this->addError('password', UserManagementModule::t('front', 'Incorrect username or password.'));
            }
        }
    }

    /**
     * Check if user is binded to IP and compare it with his actual IP
     */
    public function validateIP()
    {
        $user = $this->getUser();

        if ($user AND $user->bind_to_ip) {
            $ips = explode(',', $user->bind_to_ip);

            $ips = array_map('trim', $ips);

            if (!in_array(LittleBigHelper::getRealIp(), $ips)) {
                $this->addError('password', UserManagementModule::t('front', "You could not login from this IP"));
            }
        }
    }

    /**
     * Logs in a user using the provided username and password.
     * @return boolean whether the user is logged in successfully
     */
    public function login()
    {
        if ($this->validate()) {
            $user = $this->getUser();
            return Yii::$app->user->login($user, $this->rememberMe ? Yii::$app->user->cookieLifetime : 0);
        } else {
            return false;
        }
    }

    /**
     * Finds user by [[username]]
     * @return User|null
     */
    public function getUser()
    {
        if ($this->_user === false) {
            $u = new \Yii::$app->user->identityClass;
            $this->_user = ($u instanceof User ? $u->findByUsername($this->username) : User::findByUsername($this->username));
        }

        return $this->_user;
    }
}
