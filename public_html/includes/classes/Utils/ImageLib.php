<?

class ImageLib
{
	public $tempDir;
	/**
	 * creates a Windows BMP picture
	 * @see imagegif, imagejpeg ...
	*/
	public $ConvertInfo = array();
	function ImageToBMP( &$img, $filename = '' )
	{
		$widthOrig = imagesx($img);
		// width = 16*x
		$widthFloor = ((floor($widthOrig/16))*16);
		$widthCeil = $widthOrig; //((ceil($widthOrig/16))*16); // by def remove comment
		$height = imagesy($img);
		
//		echo "$widthOrig, $widthFloor, $widthCeil, $height";
//		exit;
		
		$size = ($widthCeil*$height*3)+54; // by def 54
	
		// Bitmap File Header
		$result = 'BM';	 // header (2b)
		$result .= $this->int_to_dword($size); // size of file (4b)
		$result .= $this->int_to_dword(0); // reserved (4b)
		$result .= $this->int_to_dword(54);	// byte location in the file which is first byte of IMAGE (4b)
		// Bitmap Info Header
		$result .= $this->int_to_dword(40);	// Size of BITMAPINFOHEADER (4b)
		$result .= $this->int_to_dword($widthCeil);	// width of bitmap (4b)
		$result .= $this->int_to_dword($height); // height of bitmap (4b)
		$result .= $this->int_to_word(1);	// biPlanes = 1 (2b)
		$result .= $this->int_to_word(24); // biBitCount = {1 (mono) or 4 (16 clr ) or 8 (256 clr) or 24 (16 Mil)} (2b)
		$result .= $this->int_to_dword(0); // RLE COMPRESSION (4b)
		$result .= $this->int_to_dword(0); // width x height (4b)
		$result .= $this->int_to_dword(0); // biXPelsPerMeter (4b)
		$result .= $this->int_to_dword(0); // biYPelsPerMeter (4b)
		$result .= $this->int_to_dword(0); // Number of palettes used (4b)
		$result .= $this->int_to_dword(0); // Number of important colour (4b)
		
		// is faster than chr()
		$arrChr = array();
		for($i=0; $i<256; $i++)
			$arrChr[$i] = chr($i);
	
		// creates image data
		$bgfillcolor = array("red"=>0, "green"=>0, "blue"=>0);
	
		// bottom to top - left to right - attention blue green red !!!
		$y=$height-1;
		for ($y2=0; $y2<$height; $y2++) 
		{
			for ($x=0; $x<$widthFloor;) 
			{
				$rgb = imagecolorsforindex($img, imagecolorat($img, $x++, $y));
				$result .= $arrChr[$rgb["blue"]].$arrChr[$rgb["green"]].$arrChr[$rgb["red"]];
				$rgb = imagecolorsforindex($img, imagecolorat($img, $x++, $y));
				$result .= $arrChr[$rgb["blue"]].$arrChr[$rgb["green"]].$arrChr[$rgb["red"]];
				$rgb = imagecolorsforindex($img, imagecolorat($img, $x++, $y));
				$result .= $arrChr[$rgb["blue"]].$arrChr[$rgb["green"]].$arrChr[$rgb["red"]];
				$rgb = imagecolorsforindex($img, imagecolorat($img, $x++, $y));
				$result .= $arrChr[$rgb["blue"]].$arrChr[$rgb["green"]].$arrChr[$rgb["red"]];
				$rgb = imagecolorsforindex($img, imagecolorat($img, $x++, $y));
				$result .= $arrChr[$rgb["blue"]].$arrChr[$rgb["green"]].$arrChr[$rgb["red"]];
				$rgb = imagecolorsforindex($img, imagecolorat($img, $x++, $y));
				$result .= $arrChr[$rgb["blue"]].$arrChr[$rgb["green"]].$arrChr[$rgb["red"]];
				$rgb = imagecolorsforindex($img, imagecolorat($img, $x++, $y));
				$result .= $arrChr[$rgb["blue"]].$arrChr[$rgb["green"]].$arrChr[$rgb["red"]];
				$rgb = imagecolorsforindex($img, imagecolorat($img, $x++, $y));
				$result .= $arrChr[$rgb["blue"]].$arrChr[$rgb["green"]].$arrChr[$rgb["red"]];
			}
			for ($x=$widthFloor; $x<$widthCeil; $x++) 
			{
				$rgb = ($x<$widthOrig) ? imagecolorsforindex($img, imagecolorat($img, $x, $y)) : $bgfillcolor;
				$result .= $arrChr[$rgb["blue"]].$arrChr[$rgb["green"]].$arrChr[$rgb["red"]];
			}
			$y--;
		}
		
		// see imagegif
		if($filename == '')
		{
			echo $result;
		} 
		else 
		{
			$file = fopen($filename, "wb");
			fwrite($file, $result);
			fclose($file);
		}
	}
	function ImageToBMPEx ($im, $fn = false)
	{
		if (!$im) 
			return false;
		
		if ($fn === false) 
			$fn = 'php://output';
		$f = fopen ($fn, "w");
		if (!$f) 
			return false;
		
		//Image dimensions
		$biWidth = imagesx ($im);
		$biHeight = imagesy ($im);
		$biBPLine = $biWidth * 3;
		$biStride = ($biBPLine + 3) & ~3;
		$biSizeImage = $biStride * $biHeight;
		$bfOffBits = 54;
		$bfSize = $bfOffBits + $biSizeImage;
		
		//BITMAPFILEHEADER
		fwrite ($f, 'BM', 2);
		fwrite ($f, pack ('VvvV', $bfSize, 0, 0, $bfOffBits));
		
		//BITMAPINFO (BITMAPINFOHEADER)
		fwrite ($f, pack ('VVVvvVVVVVV', 40, $biWidth, $biHeight, 1, 24, 0, $biSizeImage, 0, 0, 0, 0));
		
		$numpad = $biStride - $biBPLine;
		for ($y = $biHeight - 1; $y >= 0; --$y)
		{
			for ($x = 0; $x < $biWidth; ++$x)
			{
				$col = imagecolorat ($im, $x, $y);
				fwrite ($f, pack ('V', $col), 3);
			}
			for ($i = 0; $i < $numpad; ++$i)
				fwrite ($f, pack ('C', 0));
		}
		fclose ($f);
		
		return true;
	}

