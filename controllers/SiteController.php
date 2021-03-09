<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\Role;
use app\models\User;
use app\models\LoginForm;
use app\models\ContactForm;

class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    [
                        'actions' => ['login'],
                        'allow' => true,
                        'roles' => ['?']
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        $session = Yii::$app->session;
        if (!Yii::$app->user->isGuest) {
            $this->layout = 'main';
            $session = Yii::$app->session;
            
            return $this->render('index');
        }
            $this->layout = 'main-login';
            $model = new LoginForm();
            if ($model->load(Yii::$app->request->post())) {
            
                $user = User::findByEmail($model->username);
                if(!empty($user)){
                    if(User::checkPassword($user, $model->password)){
                       
                        $session = Yii::$app->session;
                        $session['user'] = $user->id;
                        $session['name'] = $user->getFullName();
                        $role = Role::findOne(['id' => $user->role]);
                        $session['role'] = $role->role;
                        $session['right'] = $role->right;
                    
                        if( $model->login()){
                        $this->layout = 'main';
                        return $this->render('index');
                        }else {
                            print("User not logged in!!!!");
                        }
                    }
                    $model->password = '';
                    $model->addError('password', 'Incorrect password.');
                }
                $model->password = '';
                $model->addError('email', 'Incorrect email address');
            }
            $model->password = '';
            return $this->render('login', [
                'model' => $model,
            ]);
    }

  
    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout()
    {
        $session = Yii::$app->session;
        $session['isLogged'] = 0;
        Yii::$app->user->logout();
        return $this->goHome();
    }
    
    public function actionSettings()
    {
        return $this->render('about');
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
            'model' => $model,
        ]);
    }

    /**
     * Displays about page.
     *
     * @return string
     */
   
}
