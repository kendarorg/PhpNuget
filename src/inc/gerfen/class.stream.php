<?php

/**
* stream - Handle raw input stream
*
* LICENSE: This source file is subject to version 3.01 of the GPL license
* that is available through the world-wide-web at the following URI:
* http://www.gnu.org/licenses/gpl.html. If you did not receive a copy of
* the GPL License and are unable to obtain it through the web, please
*
* @author jason.gerfen@gmail.com
* @license http://www.gnu.org/licenses/gpl.html GPL License 3
*/

class stream
{
	/**
	 * @var input
	 * @abstract Raw input stream
	 */
	protected $input;

	/**
	 * @function __construct
	 * @param $data stream
	 */
	public function __construct(array &$data)
	{
		$this->input = file_get_contents('php://input');

		$boundary = $this->boundary();
		
		if (!count($boundary)) {
			return array(
				'post' => $this->parse($this->input),
				'file' => array()
			);
		}
		
		$blocks = $this->split($boundary);
		

		$data = $this->blocks($blocks);
		
		return $data;
	}

	/**
	 * @function boundary
	 * @returns Array
	 */
	private function boundary()
	{
		preg_match('/boundary=(.*)$/', $_SERVER['CONTENT_TYPE'], $matches);
		return str_replace("\"","",$matches[1]);
	}

	/**
	 * @function parse
	 * @returns Array
	 */
	private function parse()
	{
		parse_str(urldecode($this->input), $result);
		return $result;
	}

	/**
	 * @function split
	 * @param $boundary string
	 * @returns Array
	 */
	private function split($boundary)
	{
		$result = preg_split("/-+$boundary/", $this->input);
		
	    array_pop($result);
		return $result;
	}

	/**
	 * @function blocks
	 * @param $array array
	 * @returns Array
	 */
	private function blocks($array)
	{
		
		$results = array(
			'post' => array(),
			'file' => array()
		);

		foreach($array as $key => $value)
		{
			if (empty($value))
				continue;

			$block = $this->decide($value);

			if (count($block['post']) > 0)
				array_push($results['post'], $block['post']);

			if (count($block['file']) > 0)
				$results['file'][$block['file']['name']]= $block['file'];
		}

		return $this->merge($results);
	}

	/**
	 * @function decide
	 * @param $string string
	 * @returns Array
	 */
	private function decide($string)
	{
		if (strpos($string, 'application/octet-stream') !== FALSE)
    	{
			return array(
				'post' => array(),
				'file' => $this->file($string)
			);
		}

		if (strpos($string, 'filename') !== FALSE)
		{
			return array(
				'post' => array(),
				'file' => $this->file_stream($string)
			);
		}

		return array(
			'post' => $this->post($string),
			'file' => array()
		);
	}

	/**
	 * @function file
	 * @param $boundary string
	 * @returns Array
	 */
	private function file($string)
	{
		$fileName = Utils::NewGuid();
		preg_match('/name=\"([^\"]*)\".*stream[\n|\r]+([^\n\r].*)?$/s', $string, $match);
		if(sizeof($match)==3){
			uplog("class.stream","Match 3");
			$cnt = substr($match[2],0,strlen($match[2])-2);
			return array(
				//$match[1] => $match[2]
				"tmp_name"=>null,
				"name"=>$match[1],
				"sizeBytes" =>strlen($match[2]),
				"content"=> $cnt
			);
		}else{
			preg_match('/filename=([^;]*);/', $string, $match);
			if(sizeof($match)==2){
				uplog("class.stream","Match 2");
				$fileName = $match[1];
			}
		}
		$match = preg_split('/\r\n\r\n/s', $string);
		
		$cnt = substr($match[1],0,strlen($match[1])-2);
		$res= array(
			"tmp_name"=>null,
			"name"=>$fileName,
			"sizeBytes" =>strlen($cnt),
			"content"=> $cnt
		);
		
		return $res;
	}

	/**
	 * @function file_stream
	 * @param $boundary string
	 * @returns Array
	 */
	private function file_stream($string)
	{
		$data = array();

		preg_match('/name=\"([^\"]*)\"; filename=\"([^\"]*)\"[\n|\r]+([^\n\r].*)?\r$/s', $string, $match);
		preg_match('/Content-Type: (.*)?/', $match[3], $mime);

		$image = preg_replace('/Content-Type: (.*)[^\n\r]/', '', $match[3]);

		$path = sys_get_temp_dir().'/php'.substr(sha1(rand()), 0, 6);
		
		$err = file_put_contents($path, $image);

		if (preg_match('/^(.*)\[\]$/i', $match[1], $tmp)) {
			$index = $tmp[1];
		} else {
			$index = $match[1];
		}

		$data[$index]['name'] = $match[2];
		$data[$index]['type'] = $mime[1];
		$data[$index]['tmp_name'] = $path;
		$data[$index]['error'] = ($err === FALSE) ? $err : 0;
		$data[$index]['size'] = filesize($path);

		return $data;
	}

	/**
	 * @function post
	 * @param $boundary string
	 * @returns Array
	 */
	private function post($string)
	{
		$data = array();

		preg_match('/name=\"([^\"]*)\"[\n|\r]+([^\n\r].*)?\r$/s', $string, $match);

		if (preg_match('/^(.*)\[\]$/i', $match[1], $tmp)) {
			$data[$tmp[1]][] = $match[2];
		} else {
			$data[$match[1]] = $match[2];
		}

		return $data;
	}

	/**
	 * @function merge
	 * @param $array array
	 *
	 * Ugly ugly ugly
	 *
	 * @returns Array
	 */
	private function merge($array)
	{
		$results = array(
			'post' => array(),
			'file' => array()
		);

		if (count($array['post'] > 0)) {
			foreach($array['post'] as $key => $value) {
				foreach($value as $k => $v) {
					if (is_array($v)) {
						foreach($v as $kk => $vv) {
							$results['post'][$k][] = $vv;
						}
					} else {
						$results['post'][$k] = $v;
					}
				}
			}
		}

		if (count($array['file'] > 0)) {
			foreach($array['file'] as $key => $value) {
				$results['file'][$key] = $value;
				/*foreach($value as $k => $v) {
					if (is_array($v)) {
						foreach($v as $kk => $vv) {
							$results['file'][$kk][] = $vv[0];
						}
					} else {
						$results['file'][$key] = $v;
					}
				}*/
			}
		}

		return $results;
	}
}