	// ImageBMP helpers
	function int_to_dword($n)
	{
		return chr($n & 255).chr(($n >> 8) & 255).chr(($n >> 16) & 255).chr(($n >> 24) & 255);
	}
	function int_to_word($n)
	{
		return chr($n & 255).chr(($n >> 8) & 255);
	}


	function BMP2GD($src, $dest) 
	{
		if(!($src_f = fopen($src, "rb"))) 
			return false;
		if(!($dest_f = fopen($dest, "wb"))) 
			return false;

		$header = unpack("vtype/Vsize/v2reserved/Voffset", fread($src_f,14));
		$info = unpack("Vsize/Vwidth/Vheight/vplanes/vbits/Vcompression/Vimagesize/Vxres/Vyres/Vncolor/Vimportant",
		fread($src_f, 40));
		
		extract($info);
		extract($header);
		
		if($type != 0x4D42) // signature "BM"
			return false;

		$palette_size = $offset - 54;
		$ncolor = $palette_size / 4;
		$gd_header = "";
		
		// true-color vs. palette
		$gd_header .= ($palette_size == 0) ? "\xFF\xFE" : "\xFF\xFF";
		$gd_header .= pack("n2", $width, $height);
		$gd_header .= ($palette_size == 0) ? "\x01" : "\x00";
		if($palette_size) 
			$gd_header .= pack("n", $ncolor);
		
		// no transparency
		$gd_header .= "\xFF\xFF\xFF\xFF";
		
		fwrite($dest_f, $gd_header);
		
		if($palette_size) 
		{
			$palette = fread($src_f, $palette_size);
			$gd_palette = '';
			$j = 0;
			while($j < $palette_size) 
			{
				$b = $palette{$j++};
				$g = $palette{$j++};
				$r = $palette{$j++};
				$a = $palette{$j++};
				$gd_palette .= "$r$g$b$a";
			}
			$gd_palette .= str_repeat("\x00\x00\x00\x00", 256 - $ncolor);
			fwrite($dest_f, $gd_palette);
		}
		
		$scan_line_size = (($bits * $width) + 7) >> 3;
		$scan_line_align = ($scan_line_size & 0x03) ? 4 - ($scan_line_size & 0x03) : 0;
		
		for($i = 0, $l = $height - 1; $i < $height; $i++, $l--) 
		{
			// BMP stores scan lines starting from bottom
			fseek($src_f, $offset + (($scan_line_size + $scan_line_align) * $l));
			$scan_line = fread($src_f, $scan_line_size);
			if($bits == 24) 
			{
				$gd_scan_line = "";
				$j = 0;
				while($j < $scan_line_size) 
				{
					$b = $scan_line{$j++};
					$g = $scan_line{$j++};
					$r = $scan_line{$j++};
					$gd_scan_line .= "\x00$r$g$b";
				}
			}
			else if($bits == 8) 
			{
				$gd_scan_line = $scan_line;
			}
			else if($bits == 4) 
			{
				$gd_scan_line = "";
				$j = 0;
				while($j < $scan_line_size) 
				{
					$byte = ord($scan_line{$j++});
					$p1 = chr($byte >> 4);
					$p2 = chr($byte & 0x0F);
					$gd_scan_line .= "$p1$p2";
				}
				
				$gd_scan_line = substr($gd_scan_line, 0, $width);
			}
			else if($bits == 1) 
			{
				$gd_scan_line = "";
				$j = 0;
				while($j < $scan_line_size) 
				{
					$byte = ord($scan_line{$j++});
					$p1 = chr((int) (($byte & 0x80) != 0));
					$p2 = chr((int) (($byte & 0x40) != 0));
					$p3 = chr((int) (($byte & 0x20) != 0));
					$p4 = chr((int) (($byte & 0x10) != 0));
					$p5 = chr((int) (($byte & 0x08) != 0));
					$p6 = chr((int) (($byte & 0x04) != 0));
					$p7 = chr((int) (($byte & 0x02) != 0));
					$p8 = chr((int) (($byte & 0x01) != 0));
					$gd_scan_line .= "$p1$p2$p3$p4$p5$p6$p7$p8";
				}
				$gd_scan_line = substr($gd_scan_line, 0, $width);
			}

			fwrite($dest_f, $gd_scan_line);
		}
		
		fclose($src_f);
		fclose($dest_f);
		return true;
	}

