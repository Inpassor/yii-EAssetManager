<?php
/**
 * EAssetManager class file.
 *
 * Extended Asset Manager
 * Compiles .less file(s) on-the-fly and publishes output .css file
 *
 * @author Inpassor <inpassor@gmail.com>
 * @link https://github.com/Inpassor/yii-EAssetManager
 *
 * @version 0.22 (2013.10.18)
 */

/*

INSTALLATION

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
5. Optional: enable Yii caching. Otherwise, EAssetManager will create (or use existing) directory /protected/runtime/cache/ and store cache data there.
You can override this path by setting public property 'cachePath'.


USAGE

Just publish .less file with assetManager like that:

$css = CHtml::asset(Yii::app()->basePath.'/vendors/bootstrap/less/bootstrap.less');

That's all :)


Also it might be useful to pre-compile .less files. For example, to make command which compiles .less files in background.
In this case you can use "lessCompile" method:

Yii::app()->assetManager->lessCompile(Yii::app()->basePath.'/vendors/bootstrap/less/bootstrap.less');

Output .css file will be stored under 'lessCompiledPath' directory.
And then add already compiled file in your application:

$css = CHtml::asset(Yii::app()->basePath.'/assets/css/bootstrap.css');

or

$css = CHtml::asset(Yii::app()->assetManager->lessCompiledPath.'/bootstrap.css');

*/


class EAssetManager extends CAssetManager
{

	// default cache path for EAssetManager. It will be used if Yii caching is not enabled.
	public $cachePath=null;

	// path and file name of lessc.inc.php . Defaults to ./EAssetManager/cache
	public $lessLib=null;

	// path to store compiled css files
	// defaults to 'application.assets.css'
	// note that this path must be writtable by script (CHMOD 777)
	public $lessCompiledPath=null;

	// compiled output formatter
	// accepted values: 'lessjs' , 'compressed' , 'classic'
	// defaults to 'lessjs'
	// read http://leafo.net/lessphp/docs/#output_formatting for details
	public $lessFormatter='lessjs';

	// passing in true will cause the input to always be recompiled
	public $lessForceCompile=false;

	// if set to false, .less to .css compilation will be done ONLY if output .css file not found
	// otherwise existing .css file will be used
	public $lessCompile=true;


	public function init()
	{
		if (!Yii::app()->cache)
		{
			$this->cachePath=$this->_getPath($this->cachePath,Yii::app()->basePath.DIRECTORY_SEPARATOR.'runtime'.DIRECTORY_SEPARATOR.'cache');
		}
		if ($this->lessCompile)
		{
			if (!$this->lessLib)
			{
				$this->lessLib=dirname(__FILE__).DIRECTORY_SEPARATOR.'EAssetManager'.DIRECTORY_SEPARATOR.'lessc.inc.php';
			}
			require_once($this->lessLib);
			$this->lessCompiledPath=$this->_getPath($this->lessCompiledPath,'application.assets.css');
		}
		parent::init();
	}


	public function publish($path,$hashByName=false,$level=-1,$forceCopy=null)
	{
		if (($src=realpath($path))!==false)
		{
			switch (pathinfo($src,PATHINFO_EXTENSION))
			{
				case 'less':
				{
					$path=$this->lessCompile($src);
				}
			}
		}
		return parent::publish($path,$hashByName,$level,$forceCopy);
	}


	public function lessCompile($src)
	{
		$path=$this->lessCompiledPath.DIRECTORY_SEPARATOR.basename($src,'.less').'.css';
		$lessCompile=false;
		if (!$this->lessForceCompile&&$this->lessCompile)
		{
			$lessFiles=$this->_cacheGet('EAssetManager-less-updated-'.$src);
			if ($lessFiles&&is_array($lessFiles))
			{
				foreach ($lessFiles as $_file=>$_time)
				{
					if (filemtime($_file)!=$_time)
					{
						$lessCompile=true;
						break;
					}
				}
			}
			else
			{
				$lessCompile=true;
			}
			unset($lessFiles);
		}
		if (!file_exists($path)||$lessCompile||$this->lessForceCompile)
		{
			$lessc=new lessc();
			$lessc->setFormatter($this->lessFormatter);
			$lessCache=$lessc->cachedCompile($src);
			file_put_contents($path,$lessCache['compiled'],LOCK_EX);
			$this->_cacheSet('EAssetManager-less-updated-'.$src,$lessCache['files']);
		}
		return $path;
	}



	private function _chkDir($path)
	{
		if (file_exists($path))
		{
			return realpath($path);
		}
		if (mkdir($path,0777,true))
		{
			return realpath($path);
		}
		return false;
	}


	private function _getPath($path,$default=null)
	{
		if ($default===null)
		{
			$default=dirname(__FILE__).DIRECTORY_SEPARATOR.'EAssetManager';
		}
		if ($path===null)
		{
			$path=$default;
		}
		if (($alias=YiiBase::getPathOfAlias($path))!==false)
		{
			return $alias;
		}
		if (($path=$this->_chkDir($path))!==false||($path=$this->_chkDir($default))!==false)
		{
			return $path;
		}
		return false;
	}


	private function _cacheSet($name,$value)
	{
		if (Yii::app()->cache)
		{
			return Yii::app()->cache->set($name,$value);
		}
		return file_put_contents($this->cachePath.DIRECTORY_SEPARATOR.md5($name).'.bin',serialize($value),LOCK_EX);
	}


	private function _cacheGet($name)
	{
		if (Yii::app()->cache)
		{
			return Yii::app()->cache->get($name);
		}
		if (file_exists($this->cachePath.DIRECTORY_SEPARATOR.md5($name).'.bin'))
		{
			return unserialize(file_get_contents($this->cachePath.DIRECTORY_SEPARATOR.md5($name).'.bin'));
		}
		return false;
	}

}

?>
