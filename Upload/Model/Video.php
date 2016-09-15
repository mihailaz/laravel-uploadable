<?php
/**
 * User: Michael Lazarev <mihailaz.90@gmail.com>
 * Date: 15.09.16
 * Time: 13:26
 */

namespace App\Upload\Model;

/**
 * Class Video
 * @package App\Upload\Model
 * @property string $id
 * @property string $url
 * @property string $embed
 * @property string $preview
 */
class Video
{
	/**
	 * @var string
	 */
	protected $youtube_id;

	/**
	 * @param string $youtube_id
	 */
	public function __construct($youtube_id)
	{
		$this->youtube_id = (string) $youtube_id;
	}

	/**
	 * @param string|null $version
	 * @return string
	 */
	public function preview($version = null)
	{
		if (!$version){
			$version = 'default';
		}
		return "http://img.youtube.com/vi/{$this->youtube_id}/{$version}.jpg";
	}

	/**
	 * @return string
	 */
	protected function getIdAttribute()
	{
		return $this->youtube_id;
	}

	/**
	 * @return string
	 */
	protected function getUrlAttribute()
	{
		return 'http://www.youtube.com/watch?v=' . $this->youtube_id;
	}

	/**
	 * @return string
	 */
	protected function getEmbedAttribute()
	{
		return 'http://www.youtube.com/embed/' . $this->youtube_id;
	}

	/**
	 * @return string
	 */
	protected function getPreviewAttribute()
	{
		return $this->preview();
	}

	/**
	 * @param string $name
	 * @return mixed
	 */
	public function __get($name)
	{
		$method = 'get' . studly_case($name) . 'Attribute';

		if (!method_exists($this, $method)){
			return null;
		}
		return call_user_func([$this, $method]);
	}

	/**
	 * @return string
	 */
	public function __toString()
	{
		return $this->youtube_id;
	}
}