	function imagecreatefrombmp($filename) 
	{
		$tmp_name = tempnam("/tmp", "GD");
		if($this->BMP2GD($filename, $tmp_name)) 
		{
			$img = imagecreatefromgd($tmp_name);
			@unlink($tmp_name);
			return $img;
		}
		return false;
	}

	function Convert($source, $destination, $width = 0, $height = 0, $quality = 100, $rgb = 0xFFFFFF, $fixed_size = 0, $rgb_transparent_for_png = true, $zoom = false)
	{
		$this->ConvertInfo = array(
			'Success' => true,
			'ErrorMessage' => '',
			'width' => 0,
			'height' => 0,
		);
		
		$sourceType = $GLOBALS['Utils']->getFileExtension($source);
		$sourceType = ($sourceType == 'jpg') ? 'jpeg' : $sourceType;
		$destinationType = $GLOBALS['Utils']->getFileExtension($destination);
		
		if (empty($sourceType) || empty($destinationType))
		{
			$this->ConvertInfo['Success'] = false;
			$this->ConvertInfo['ErrorMessage'] = 'Extension is empty.';
			return -1;
		}
		
		
		$icfunc = "imagecreatefrom" . $sourceType;
		$isrc = null;
		if ($sourceType == 'bmp')
			$isrc = $this->imagecreatefrombmp($source);
		else if ( function_exists($icfunc) ) 
			$isrc = $icfunc($source);
		else
		{
			$this->ConvertInfo['Success'] = false;
			$this->ConvertInfo['ErrorMessage'] = 'Unsupported function: ' . $icfunc;
			return -2;
		}
		
		$sourceInfo = getimagesize($source);
		
		if ($width == 0 || $height == 0)
		{
			$width = $sourceInfo[0];
			$height = $sourceInfo[1];
		}
		
		$idest = null;
		$new_left = $new_top = 0; 
		$new_width = $new_height = 0;
		if ($fixed_size)
		{
			$new_width = $ex_width = $sourceInfo[0];
			$new_height = $ex_height = $sourceInfo[1];
			if($ex_width >= $ex_height)
			{
				if($ex_width > $width)
				{
					$new_width  = $width;
					$ratio_ex     = $ex_width / $new_width;		
					$new_height = round($ex_height / $ratio_ex);
					if($new_height > $height)
						$new_height = $height;
				}
			}
			else if($ex_width < $ex_height) //portrait image
			{
				if ($ex_height > $height)
				{
					$new_height = $height;
					$ratio_ex = $ex_height / $new_height;
					$new_width = round($ex_width / $ratio_ex);
					if($new_width > $width)
						$new_width = $width;
				}
			}
			
			$new_left = round(($width - $new_width)/2);
			if($new_left == 0)
				$new_left = 1;
			$new_top = round(($height - $new_height)/2);
			if($new_top == 0)
				$new_top = 1;
			
			$idest = imagecreatetruecolor($width, $height);
		}
		else
		{
			$x_ratio = $width / $sourceInfo[0];
			$y_ratio = $height / $sourceInfo[1];
			
			$ratio       = min($x_ratio, $y_ratio);
			$use_x_ratio = ($x_ratio == $ratio);
			
			$new_width = $use_x_ratio  ? $width  : floor($sourceInfo[0] * $ratio);
			$new_height = !$use_x_ratio ? $height : floor($sourceInfo[1] * $ratio);
			
			if ($new_width > $sourceInfo[0] && !$zoom)
				$new_width = $sourceInfo[0];
			if ($new_height > $sourceInfo[1] && !$zoom)
				$new_height = $sourceInfo[0];
			
			$idest = imagecreatetruecolor($new_width, $new_height);
		}
		
		if ($rgb_transparent_for_png)
			imagecolortransparent($idest, $rgb);
		
		imagefill($idest, 0, 0, $rgb);
		imagecopyresampled($idest, $isrc, $new_left, $new_top, 0, 0, $new_width, $new_height, $sourceInfo[0], $sourceInfo[1]);
		
		if ($destinationType == 'bmp')
			$this->ImageToBMPEx($idest, $destination);
		else
		{
			$isfunc = 'image' . (($destinationType == 'jpg') ? 'jpeg' : $destinationType);
			if ($isfunc == 'imagejpeg')
				$isfunc($idest, $destination, $quality);
			else
				$isfunc($idest, $destination);
		}
		
		if ( file_exists($destination) )
		{
			$resultInfo = getimagesize($destination);
			$this->ConvertInfo['width'] = $resultInfo[0];
			$this->ConvertInfo['height'] = $resultInfo[1];
		}
		else
		{
			$this->ConvertInfo['Success'] = false;
			$this->ConvertInfo['ErrorMessage'] = 'Unable to create destination file';
		}
		
		imagedestroy($isrc);
		imagedestroy($idest);
		unset($isrc);
		unset($idest);
		return 1;
	}
	
