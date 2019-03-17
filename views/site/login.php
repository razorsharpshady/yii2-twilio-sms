<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model app\models\LoginForm */
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;

$this->title = 'Login';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-login">
	<h1><?= Html::encode($this->title) ?></h1>

	<p>Please fill out the following fields to login:</p>

    <?php
    
$form = ActiveForm::begin([
        'id' => 'login-form',
        'layout' => 'horizontal',
        'fieldConfig' => [
            'template' => "{label}\n<div class=\"col-lg-3\">{input}</div>\n<div class=\"col-lg-7\">{error}</div>",
            'labelOptions' => [
                'class' => 'col-lg-2 control-label'
            ]   
        ]
    ]);
    ?>
	<div id="section-1">
        <?= $form->field($model, 'username')->textInput(['autofocus' => false,'placeholder'=>'+1234567890']) ?>
		<div class="form-group">
			<div class="col-lg-offset-2 col-lg-11">
                <?= Html::button('Send OTP',['class'=>'btn btn-primary','id'=>'request-otp-btn']); ?>
            </div>
		</div>
	</div>
	<div id="section-2" style="display:none;">
	    <?= $form->field($model, 'password')->passwordInput() ?>

        <?=$form->field($model, 'rememberMe')->checkbox(['template' => "<div class=\"col-lg-offset-2 col-lg-3\">{input} {label}</div>\n<div class=\"col-lg-8\">{error}</div>"])?>

        <div class="form-group">
			<div class="col-lg-offset-2 col-lg-11">
                <?= Html::button('Login', ['class' => 'btn btn-primary', 'id' => 'login-button']) ?>
            </div>
		</div>
	</div>
    <?php ActiveForm::end(); ?>
</div>

<?php
$otpUrl = Url::toRoute(['site/send-otp']);
$loginUrl = Url::toRoute(['site/login']);
$loginSuccessUrl = Url::toRoute('site/index');
$csrf =Yii::$app->request->csrfToken;
$script = <<< JS

    $('button#request-otp-btn').click(function(){
        sendOtp();
    });
    function sendOtp() {
      
        $('#login-form').yiiActiveForm('validateAttribute', 'loginform-username'); //To Validate the phone/username field first before sending the OTP
        setTimeout(function(){
          var username = $('#loginform-username');
          var phone = username.val(); 
          var isPhoneValid = ($('div.field-loginform-username.has-error').length==0);
          if(phone!='' && isPhoneValid){
              $.ajax({
                 url: '$otpUrl',
                 data: {phone: phone,_csrf:'$csrf'},
                 method:'POST',
                 beforeSend:function(){
                        $('button#request-otp-btn').prop('disabled',true);
                    },
                error:function( xhr, err ) {
                            alert('Error');     
                     },
                 complete:function(){
                        $('button#request-otp-btn').prop('disabled',false);
                    },
                 success: function(data){
                            if(data.success==false){
                                alert(data.msg);
                                return false;
                            }else{
                                $('#section-1').hide();
                                $('#section-2').show();
                                alert(data.msg);
                            }
                            
                   }
              });
           }
        }, 200);
         
    }
     $('button#login-button').click(function(){
        login();
    });
    function login(){
        var form = $('#login-form') 
        form.yiiActiveForm('validateAttribute', 'loginform-password'); //To Validate the password/otp field
        setTimeout(function(){
          var otp = $('#loginform-password').val();
          var isOtpValid = ($('div.field-loginform-password.has-error').length==0);
          if(otp!='' && isOtpValid){
              $.ajax({
                 url: '$loginUrl',
                 data:form.serialize(),
                 dataType: 'json',
                 method:'POST',
                 beforeSend:function(){
                        $('button#login-button').prop('disabled',true);
                       },
                 error:function( xhr, err ) {
                      alert('An error occurred,please try again');     
                  },
                 complete:function(){
                        $('button#login-button').prop('disabled',false);
                    },
                 success: function(data){
                            if(data.success==true){
                                alert(data.msg);
                                window.location="$loginSuccessUrl";
                            }else{
                                alert(data.msg);
                            }
                            
                   }
              });
           }
        }, 200);
    }
    
JS;
$position = \yii\web\View::POS_READY;
$this->registerJs($script, $position);
?>