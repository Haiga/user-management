<?php

namespace webvimark\modules\UserManagement\controllers;

use app\models\Funcionario;
use app\models\PessoaFisica;
use app\models\CdComplementares;
use webvimark\components\AdminDefaultController;
use Yii;
use webvimark\modules\UserManagement\models\User;
use webvimark\modules\UserManagement\models\search\UserSearch;
use yii\web\NotFoundHttpException;

/**
 * UserController implements the CRUD actions for User model.
 */
class UserController extends AdminDefaultController
{
	/**
	 * @var User
	 */
	public $modelClass = 'webvimark\modules\UserManagement\models\User';

	/**
	 * @var UserSearch
	 */
	public $modelSearchClass = 'webvimark\modules\UserManagement\models\search\UserSearch';

	/**
	 * @return mixed|string|\yii\web\Response
	 */
	public function actionCreate()
	{
        $idFuncionario=Yii::$app->getSession()->get('idFuncionario');
		$model = new User(['scenario'=>'newUser']);
        $func = Funcionario::findOne(['id' => $idFuncionario]);
        if (is_null($func)) {
            Yii::$app->getSession()->setFlash('error','Funcionário inválido!');
            return $this->redirect(['/']);
        }else{
            $pessoa = PessoaFisica::findOne(['id' => $func->pessoa_fisica_id]);
            if (is_null($pessoa)) {
                Yii::$app->getSession()->setFlash('error', 'Funcionário inválido!');
                return $this->redirect(['/']);
            }
        }

		if ( $model->load(Yii::$app->request->post()))
		{
            $model->username = str_pad($pessoa->cpf, 11, '0', STR_PAD_LEFT);
		    if($model->save()){
			    $funcao = CdComplementares::findOne(['id'=>$func->funcao]);
			    if(!is_null($funcao)){
		            	User::assignRole($model->getId(),$funcao->nome);
                	    }
                
                return $this->redirect(['view',	'id' => $model->id]);
            }
		}
        $model->username = str_pad($pessoa->cpf, 11, '0', STR_PAD_LEFT);
		return $this->renderIsAjax('create', compact('model'));
	}

	/**
	 * @param int $id User ID
	 *
	 * @throws \yii\web\NotFoundHttpException
	 * @return string
	 */
	public function actionChangePassword($id)
	{
		$model = User::findOne($id);

		if ( !$model )
		{
			throw new NotFoundHttpException('User not found');
		}

		$model->scenario = 'changePassword';

		if ( $model->load(Yii::$app->request->post()) && $model->save() )
		{
			return $this->redirect(['view',	'id' => $model->id]);
		}

		return $this->renderIsAjax('changePassword', compact('model'));
	}

}
