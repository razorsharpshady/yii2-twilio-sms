<h1 align="center">
   Yii 2 User Login+Signup using Twilio SMS
</h1>


### Install

Download/clone this respository and then run below <a href="https://getcomposer.org/">*composer*</a> command in your project directory to install the required dependencies:

```
$ php composer.phar install
```

or run

```
$ composer install
```

<p> Next create a MySQL database and import the <b>db/twilio_sms.sql</b> file in the created database, and configure the <b>config/db.php</b> with the right credentials</p>

```
return [
    'class' => 'yii\db\Connection',
    'dsn' => 'mysql:host=localhost;dbname=twilio_sms',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8',
    ];
```


<p> Setup your twilio credentials in <b>config/params.php</b></p>

```
return [
    'adminEmail' => 'admin@example.com',
    'twilioSid' => 'AC1c4ea1a103xx2xxx53ba3d3b1xx40830', //replace with your sid
    'twiliotoken' => '3xx460c3516xx8xx535f5b4181d00xx5', //replace with your token
    'twilioNumber'=>'+19999123456'//replace with your Twilio phone number
    
];
```

Now you should be able to access the application through the following URL, assuming `yii2-twilio-sms`
 is the directory directly under the Web root.

~~~
http://localhost/yii2-twilio-sms/web/
~~~
