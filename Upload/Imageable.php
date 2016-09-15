<?php
/**
 * User: Michael Lazarev <mihailaz.90@gmail.com>
 * Date: 10.06.16
 * Time: 12:32
 */

namespace App\Upload;

/**
 * Trait Imageable
 * @package App\Upload
 * @mixin Uploadable
 * @property string $image_upload_path
 */
trait Imageable
{
	/**
	 * @param string       $attr
	 * @param array|string $value
	 * @return array|string
	 */
	public function castImageFile($attr, $value)
	{
		$handle = function(&$v){
			$tmp = new \stdClass;

			foreach (array_keys(config('imagecache.templates')) as $name){
				$tmp->{$name} = asset(implode('/', [
					config('imagecache.route'),
					$name,
					$v,
				]));
			}
			$v = $tmp;
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
	 * @param string $attr
	 * @return string
	 */
	public function getImageUploadPath($attr)
	{
		return property_exists($this, 'image_upload_path') ? $this->image_upload_path : 'uploads/images';
	}
}