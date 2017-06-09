<?php

namespace App\Http\Controllers\Api;

use App\AmoCRMHelper;
use App\Jobs\SendNotificationTelegram;
use App\LeadCreateException;
use App\ContactCreateException;
use App\ContactUpdateException;
use App\TaskCreateException;
use App\Http\Controllers\Controller;
use Dotzero\LaravelAmoCrm\AmoCrmManager;
use Illuminate\Support\Facades\App;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Класс создания заявок в amoCRM
 *
 * @package App\Http\Controllers\api
 */
class RequestController extends Controller
{
	protected $amoCRMHelper;

	/**
	 * RequestController constructor.
	 */
	public function __construct() {
		$amocrm = App::make(AmoCrmManager::class);
		$this->amoCRMHelper = new AmoCRMHelper($amocrm);
	}

	/**
	 * Создает новое обращение пользователя
	 */
	public function create(Request $request) {
		$this->validate($request, [
			'name' => 'required|max:255',
			'phone' => 'required'
		], [
			'name.required' => 'Не указано имя',
			'phone.required'  => 'Не указан телефон',
		]);

		$name = $request['name'];
		$phone = $request['phone'];

		try {
			$leadId = $this->amoCRMHelper->createLead($name);

			$contact = $this->amoCRMHelper->findContactByPhone($phone);
			if (is_array($contact)) {
				$this->amoCRMHelper->updateContact($contact, $leadId);
			} else {
				$this->amoCRMHelper->createContact($name, $phone, $leadId);
			}

			$this->amoCRMHelper->createTask($leadId, AmoCRMHelper::TASK_ELEMENT_TYPE_LEAD, $name);

			$leadLink = $this->amoCRMHelper->getLeadLink($leadId);

			$this->dispatch(new SendNotificationTelegram($name, $phone, $leadLink));
		} catch(TaskCreateException $e) {
			return response()->json([ 'error' => 'Ошибка создания задачи' ])->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
		} catch(LeadCreateException $e) {
			return response()->json([ 'error' => 'Ошибка создания сделки' ])->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
		} catch(ContactCreateException $e) {
			return response()->json([ 'error' => 'Ошибка создания контакта' ])->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
		} catch(ContactUpdateException $e) {
			return response()->json([ 'error' => 'Ошибка обновления контакта' ])->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
		}

		return response()->json([ 'message' => 'Обращение успешно создано, мы свяжемся с вами в ближайшее время.' ]);
	}
}
