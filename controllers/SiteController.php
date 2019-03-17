<?php
namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;
use app\models\User;
use yii\db\Expression;
use Twilio\Rest\Client;

class SiteController extends Controller
{

    /**
     *
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout','send-otp','login'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@']
                    ],
                    [
                        'actions' => ['send-otp','login'],
                        'allow' => true,
                        'roles' => ['?']
                    ]]
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                    'send-otp' => ['post']
                ]]
        ];
    }

    /**
     *
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction'
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null
            ]
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * Login action.
     *
     * @return Response|string
     */
    public function actionLogin()
    {
        if (! Yii::$app->user->isGuest) {
            return $this->goHome();
        }
        
        $model = new LoginForm();
        if (\Yii::$app->request->isAjax){
            \Yii::$app->response->format = 'json';
            $model->load(Yii::$app->request->post());
            if ($model->login()) {
                $response = [
                    'success' => true,
                    'msg' => 'Login Successful'
                ];
            } else {
                $error = implode(", ", \yii\helpers\ArrayHelper::getColumn($model->errors, 0, false)); // Model's Errors string
                $response = [
                    'success' => false,
                    'msg' => $error
                ];
            }
            return $response;
        }
        $model->password = '';
        return $this->render('login', [
            'model' => $model
        ]);
    }

    public function actionSendOtp()
    {
        $phone = \Yii::$app->request->post('phone');
        \Yii::$app->response->format = 'json';
        $response = [];
        if ($phone) {
            $user = \app\models\User::findByPhone($phone);
            $otp = rand(100000, 999999); // a random 6 digit number
            if ($user == null) {
                $user = new \app\models\User();
                $user->phone = $phone;
                $user->created_on = time();
            }
            $user->otp = "$otp";
            $user->otp_expire = time() + 600; // To expire otp after 10 minutes
            if (! $user->save()) {
                $errorString = implode(", ", \yii\helpers\ArrayHelper::getColumn($user->errors, 0, false)); // Model's Errors string
                $response = [
                    'success' => false,
                    'msg' => $errorString
                ];
            } else {
                $msg = 'One Time Passowrd(OTP) is ' . $otp;
                
                $sid = \Yii::$app->params['twilioSid'];
                $token = \Yii::$app->params['twiliotoken'];
                $twilioNumber = \Yii::$app->params['twilioNumber'];
                try{
                    $client = new \Twilio\Rest\Client($sid, $token);
                    $client->messages->create($phone, [
                        'from' => $twilioNumber,
                        'body' => (string) $msg
                    ]);
                    $response = [
                        'success' => true,
                        'msg' => 'OTP Sent and valid for 10 minutes.'
                    ];
                }catch(\Exception $e){
                    $response = [
                        'success' => false,
                        'msg' => $e->getMessage()
                    ];
                }
                
            }
        } else {
            $response = [
                'success' => false,
                'msg' => 'Phone number is empty.'
            ];
        }
        return $response;
    }

    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();
        
        return $this->goHome();
    }

    /**
     * Displays contact page.
     *
     * @return Response|string
     */
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->contact(Yii::$app->params['adminEmail'])) {
            Yii::$app->session->setFlash('contactFormSubmitted');
            
            return $this->refresh();
        }
        return $this->render('contact', [
            'model' => $model
        ]);
    }

    /**
     * Displays about page.
     *
     * @return string
     */
    public function actionAbout()
    {
        return $this->render('about');
    }
}
