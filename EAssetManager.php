<?php
/**
 * EAssetManager class file.
 *
 * Extended Asset Manager
 * Compiles .less file(s) on-the-fly and publishes output .css file
 *
 * @author Inpassor <inpassor@yandex.com>
 * @link https://github.com/Inpassor/yii-EAssetManager
 *
 * @version 0.3 (2013.10.24)
 */
/*

EAssetManager class file.

Extended Asset Manager
Compiles .less file(s) on-the-fly and publishes output .css file

Author: Inpassor <inpassor@yandex.com> .
Link: https://github.com/Inpassor/yii-EAssetManager .
Version: 0.3 (2013.10.24) .


INSTALLATION

Install with composer:

composer require inpassor/yii-eassetmanager

Manual install:

1. Copy EAssetManager.php to /protected/vendor/ directory
2. Add or replace the assetManager component in /protected/config/main.php like that:

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

3. CHMOD 'lessCompiledPath' directory to 0777 in order to create new files there by EAssetManager.
4. Optional: enable Yii caching. Otherwise, EAssetManager will create (or use existing) directory /protected/runtime/cache/ and store cache data there.
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

$css = CHtml::asset(Yii::app()->assetManager->lessCompiledPath.'/bootstrap.css');

*/

class EAssetManager extends CAssetManager
{

    /**
     * @var string default cache path for EAssetManager. It will be used if Yii caching is not enabled.
     */
    public $cachePath = null;

    /**
     * @var string path to store compiled css files. Defaults to 'application.assets.css'.
     * Note that this path must be writtable by script (CHMOD 777)
     */
    public $lessCompiledPath = null;

    /**
     * @var string compiled output formatter. Accepted values: 'lessjs' , 'compressed' , 'classic' . Defaults to 'lessjs'.
     * Read http://leafo.net/lessphp/docs/#output_formatting for details.
     */
    public $lessFormatter = 'lessjs';

    /**
     * @var bool passing in true will cause the input to always be recompiled.
     */
    public $lessForceCompile = false;

    /**
     * @var bool if set to false, .less to .css compilation will be done ONLY if output .css file not found.
     * Otherwise existing .css file will be used
     */
    public $lessCompile = true;

    /**
     * @var lessc
     */
    protected $_lessc = null;

    /**
     * @inheritdoc
     */
    public function init()
    {
        if (!Yii::app()->cache) {
            $this->cachePath = $this->_getPath($this->cachePath, Yii::app()->basePath . DIRECTORY_SEPARATOR . 'runtime' . DIRECTORY_SEPARATOR . 'cache', true);
        }
        if ($this->lessCompile) {
            $this->lessCompiledPath = $this->_getPath($this->lessCompiledPath, 'application.assets.css', true);
        }
        parent::init();
    }

    /**
     * @param string $path
     * @param bool $hashByName
     * @param int $level
     * @param null $forceCopy
     * @return mixed
     */
    public function publish($path, $hashByName = false, $level = -1, $forceCopy = null)
    {
        if (($src = realpath($path)) !== false) {
            switch (pathinfo($src, PATHINFO_EXTENSION)) {
                case 'less': {
                    $path = $this->lessCompile($src);
                }
            }
        }
        return parent::publish($path, $hashByName, $level, $forceCopy);
    }

    /**
     * @param string $src
     * @return string
     */
    public function lessCompile($src)
    {
        $path = $this->lessCompiledPath . DIRECTORY_SEPARATOR . basename($src, '.less') . '.css';
        $lessCompile = false;
        if (!$this->lessForceCompile && $this->lessCompile) {
            $lessFiles = $this->_cacheGet('EAssetManager-less-updated-' . $src);
            if ($lessFiles && is_array($lessFiles)) {
                foreach ($lessFiles as $_file => $_time) {
                    if (filemtime($_file) != $_time) {
                        $lessCompile = true;
                        break;
                    }
                }
            } else {
                $lessCompile = true;
            }
            unset($lessFiles);
        }
        if (!file_exists($path) || $lessCompile || $this->lessForceCompile) {
            if (!$this->_lessc) {
                $this->_lessc = new lessc();
            }
            $this->_lessc->setFormatter($this->lessFormatter);
            $lessCache = $this->_lessc->cachedCompile($src);
            file_put_contents($path, $lessCache['compiled'], LOCK_EX);
            $this->_cacheSet('EAssetManager-less-updated-' . $src, $lessCache['files']);
        }
        return $path;
    }

    /**
     * @param string $dir
     * @param bool $create
     * @return bool
     */
    protected function _chkDir($dir, $create)
    {
        if (($alias = Yii::getPathOfAlias($dir))) {
            return $alias;
        }
        if (!file_exists($dir) && $create) {
            mkdir($dir, 0777, true);
        }
        if (file_exists($dir)) {
            return $dir;
        }
        return false;
    }

    /**
     * @param string $path
     * @param null $default
     * @param bool $createDir
     * @return bool
     */
    protected function _getPath($path, $default = null, $createDir = false)
    {
        if ($default === null) {
            $default = dirname(__FILE__);
        }
        if ($path === null) {
            $path = $default;
        }
        if (!($ret = $this->_chkDir($path, $createDir))) {
            $ret = $this->_chkDir($default, $createDir);
        }
        return $ret;
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return int
     */
    protected function _cacheSet($name, $value)
    {
        if (Yii::app()->cache) {
            return Yii::app()->cache->set($name, $value);
        }
        return file_put_contents($this->cachePath . DIRECTORY_SEPARATOR . md5($name) . '.bin', serialize($value), LOCK_EX);
    }

    /**
     * @param string $name
     * @return bool|mixed
     */
    protected function _cacheGet($name)
    {
        if (Yii::app()->cache) {
            return Yii::app()->cache->get($name);
        }
        if (file_exists($this->cachePath . DIRECTORY_SEPARATOR . md5($name) . '.bin')) {
            return unserialize(file_get_contents($this->cachePath . DIRECTORY_SEPARATOR . md5($name) . '.bin'));
        }
        return false;
    }

}

?>
