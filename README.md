EAssetManager class file.
 
Extended Asset Manager
Compiles .less file(s) on-the-fly and publishes output .css file
 
Author: Inpassor <inpassor@gmail.com>
Link: https://github.com/Inpassor/yii-EAssetManager
Version: 0.21 (2013.10.17)


INSTALLATION
============

1. Copy EAssetManager.php to /protected/extensions/ directory
2. Download the latest version of lessphp from http://leafo.net/lessphp and put lessc.inc.php file under /protected/extensions/EAssetManager/ directory
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

4. CHMOD 'lessCompiledPath' directory to 777 in order to create new files there by EAssetManager.
5. Optional: enable Yii caching. Otherwise, EAssetManager will try to create directory /protected/extensions/EAssetManager/cache/ and store cache data there.


USAGE
=====

Just publish .less file with assetManager like that:

$css = CHtml::asset(Yii::app()->basePath.'/vendors/bootstrap/less/bootstrap.less');

That's all :)


Also it might be useful to pre-compile .less files. For example, to make command which compiles .less files in background.
In this case you can use "lessCompile" method:

Yii::app()->assetManager->lessCompile(Yii::app()->basePath.'/vendors/bootstrap/less/bootstrap.less');

Next, add already compiled file in your application:

$css = CHtml::asset(Yii::app()->basePath.'/assets/css/bootstrap.css');
