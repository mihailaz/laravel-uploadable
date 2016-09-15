<?php
/**
 * User: Michael Lazarev <mihailaz.90@gmail.com>
 * Date: 11.06.16
 * Time: 15:27
 */

namespace App\Upload;

/**
 * Trait Videoable
 * @package App\Upload
 * @mixin Uploadable
 */
trait Videoable
{
	/**
	 * @param string $attr
	 * @return string|null
	 */
	protected function saveVideoFile($attr)
	{
		/**
		 * @var \Illuminate\Http\Request $request
		 */
		$request = app('request');
		$file    = $request->file($attr);
		$value   = $request->get($attr);

		if (!$file){
			return $this->youtubeIdFromUrl($value);
		}
		return \Youtube::upload($file->getRealPath(), ['title' => $file->getClientOriginalName()]);
	}

	protected function youtubeIdFromUrl($url)
	{
	    $pattern =
	        '%^# Match any youtube URL
	        (?:https?://)?  # Optional scheme. Either http or https
	        (?:www\.)?      # Optional www subdomain
	        (?:             # Group host alternatives
	          youtu\.be/    # Either youtu.be,
	        | youtube\.com  # or youtube.com
	          (?:           # Group path alternatives
	            /embed/     # Either /embed/
	          | /v/         # or /v/
	          | /watch\?v=  # or /watch\?v=
	          )             # End path alternatives.
	        )               # End host alternatives.
	        ([\w-]{10,12})  # Allow 10-12 for 11 char youtube id.
	        $%x'
	        ;
	    $result = preg_match($pattern, $url, $matches);

	    if ($result){
	        return $matches[1];
	    }
		return null;
	}

	/**
	 * @param string $attr
	 * @param string $value
	 * @return string
	 */
	protected function castVideoFile($attr, $value)
	{
		return 'http://www.youtube.com/embed/' . $value;
	}

	/**
	 * @param string $attr
	 * @param string $value
	 * @return string
	 */
	protected function castVideoPreviewFile($attr, $value)
	{
		@list($video_attr, $preview_version) = explode('_', preg_replace('/_?preview/', '', $attr));

		if (!$video_attr){
			$video_attr = head($this->getFiles('video'));
		}
		return $this->getVideoPreview($this->{$video_attr}, $preview_version);
	}

	/**
	 * @param string      $youtube_id
	 * @param string|null $version
	 * @return string
	 */
	protected function getVideoPreview($youtube_id, $version = null)
	{
		if (!$version){
			$version = 'default';
		}
		return "http://img.youtube.com/vi/{$youtube_id}/{$version}.jpg";
	}
}