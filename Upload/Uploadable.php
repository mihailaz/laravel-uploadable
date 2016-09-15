<?php
/**
 * User: Michael Lazarev <mihailaz.90@gmail.com>
 * Date: 11.06.16
 * Time: 16:08
 */

namespace App\Upload;

/**
 * Trait Uploadable
 * @package App\Upload
 * @property array $files
 * @property array $file_casts
 * @property string $upload_path
 */
trait Uploadable
{
	/**
	 * @param  string  $key
	 * @param  mixed  $value
	 * @return $this
	 */
	public function setAttribute($key, $value)
	{
		if (in_array($key, $this->getFiles())){
			$val = $this->handleFile($key);

			if (!$val) {
				return $this;
			}
			$value = $val;

			if ($this->isAttributeArray($key)){
				$current = $this->$key;

				if (!is_array($current)){
					$current = [];
				}
				$current[] = $value;
				$value     = $current;
			}
		}
		return parent::setAttribute($key, $value);
	}

	/**
	 * @param string $attr
	 * @return mixed
	 */
	public function getAttribute($attr)
	{
		$value = parent::getAttribute($attr);

		if (in_array($attr, $this->getFiles())){
			$type = '';

			if ($this->hasFileCast($attr)){
				$type = $this->getFileCastType($attr);
			}
			$method = 'cast' . studly_case($type) . 'File';

			if (!method_exists($this, $method)){
				$method = 'castFile';
			}
			$value = call_user_func([$this, $method], $attr, $value);
		}
		return $value;
	}

	/**
	 * @return array
	 */
	public function attributesToArray()
	{
		$attrs = parent::attributesToArray();

		foreach ($this->getFiles() as $key){
			if (!array_key_exists($key, $attrs) || !$attrs[$key]){
				continue;
			}
			$attrs[$key] = $this->getAttribute($key);
		}
		return $attrs;
	}

	/**
	 * @param mixed $value
	 * @return string
	 */
	protected function castFile($attr, $value)
	{
		$handle = function(&$v){
			$v = asset($v);
		};

		if ($this->isAttributeArray($attr)){
			if (!is_array($value)){
				$value = [];
			}
			array_walk($value, $handle);
		} elseif ($value) {
			$handle($value);
		}
		return $value;
	}

	/**
	 * @param string|null $type
	 * @return array
	 */
	public function getFiles($type = null)
	{
		$files = property_exists($this, 'files') ? $this->files : [];

		if (!$type){
			return $files;
		}
		$byType = [];

		foreach ($files as $a => $t){
			if ($t !== $type){
				continue;
			}
			$byType[] = $a;
		}
		return $byType;
	}

	/**
	 * @param string $attr
	 * @return string
	 */
	public function getUploadPath($attr)
	{
		$type = 'File';

		if ($this->hasFileCast($attr)){
			$type = $this->getFileCastType($attr);
		}
		$method = 'get' . studly_case($type) . 'UploadPath';

		if (!method_exists($this, $method)){
			$method = 'getFileUploadPath';
		}
		return call_user_func([$this, $method], $attr);
	}

	/**
	 * @param string $attr
	 * @return string
	 */
	protected function getFileUploadPath($attr)
	{
		return property_exists($this, 'upload_path') ? $this->upload_path : 'uploads/files';
	}

	/**
	 * @param string $attr
	 * @return string
	 */
	protected function handleFile($attr)
	{
		$type = '';

		if ($this->hasFileCast($attr)){
			$type = $this->getFileCastType($attr);
		}
		$method = 'save' . studly_case($type) . 'File';

		if (!method_exists($this, $method)){
			$method = 'saveFile';
		}
		return call_user_func([$this, $method], $attr);
	}

	/**
	 * @param string $attr
	 * @return string
	 */
	protected function saveFile($attr)
	{
		/**
		 * @var \Illuminate\Http\Request $request
		 */
		$request = app('request');
		$file    = $request->file($attr);
		$value   = $request->get($attr);

		if (!$file){
			return $value;
		}
		$destPath = $this->getUploadPath($attr);
		$ext      = strtolower($file->getClientOriginalExtension());

		do {
			$filename = str_random(20) . '.' . $ext;
			$path     = implode('/', [$destPath, $filename]);
		} while(file_exists(public_path($path)));

		$file->move($destPath, $filename);

		return $path;
	}

	/**
	 * @param string $attr
	 * @return bool
	 */
	protected function hasFileCast($attr)
	{
		return array_key_exists($attr, $this->getFileCasts());
	}

	/**
	 * @param string $attr
	 * @return string
	 */
	protected function getFileCastType($attr)
	{
		return trim(strtolower($this->getFileCasts()[$attr]));
	}

	/**
	 * @return array
	 */
	protected function getFileCasts()
	{
		return property_exists($this, 'file_casts') ? $this->file_casts : '';
	}

	/**
	 * @param string $attr
	 * @return bool
	 */
	protected function isAttributeArray($attr)
	{
		return property_exists($this, 'casts')
		&& is_array($this->casts)
		&& array_key_exists($attr, $this->casts)
		&& trim(strtolower($this->casts[$attr])) === 'array';
	}
}