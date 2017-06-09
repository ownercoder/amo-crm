<?php
namespace App;

use Dotzero\LaravelAmoCrm\AmoCrmManager;

/**
 * Класс помощник для создания цепочки связей задач и сделок с контактом
 *
 * @package App
 */
class AmoCRMHelper {
	/**
	 * Типы элементов
	 */
	const ELEMENT_TYPE_CONTACTS = 'contacts';
	const ELEMENT_TYPE_LEADS = 'leads';
	const ELEMENT_TYPE_CUSTOMERS = 'customers';

	/**
	 * Типы элементов задач
	 */
	const TASK_ELEMENT_TYPE_CONTACT = 1;
	const TASK_ELEMENT_TYPE_LEAD = 2;
	const TASK_ELEMENT_TYPE_COMPANY = 3;

	/**
	 * @var AmoCrmManager
	 */
	protected $amoCRMManager;
	/**
	 * @var array Список дополнительных полей
	 */
	protected $customFieldList;
	/**
	 * @var array Список статусов
	 */
	protected $leadStatusList;
	/**
	 * @var array Список типов задач
	 */
	protected $taskTypeList;

	/**
	 * AmoCRMHelper constructor.
	 *
	 * @param AmoCrmManager $manager
	 */
	public function __construct(AmoCrmManager $manager) {
		$this->amoCRMManager = $manager;
		$this->initFields();
	}

	/**
	 * Выполняет поиск контакта по телефону, при найденом контакте вернет массив контакта
	 *
	 * @param string $phone Телефон
	 *
	 * @return bool|array
	 */
	public function findContactByPhone($phone) {
		$contact            = $this->amoCRMManager->contact;
		$foundedContactList = $contact->apiList(['query' => $phone]);
		if (!empty($foundedContactList)) {
			$firstContact = array_shift($foundedContactList);
			return $firstContact;
		}

		return false;
	}

	/**
	 * Создает задачу
	 *
	 * @param int $elementId ID связываемого элемента
	 * @param int $elementType Тип связываемого элемента (константы из AmoCRMHelper::TASK_ELEMENT_TYPE_*)
	 * @param string $name Имя контакта
	 *
	 * @return int
	 */
	public function createTask($elementId, $elementType, $name) {
		$defaultTaskTypeId = $this->getDefaultTaskTypeId();
		$taskTemplateText  = env('AMO_DEFAULT_TASK_TEXT_TEMPLATE');

		$task                 = $this->amoCRMManager->task;
		$task['element_id']   = $elementId;
		$task['element_type'] = $elementType;
		$task['task_type']    = $defaultTaskTypeId;
		$task['text']         = sprintf($taskTemplateText, $name);

		return (int)$task->apiAdd();
	}

	/**
	 * Создает контакт
	 *
	 * @param string $name Имя контакта
	 * @param string $phone Телефон
	 * @param int $leadId Id сделаки для связи
	 *
	 * @return int
	 * @throws ContactCreateException
	 */
	public function createContact($name, $phone, $leadId) {
		$phoneFieldId     = $this->getFieldIdByTypeCode(self::ELEMENT_TYPE_CONTACTS, 'PHONE');
		$defaultPhoneType = env('AMO_DEFAULT_CONTACT_PHONE_TYPE');

		$contact         = $this->amoCRMManager->contact;
		$contact['name'] = $name;
		$contact->addCustomField($phoneFieldId, $phone, $defaultPhoneType);
		$contact['linked_leads_id'] = [$leadId];

		$contactId = (int)$contact->apiAdd();

		if ($contact->getLastHttpCode() != 200) {
			throw new ContactCreateException();
		}

		return $contactId;
	}

	/**
	 * Обновляет сделки контакта
	 *
	 * @param array $contact Массив с Контактом
	 * @param int $leadId Id сделки
	 *
	 * @throws ContactUpdateException
	 */
	public function updateContact($contact, $leadId) {
		$updateContact                    = $this->amoCRMManager->contact;
		$updateContact['linked_leads_id'] = array_merge($contact['linked_leads_id'], array($leadId));

		$updateContact->apiUpdate($contact['id']);
		if ($updateContact->getLastHttpCode() != 200) {
			throw new ContactUpdateException();
		}
	}

	/**
	 * Создает сделку
	 *
	 * @param string $name Имя клиента
	 *
	 * @return int Id сделки
	 *
	 * @throws LeadCreateException
	 */
	public function createLead($name) {
		$leadNameTemplate = env('AMO_DEFAULT_LEAD_NAME_TEMPLATE');

		$leadName = sprintf($leadNameTemplate, $name);
		$statusId = $this->getDefaultLeadStatusId();

		$lead = $this->amoCRMManager->lead;

		$lead['name']      = $leadName;
		$lead['status_id'] = $statusId;

		$leadId = (int)$lead->apiAdd();

		if ($lead->getLastHttpCode() != 200) {
			throw new LeadCreateException();
		}

		return $leadId;
	}

	/**
	 * Инициализирует дополнительные свойства
	 */
	protected function initFields() {
		$account = $this->amoCRMManager->account->apiCurrent();
		if (!empty($account['custom_fields'])) {
			$this->customFieldList = $account['custom_fields'];
		}

		if (!empty($account['leads_statuses'])) {
			$this->leadStatusList = $account['leads_statuses'];
		}

		if (!empty($account['task_types'])) {
			$this->taskTypeList = $account['task_types'];
		}
	}

	/**
	 * Получает идентификатор поля
	 *
	 * @param string $type Тип элемента (константы из AmoCRMHelper::ELEMENT_TYPE_*)
	 * @param string $code Код поля
	 *
	 * @return int|bool
	 */
	protected function getFieldIdByTypeCode($type, $code) {
		foreach ($this->customFieldList[$type] as $field) {
			if (!empty($field['code']) && $field['code'] == $code) {
				return $field['id'];
			}
		}

		return false;
	}

	/**
	 * Получает идентификатор статуса сделки по умолчанию
	 *
	 * @return bool|int
	 */
	protected function getDefaultLeadStatusId() {
		$defaultStatusText = env('AMO_DEFAULT_LEAD_STATUS');

		foreach ($this->leadStatusList as $leadStatus) {
			if ($leadStatus['name'] == $defaultStatusText) {
				return $leadStatus['id'];
			}
		}

		return false;
	}

	/**
	 * Получает идентификатор задачи по умолчанию
	 *
	 * @return bool|int
	 */
	protected function getDefaultTaskTypeId() {
		$defaultType = env('AMO_DEFAULT_TASK_TYPE');

		foreach ($this->taskTypeList as $taskType) {
			if ($taskType['code'] == $defaultType) {
				return $taskType['id'];
			}
		}

		return false;
	}

	/**
	 * Получает ссылку на сделку
	 *
	 * @param int $leadId Идентификатор сделки
	 *
	 * @return string
	 */
	public function getLeadLink($leadId) {
		return sprintf('https://%s.%s/leads/detail/%d', env('AMO_DOMAIN'), env('AMO_CRM_DOMAIN'), $leadId);
	}
}