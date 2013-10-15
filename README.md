EAssetManager class file.
 
Extended Asset Manager
Compiles .less file(s) on-the-fly and publishes output .css file
 
Author: Inpassor <inpassor@gmail.com>
Link: https://github.com/Inpassor/yii-EAssetManager
Version: 0.1 (2013.10.15)


INSTALLATION
============

1. Copy EAssetManager.php to /protected/extensions directory
2. Download the latest version of lessphp from http://leafo.net/lessphp and put lessc.inc.php file under /protected/extentions/EAssetManager/ folder
3. Add or replace the assetManager component in /protected/config/main.php like that:

	'components'=>array(

		...

		'assetManager'=>array(
			'class'=>'EAssetManager',
			'lessCompile'=>true,
			'lessCompiledPath'=>'application.assets.css',
			'lessFormatter'=>'compressed',
			'lessForceCompile'=>false,
		),

		...

	),

See code of EAssetManager.php to read description of public properties.


USAGE
=====

CHtml::asset(Yii::app()->basePath.'/vendors/bootstrap/less/bootstrap.less');

That's all :)
