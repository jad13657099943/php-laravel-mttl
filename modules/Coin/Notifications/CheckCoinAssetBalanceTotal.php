<?php


namespace Modules\Coin\Notifications;


use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Leonis\Notifications\EasySms\Channels\EasySmsChannel;
use Overtrue\EasySms\Message;

class CheckCoinAssetBalanceTotal extends Notification implements ShouldQueue
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

        $lineText = "币种余额总数变动异常提醒";
        if ($this->msg) {
            $lineText .= " 详情：【" . $this->msg . "】";
        }

        return (new MailMessage())
            ->subject('币种余额总数变动异常提醒')
            ->greeting('系统钱包管理者您好：')
            ->line($lineText);
    }

    public function toEasySms($notifiable)
    {
        $msg = "币种余额总数变动异常提醒，请及时处理"; //发送内容（这样写肯定过不了短信风控）
        return (new Message())->setContent($msg);
    }

}
