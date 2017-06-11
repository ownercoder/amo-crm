<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;

/**
 * Класс API аватарок пользователей
 *
 * @package App\Http\Controllers\Api
 */
class ContactController extends Controller
{
	/**
	 * Расширение файла изображения
	 */
	const FILE_EXT = '.jpg';

	/**
	 * Возвращает массив загруженных аватарок пользователей
	 *
	 * @param Request $request
	 *
	 * @return string
	 */
	public function getAvatars(Request $request) {
		$idList = array_map('intval', $request['id']);
		$avatarList = [];

		foreach($idList as $id) {
			$filename = $id . self::FILE_EXT;
			if (Storage::exists($filename)) {
				$avatarList[] = [ 'id' => $id, 'url' => Storage::url($filename) ];
			} else {
				$avatarList[] = [ 'id' => $id, 'url' => false ];
			}
		}

		return response()->json([ 'result' =>  $avatarList]);
	}

	/**
	 * Удаляет загруженную аватарку пользователя
	 *
	 * @param Request $request
	 *
	 * @return string
	 */
	public function removeAvatar(Request $request) {
		$id = intval($request['id']);
		$filename = $id . self::FILE_EXT;
		$success = Storage::delete($filename);

		return response()->json([ 'result' =>  $success]);
	}

	/**
	 * Загружает аватарку пользователя
	 *
	 * @param Request $request
	 *
	 * @return string
	 */
	public function uploadAvatar(Request $request) {
		try {
			$this->validate($request, [
				'file' => 'required|image'
			], [
				'file.required' => 'Изображение не передано',
				'file.image'    => 'Изображение имеет не верный формат',
			]);
		} catch(ValidationException $e) {
			return response()->json(['message' => 'Ошибка проверки загруженного файла'])->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
		}

		$file = $request->file('file');
		$id = intval($request['id']);
		$filename = $id . self::FILE_EXT;
		$success = $file->storeAs('/', $filename);

		if (!$success) {
			return response()->json(['message' => 'Не удалось загрузить файл'])->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
		}

		return response()->json([ 'result' =>  Storage::url($filename)]);
	}
}
