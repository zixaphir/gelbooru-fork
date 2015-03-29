<?php
/*
 *  Class for handling webm files around the MitsubaBBS Project
 *  Copyright (C) 2014  Malkovich <chlodnapiwnica@gmail.com>
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as
 *  published by the Free Software Foundation, either version 3 of the
 *  License, or (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Affero General Public License for more details.
 *
 *  You should have received a copy of the GNU Affero General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

class webm {
	/* private properties */
	private $input;			//orginal webm movie
	private $exec_string;	//commandline for ffmpeg

	function webm($webm_clip_name) {
		$this->input = $webm_clip_name;
		$this->exec_string = "";
	}

	function frame() {
		$this->exec_string = 'ffmpeg -i '.$this->input.
			' -vframes 1'.
			' -y tmp/webmthumb.jpg'.
			' 2>&1';

		exec($this->exec_string,$output,$return_var);

		echo $return_var;

		if ($return_var==0) {
			return imagecreatefromjpeg("tmp/webmthumb.jpg");
		} else {
			return false;
		}
	}

	/*
	 *	check for VP8/9 format
	 */
	function valid_webm() {
		$lines = [];
		$return = "";
		$this->exec_string = 'ffmpeg -i '.$this->input .' 2>&1';
		exec('ffmpeg -i '.$this->input .' 2>out.txt');
		exec($this->exec_string, $lines, $return);

		foreach ($lines as $line) {
			if (preg_match('/Stream.+#\d:\d.+Video.+vp(8|9)/i', $line)) {
				return true;
			}
		}
		echo "invalid webm";
		return false;
	}


}

?>