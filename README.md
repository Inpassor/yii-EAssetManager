EAssetManager class file.
 
Extended Asset Manager
Compiles .less file(s) on-the-fly and publishes output .css file
 
Author: Inpassor <inpassor@yandex.com> .
Link: https://github.com/Inpassor/yii-EAssetManager .
Version: 0.3 (2013.10.24) .


INSTALLATION
============

##Install with composer:

```
composer require inpassor/yii-eassetmanager
```

##Manual install:

1. Copy EAssetManager.php to /protected/vendor/ directory
2. Add or replace the assetManager component in /protected/config/main.php like that:

```
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
```

See code of EAssetManager.php to read description of public properties.

3. CHMOD 'lessCompiledPath' directory to 0777 in order to create new files there by EAssetManager.
4. Optional: enable Yii caching. Otherwise, EAssetManager will create (or use existing) directory /protected/runtime/cache/ and store cache data there.
You can override this path by setting public property 'cachePath'.


USAGE
=====

Just publish .less file with assetManager like that:

```
$css = CHtml::asset(Yii::app()->basePath.'/vendors/bootstrap/less/bootstrap.less');
```

That's all :)


Also it might be useful to pre-compile .less files. For example, to make command which compiles .less files in background.
In this case you can use "lessCompile" method:

```
Yii::app()->assetManager->lessCompile(Yii::app()->basePath.'/vendors/bootstrap/less/bootstrap.less');
```

Output .css file will be stored under 'lessCompiledPath' directory.
And then add already compiled file in your application:

```
$css = CHtml::asset(Yii::app()->assetManager->lessCompiledPath.'/bootstrap.css');
```
