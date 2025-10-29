<?php
namespace Beeralex\Notification\Events;

use Bitrix\Main;
use Bitrix\Main\Sms\Event;

/**
 * Класс для отправки SMS уведомлений, используя стандартный модуль messageservice, но в событии onBeforeSendSms передется массив полей
 */
class SmsEvent extends Event
{
	public function send($directly = false)
	{
		$result = new Main\Result();

		if(!Main\Loader::includeModule("messageservice"))
		{
			$result->addError(new Main\Error("Module messageservice is not installed.", self::ERR_MODULE));
			return $result;
		}

		$senderId = Main\Config\Option::get("main", "sms_default_service");
		if($senderId == '')
		{
			//messageservice will try to use any available sender
			$senderId = null;
		}

		$messageListResult = $this->createMessageList();
		if (!$messageListResult->isSuccess())
		{
			return $result->addErrors($messageListResult->getErrors());
		}
		$messageList = $messageListResult->getData();

		foreach($messageList as $message)
		{
			$smsMessage = \Bitrix\MessageService\Sender\SmsManager::createMessage([
				'SENDER_ID' => $senderId,
				'MESSAGE_FROM' => $message->getSender(),
				'MESSAGE_TO' => $message->getReceiver(),
				'MESSAGE_BODY' => $message->getText(),
			]);

			$event = new Main\Event('main', 'onBeforeSendSms', [
				'message' => $smsMessage,
				'template' => $message->getTemplate(),
                'fields' => $this->fields, // добавлено для совместимости с SmsEventInterceptor
			]);
			$event->send();

			foreach($event->getResults() as $evenResult)
			{
				if($evenResult->getType() === Main\EventResult::ERROR)
				{
					continue 2;
				}
			}

			if($directly)
			{
				$smsResult = $smsMessage->sendDirectly();
			}
			else
			{
				$smsResult = $smsMessage->send();
			}

			if(!$smsResult->isSuccess())
			{
				$result->addErrors($smsResult->getErrors());
			}
		}

		return $result;
	}
}
