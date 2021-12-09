<?php

namespace Modules\Coin\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Overtrue\EasySms\Message;
use Leonis\Notifications\EasySms\Channels\EasySmsChannel;

class SystemWalletMinBalance extends Notification implements ShouldQueue
{

    use Queueable;

    public $msg;

    public function __construct($msg = '')
    {
        $this->msg = $msg;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     *
     * @return array
     */
    public function via($notifiable)
    {
        if (isset($notifiable->routes['mail'])) {
            return ['mail'];
        } else { //使用easySms通道发送短信
            return [EasySmsChannel::class];
        }
    }

    public function toMail($notifiable)
    {

        $lineText = "系统热钱包余额不足，请及时补充处理";
        if ($this->msg) {
            $lineText .= " 详情：【" . $this->msg . "】";
        }
        return (new MailMessage())
            ->subject('系统钱包余额邮件通知')
            ->greeting('系统钱包管理者您好：')
            ->line($lineText);
    }

    public function toEasySms($notifiable)
    {
        $msg = "系统热钱包余额不足，请及时处理"; //发送内容（这样写肯定过不了短信风控）
        return (new Message())->setContent($msg);
    }
}
