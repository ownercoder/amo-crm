<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Telegram\Bot\Laravel\Facades\Telegram;

/**
 * Отложенная задача отправки уведомления в Telegram
 *
 * @package App\Jobs
 */
class SendNotificationTelegram implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var string
     */
    protected $name;
    /**
     * @var string
     */
    protected $phone;
    /**
     * @var string
     */
    protected $leadLink;

    /**
     * Create a new job instance.
     *
     * @param string $name Имя
     * @param string $phone Телефон
     * @param string $leadLink Ссылка на сделку
     */
    public function __construct($name, $phone, $leadLink)
    {
        $this->name = $name;
        $this->phone = $phone;
        $this->leadLink = $leadLink;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle() {
        $groupId = env('TELEGRAM_GROUP_ID');
        $textTemplate = env('TELEGRAM_MESSAGE_TEMPLATE');

        $text = str_replace(
            array('{name}', '{leadLink}', '{phone}'),
            array($this->name, $this->leadLink, $this->phone), $textTemplate);

        Telegram::sendMessage([
            'chat_id' => $groupId,
            'text' => $text
        ]);
    }
}
