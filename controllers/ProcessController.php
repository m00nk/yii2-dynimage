<?php
/**
 * @author Dmitrij "m00nk" Sheremetjev <m00nk1975@gmail.com>
 * Date: 29.09.16, Time: 2:30
 */

namespace m00nk\dynimage\controllers;

use Imagine\Exception\RuntimeException;
use Imagine\Image\ImageInterface;
use Imagine\Imagick\Imagine;
use Yii;
use yii\web\Controller;
use yii\web\HttpException;
use yii\web\Response;

class ProcessController extends Controller
{
	public function actionGetImage($filepath)
	{
		$_idx = strrpos($filepath, '=');
		if($_idx === false) throw new HttpException(404);

		$srcFilePath = Yii::getAlias('@webroot/').substr($filepath, 0, $_idx);
		if(!file_exists($srcFilePath)) throw new HttpException(404, 'Wrong path: '.$filepath.' == '.$srcFilePath);

		$dstFilePath = Yii::getAlias('@webroot').Yii::$app->dynimage->cachePath.'/'.$filepath;
		Yii::$app->dynimage->createFolderForFile($dstFilePath);

		list($params, $ext) = explode('.', substr($filepath, $_idx + 1));
		$ext = strtolower($ext);
		list($width, $height, $quality) = explode('x', $params);

		// масштабируем
		$imagine = new Imagine();
		$img = $imagine->open($srcFilePath);
		$_size = $img->getSize();
		$_dx = $_size->getWidth();
		$_dy = $_size->getHeight();
		$_maxW = $width > 0 ? $width : 99999999;
		$_maxH = $height > 0 ? $height : 99999999;

		if($_dx > $_maxW || $_dy > $_maxH)
		{
			if($_dx / $_dy > $_maxW / $_maxH)
				$_size = $_size->widen($_maxW);
			else
				$_size = $_size->heighten($_maxH);

			$img = $img->resize($_size, ImageInterface::FILTER_QUADRATIC);
		}

		$img->save($dstFilePath, ['quality' => $quality]);

		chmod($dstFilePath, 0666);

//		$img->show($ext); // работает криво - получаем ошибки, что данные пошли раньше заголовоков. Поэтому просто дублируем здесь функционал.

		static $mimeTypes = [
			'jpeg' => 'image/jpeg',
			'jpg' => 'image/jpeg',
			'gif' => 'image/gif',
			'png' => 'image/png',
			'wbmp' => 'image/vnd.wap.wbmp',
			'xbm' => 'image/xbm',
		];

		if(!isset($mimeTypes[$ext]))
		{
			throw new RuntimeException(sprintf('Unsupported format given. Only %s are supported, %s given', implode(", ", array_keys($mimeTypes)), $ext));
		}

//		header('Content-type: '.$mimeTypes[$ext]);
		Yii::$app->response->format = Response::FORMAT_RAW;
		Yii::$app->response->headers->add('Content-type', $mimeTypes[$ext]);

		return $img->get($ext, []);
	}
}