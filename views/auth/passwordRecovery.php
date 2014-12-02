<?php

use webvimark\modules\UserManagement\UserManagementModule;
use yii\bootstrap\ActiveForm;
use yii\captcha\Captcha;
use yii\helpers\Html;

/**
 * @var yii\web\View $this
 * @var yii\base\DynamicModel $model
 */

$this->title = UserManagementModule::t('front', 'Password recovery');
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="password-recovery">

	<h2 class="text-center"><?= $this->title ?></h2>

	<?php $form = ActiveForm::begin([
		'id'=>'user',
		'layout'=>'horizontal',
		'validateOnBlur'=>false,
	]); ?>

	<?= $form->field($model, 'email')->textInput(['maxlength' => 255, 'autofocus'=>true]) ?>

	<?= $form->field($model, 'captcha')->widget(Captcha::className(), [
		'template' => '<div class="row"><div class="col-sm-2">{image}</div><div class="col-sm-3">{input}</div></div>',
		'captchaAction'=>['/user-management/auth/captcha']
	]) ?>

	<div class="form-group">
		<div class="col-sm-offset-3 col-sm-9">
			<?= Html::submitButton(
				'<span class="glyphicon glyphicon-ok"></span> ' . UserManagementModule::t('front', 'Recover'),
				['class' => 'btn btn-primary']
			) ?>
		</div>
	</div>

	<?php ActiveForm::end(); ?>

</div>
