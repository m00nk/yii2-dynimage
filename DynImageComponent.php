<?php
/**
 * @author Dmitrij "m00nk" Sheremetjev <m00nk1975@gmail.com>
 * Date: 29.09.16, Time: 2:01
 */

namespace m00nk\dynimage;

use Yii;
use yii\base\Component;
use yii\base\InvalidParamException;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;

/**
 * @property string $cachePath путь относительно корня сайта к папке кэша изображений. По-умолчанию '/assets/dynimg'
 */
class DynImageComponent extends Component
{
	/** @var int уровень качества JPEG */
	public $jpegQuality = 70;

	/** @var array массив допустимых ширин. Используется только при автоматическом определении ширины. */
	public $sizes = [5000, 2400, 2000, 1600, 1200, 1000, 800, 600, 400, 300, 200, 150, 100, 70, 50, 30];

	//-----------------------------------------
	private $_cachePath = '/assets/dynimg';


	public function init()
	{
		parent::init();

		if(empty($this->cachePath)) throw new InvalidParamException('DynImageComponent::cachePath can not be empty.');
		if(empty($this->sizes) || !is_array($this->sizes)) throw new InvalidParamException('DynImageComponent::sizes must be non empty array.');
	}

	public function createFolderForFile($filePath)
	{
		if(strpos($filePath, Yii::getAlias('@webroot').$this->cachePath) !== false)
		{ // только если внутри папки кэша
			$folderPath = substr($filePath, 0, strrpos($filePath, '/'));
			if(!file_exists($folderPath))
			{
				@mkdir($folderPath, 0777, true);
				@chmod($folderPath, 0777);
			}
		}
	}

	/**
	 * Возвращает URL динамического изображения
	 *
	 * @param string      $path     путь к исходному изображению относительно корня сайта
	 * @param null|int    $width    ширина результирующей картинки. Не обязательное.
	 * @param null|int    $height   высота результирующей картинки. Не обазательное.
	 * @param null|string $ext      Расширение результирующей картинки. Если не задано, то будет использовано расшинение исходной картинки.
	 * @param null|int    $quality  Качество. Только для JPEG. Если не задано, то будет использоваться значение $jpegQuality
	 * @param bool        $absolute нужен абсолютный URL ?
	 *
	 * @return string
	 */
	public function getUrl($path, $width = null, $height = null, $ext = null, $quality = null, $absolute = false)
	{
		if(empty($path)) throw new InvalidParamException('DynImageComponent::getUrl: parameter $path can not be empty.');

		$q = intval($quality);
		if($q == 0) $q = $this->jpegQuality;

		$params = [
			intval($width),
			intval($height),
			$q
		];

		$index = strrpos($path, '.');
		$ext = empty($ext) ? strtolower(substr($path, $index + 1)) : strtolower($ext);
		return ($absolute ? Url::base(true) : '').$this->cachePath.$path.'='.implode('x', $params).'.'.$ext;
	}

	/**
	 * Возвращает HTML-код для вставки динамического изображения
	 *
	 * @param string      $path    путь к исходному изображению относительно корня сайта
	 * @param null|string $ext     Расширение результирующей картинки. Если не задано, то будет использовано расшинение исходной картинки.
	 * @param null|int    $quality Качество. Только для JPEG. Если не задано, то будет использоваться значение $jpegQuality
	 * @param array       $options атрибуты тега img
	 *
	 * @return string
	 */
	public function img($path, $ext = null, $quality = null, $options = [])
	{
		if(empty($path)) throw new InvalidParamException('DynImageComponent::getUrl: parameter $path can not be empty.');

		$q = intval($quality);
		if($q == 0) $q = $this->jpegQuality;

		$index = strrpos($path, '.');
		$ext = empty($ext) ? strtolower(substr($path, $index + 1)) : strtolower($ext);

		$view = Yii::$app->view;
		DynImageAsset::register($view);
		if(is_array($this->sizes)) sort($this->sizes);
		$view->registerJs('dynImage.init('.Json::encode($this->sizes).');');

		$options = array_merge($options, [
			'data-dyn-quality' => $q,
			'data-dyn-src' => $this->cachePath.$path,
			'data-dyn-ext' => $ext
		]);

		return Html::tag('img-dyn', '', $options);
	}

	/**
	 * @return string
	 */
	public function getCachePath()
	{
		return $this->_cachePath;
	}

	/**
	 * @param string $cachePath
	 */
	public function setCachePath($cachePath)
	{
		$rootPath = Yii::getAlias('@webroot');
		$_ = realpath($rootPath.$cachePath);
		if($_ === false || strpos($_, $rootPath) !== 0)
			throw new InvalidParamException('DynImageComponent::cachePath has wrong value.');

		$this->_cachePath = substr($_, strlen($rootPath));
	}

}