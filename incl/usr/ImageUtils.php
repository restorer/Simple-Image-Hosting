<?php

class ImageUtils
{
	function GenerateThumbnail($src, $dst, $mw, $mh, $alwaysResize=false)
	{
		$size = @getImageSize($src);
		if (!$size || !($size[2]==1 || $size[2]==2 || $size[2]==3)) return false;

		switch ($size[2])
		{
			case 1: $type = 'gif'; break;
			case 2: $type = 'jpeg'; break;
			case 3: $type = 'png'; break;
		}

		if (isset($type))
		{
			switch ($type)
			{
				case 'jpeg': $sim = imageCreateFromJPEG($src); break;
				case 'gif' : $sim = imageCreateFromGIF($src); break;
				case 'png' : $sim = imageCreateFromPNG($src); break;
			}

			if ($mw<=0 || $mh<=0)
			{
				$tw = $size[0];
				$th = $size[1];
			}
			elseif ($size[0]>$mw || $size[1]>$mh || $alwaysResize)
			{
				$ratio = ($size[0] > $size[1]) ? ($size[0] / $mw) : ($size[1] / $mh);
				$tw = $size[0] / $ratio;
				$th = $size[1] / $ratio;
			}
			else
			{
				$tw = $size[0];
				$th = $size[1];
			}

			$text = $size[0].'x'.$size[1].' - '.intval(filesize($src) / 1024).'Kb';

			$font = BASE_PATH . 'incl/serifan.ttf';
			$fontsize = 12 + 1;
			$fontangle = 0;

			do {
				$fontsize--;
				$bounds = imageTtfBBox($fontsize, $fontangle, $font, $text);
			} while ($fontsize>6 && abs($bounds[0]-$bounds[2]) > $tw);

			$tha = 6+abs($bounds[1]-$bounds[7]);

			$thumb = imageCreateTrueColor($tw, $th + $tha);
			imageCopyResampled($thumb, $sim, 0, 0, 0, 0, $tw, $th, $size[0], $size[1]);

			$th += $tha;

			$black = imageColorAllocate($thumb, 0, 0, 0);
			$white = imageColorAllocate($thumb, 255, 255, 255);

			$GLOBALS['image_orig_width'] = $size[0];
			$GLOBALS['image_orig_height'] = $size[1];
			$GLOBALS['image_thumb_width'] = $tw;
			$GLOBALS['image_thumb_height'] = $th;

			imageFilledRectangle($thumb, 0, $th-$tha, $tw-1, $th-1, $black);
			imageTtfText($thumb, $fontsize, $fontangle, $tw/2-$bounds[2]/2, $th-4-$bounds[3], $white, $font, $text);

			imageRectangle($thumb, 0, 0, $tw-1, $th-1, $black);

			switch ($type)
			{
				case 'jpeg': imageJPEG($thumb, $dst); break;
				case 'gif' : imageGIF($thumb, $dst); break;
				case 'png' : imagePNG($thumb, $dst); break;
			}

			return true;
		}

		return false;
	}
}

?>