	// -r fps Set frame rate (Hz value, fraction or abbreviation), (default = 25).
	// -b bitrate Set the video bitrate in bit/s (default = 200 kb/s).
	// -ar freq Set the audio sampling frequency (default = 44100 Hz).
	// -s size Set frame size. The format is wxh (ffserver default = 160x128, ffmpeg default = same as source). The following abbreviations are recognized:
	// -i Input file
	
	function VideoConvert($source, $base_filename, $destination_folder, $destination_folder_relative)
	{
		global $config;
		
		$info = array();
		$info['status'] = false;
		$info['output'] = array();
		$info['width'] = 0;
		$info['height'] = 0;
		$info['frames'] = array();
		$video_destination = $destination_folder . '/' . $base_filename . '.flv';
		$video_destination_relative = $destination_folder_relative . '/' . $base_filename . '.flv';
		
		if (!class_exists('ffmpeg_movie'))
		{
			$info['error'] = 'FFFMPEG not installed';
			return $info;
		}
		if (file_exists($video_destination))
		{
			$info['error'] = 'Destination file already exists';
			return $info;
		}
		if (!file_exists($destination_folder) || !is_dir($destination_folder))
		{
			$info['error'] = 'Destination folder not exists';
			return $info;
		}
		
		$info['sourceType'] = $GLOBALS['Utils']->getFileExtension($source);
		$info['sourceFile'] = $source;
		$info['videoDdestinationFile'] = $video_destination;
		$info['videoRelativeDdestinationFile'] = $video_destination_relative;
		$info['DestinationFolder'] = $destination_folder;
		
		if (count($config['SupportedVideoFormats']))
		{
			if (empty($info['sourceType']) || !in_array($info['sourceType'], $config['SupportedVideoFormats']))
			{
				$info['error'] = 'Invalid Video Format: ' . $info['sourceType'];
				return $info;
			}
		}
		$ffmpegObj = @new ffmpeg_movie($source,0);
		
		$srcWidth = $this->_makeMultipleTwo($ffmpegObj->getFrameWidth());
		$srcHeight = $this->_makeMultipleTwo($ffmpegObj->getFrameHeight());
		$srcDuration = $ffmpegObj->getDuration();
		
		$srcFPS = $ffmpegObj->getFrameRate();
		$srcBR = intval($ffmpegObj->getBitRate()/1000);
		
		$srcAB = intval($ffmpegObj->getAudioBitRate()/1000);
		$srcAR = $ffmpegObj->getAudioSampleRate();
		
		$setFPS = ($srcFPS == 0 || ($config['VideoFPS'] > 0 && $srcFPS > $config['VideoFPS'])) ? $config['VideoFPS'] : $srcFPS;
		$setBR = ($srcBR == 0 || ($config['VideoBitrate'] > 0 && $srcBR > $config['VideoBitrate'])) ? $config['VideoBitrate'] : $srcBR;
		
		$setAB = ($srcAB == 0 || ($config['AudioBitRate'] > 0 && $srcAB > $config['AudioBitRate'])) ? $config['AudioBitRate'] : $srcAB;
		$setAR = ($srcAR == 0 || ($config['AudioSampleRate'] > 0 && $srcAR > $config['AudioSampleRate'])) ? $config['AudioSampleRate'] : $srcAR;
		
		$setWidth = $srcWidth;
		$setHeight = $srcHeight;
		
		// resize only if destination sizes less than source sizes
		if ($config['VideoWidth'] < $srcWidth && $config['VideoHeight'] < $srcHeight)
		{
			$setWidth = $config['VideoWidth'];
			$setHeight = $config['VideoHeight'];
			$this->_getSize($srcWidth, $srcHeight, $setWidth, $setHeight);
		}
		$info['width'] = $setWidth;
		$info['height'] = $setHeight;
		$info['duration'] = (int)$srcDuration;
		
		$cmd = $config['FFMPEGPath'] . " -i " . $source . " -b " . $setBR . "k -g 20 -r " . $setFPS . " -ar " . $setAR . " -ab " . $setAB . " -f flv -s " . $setWidth . "x" . $setHeight . " " . $video_destination;
		$info['output'][] = $cmd;
		exec($cmd, $info['output']);
		
		if (!file_exists($video_destination))
		{
			$info['error'] = 'Unable to create Video File';
			return $info;
		}
		
		/* based on video dims */
		$previewWidth = $setWidth;
		$previewHeight = $setHeight;
		
		// make thumbnail from new created videofile
		if (is_array($config['PreviewFramesGenerate']) && count($config['PreviewFramesGenerate']) > 0)
		{
			foreach ($config['PreviewFramesGenerate'] as $frame)
			{
				$frameNumber = ($frame < 10) ? '0' . $frame : $frame;
				
				// for base frame 
				$ff_temp_preview_frame = $destination_folder . '/' . $base_filename . '%d.jpg';
				$temp_preview_frame = $destination_folder . '/' . $base_filename . '1.jpg';
				
				$frame_absolute = $destination_folder . '/' . $base_filename . '_' . $frameNumber . '.jpg';
				$frame_relative = $destination_folder_relative . '/' . $base_filename . '_' . $frameNumber . '.jpg';
				
				$cmd = $config['FFMPEGPath'] . " -i " . $video_destination . " -an -ss 00:00:" . $frameNumber . " -an -vframes 1 -y -s " . $srcWidth . "x" . $srcHeight . " " . $ff_temp_preview_frame;
				$info['output'][] = $cmd;
				exec($cmd, $info['output']);
				
				if (!file_exists($temp_preview_frame))
				{
					$info['error'] = 'Unable to create Temp Thumbnail Image(1)';
					return $info;
				}
				rename($temp_preview_frame, $frame_absolute);
				
				if (!file_exists($frame_absolute))
				{
					$info['error'] = 'Unable to rename Thumbnail';
					return $info;
				}
				else
				{
					// make icon and thumb
					$icon_filename = $base_filename . '_i' . $frameNumber . '_ico.jpg';
					$icon_absolute = $destination_folder . '/' . $icon_filename;
					$icon_relative = $destination_folder_relative . '/' . $icon_filename;
					
					$thumb_filename = $base_filename . '_t' . $frameNumber . '_thumb.jpg';
					$thumb_absolute = $destination_folder . '/' . $thumb_filename;
					$thumb_relative = $destination_folder_relative . '/' . $thumb_filename;
					
					// make  required include '%d' into path for correct ffmpeg work
					$this->Convert($frame_absolute, $thumb_absolute, $config['VideoThumbnailWidth'], $config['VideoThumbnailHeight']);
					$ThumbRes = $this->ConvertInfo;
					$this->Convert($frame_absolute, $icon_absolute, $config['VideoIconWidth'], $config['VideoIconHeight']);
					$IcoRes = $this->ConvertInfo;
					
					// there can be get info about width / height
					$info['frames'][$frame] = array(
						'thumb_filename' => $thumb_filename,
						'thumb_absolute' => $thumb_absolute,
						'thumb_relative' => $thumb_relative,
						'thumb_width' => $ThumbRes['width'],
						'thumb_height' => $ThumbRes['height'],
						'icon_filename' => $icon_filename,
						'icon_absolute' => $icon_absolute,
						'icon_relative' => $icon_relative,
						'icon_width' => $IcoRes['width'],
						'icon_height' => $IcoRes['height'],
					);
					unlink($frame_absolute);
				}
			}
		}
		
		$info['status'] = true;
		return $info;
	}
	
	
	function FlashConvert($source, $swf_destination, $destination_folder)
	{
		$info = array();
		$info['status'] = false;
		$info['width'] = 0;
		$info['height'] = 0;
		
		if (!file_exists($destination_folder) || !is_dir($destination_folder))
		{
			$info['error'] = 'Destination folder not exists';
			return $info;
		}
		if (file_exists($swf_destination))
		{
			$info['error'] = 'Destination file already exists';
			return $info;
		}
		
		copy($source, $swf_destination);
		if (!file_exists($swf_destination))
		{
			$info['error'] = 'Unable to create Preview Swf';
			return $info;
		}
		else
		{
			for ($i = 0; $i < 3; $i++)
			{
				$size = getimagesize($swf_destination);
				if ( is_array($size) && count($size) >= 2 )
				{
					$info['width'] = $size[0];
					$info['height'] = $size[1];
					break;
				}
				sleep(1);
			}
			$info['status'] = true;
		}
		return $info;
	}
	
