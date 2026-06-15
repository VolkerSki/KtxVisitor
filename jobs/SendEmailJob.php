<?php

namespace humhub\modules\visitor\jobs;

use Yii;
use yii\base\BaseObject;
use yii\queue\JobInterface;

class SendEmailJob extends BaseObject implements JobInterface
{
    public $email;
    public $body;
    public $title;

    public function execute($queue)
    {
        Yii::$app->mailer->compose()
            ->setTo($this->email)
            ->setFrom(['ktxinside@kautex-group.com' => 'Visitor Report'])
            ->setSubject($this->title)
            ->setHtmlBody($this->body)
            ->send();
    }
}
