<?php
/**
 * @author Dmitrij "m00nk" Sheremetjev <m00nk1975@gmail.com>
 * Date: 29.09.16, Time: 3:36
 */

namespace m00nk\dynimage;

use yii\web\AssetBundle;

class DynImageAsset extends AssetBundle
{
	public $js = [
		'dynImage.js'
	];

	public $depends = [
		'yii\web\JqueryAsset',
	];

	public $publishOptions = [
		'forceCopy' => YII_ENV_DEV
	];

	public function init()
	{
		$this->sourcePath = __DIR__.'/assets';
		parent::init();
	}

}