	function GetMediaInfo($source)
	{
		global $config;
		$info = array(
			'MediaType' => 'unknown',
			'Ext' => '',
			'Error' => false,
			'ErrorMessage' => ''
		);
		$info['Ext'] = $GLOBALS['Utils']->getFileExtension($source);
		 
		if (empty($info['Ext']))
		{
			$info['Error'] = 1;
			$info['ErrorMessage'] = 'Unknown file type';
			return $info;
		}
		if (in_array($info['Ext'], $config['SupportedImageFormats']))
		{
			$info['MediaType'] = 'image';
            $ifimg = @getimagesize($source);

            if ($ifimg !== false)
            {
                list($width, $height, $type, $attr) = $ifimg;
                $info['Width'] = (int)$width;
                $info['Height'] = (int)$height;
            }

			return $info;
		}
		if (in_array($info['Ext'], $config['SupportedVideoFormats']))
		{
			$info['MediaType'] = 'video';
			return $info;
		}
		if (in_array($info['Ext'], $config['SupportedFlashFormats']))
		{
//			$check_format = getimagesize($source);
//			if ( is_array($check_format) && count($check_format) >= 2 )
//			{
//				if ( isset($check_format['mime']) && !preg_match('/flash/i', $check_format['mime']))
//				{
//				}
//				else
//				{
					$info['MediaType'] = 'flash';
					return $info;
//				}
//			}				
		}
		$info['Error'] = 1;
		$info['ErrorMessage'] = 'Unsupported file type ' . $info['Ext'];
		
		return $info;
	}
	
	function _getSize($source_width, $source_height, &$dest_width, &$dest_height)
	{
		$x_ratio = $dest_width / $source_width;
		$y_ratio = $dest_height / $source_height;
		
		$ratio       = min($x_ratio, $y_ratio);
		$use_x_ratio = ($x_ratio == $ratio);
		
		$dest_width   = ($use_x_ratio)  ? $dest_width  : floor($source_width * $ratio);
		$dest_height  = (!$use_x_ratio) ? $dest_height : floor($source_height * $ratio);
	}
	function _makeMultipleTwo($value)
	{
		return (gettype($value/2) == "integer") ? $value : ($value-1);
	}
}

$GLOBALS['ImageLib'] = new ImageLib;


?>