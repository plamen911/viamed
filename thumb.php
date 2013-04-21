<?php
ini_set("memory_limit", "64M");

$maxwidth=$HTTP_GET_VARS['maxwidth'];
$maxheight=$HTTP_GET_VARS['maxheight'];
# Get image location
$image_path = $HTTP_GET_VARS['i'];
# Load image
$img = null;

$image_path = (is_file($image_path) && file_exists($image_path)) ? $image_path : 'img/__noimage.jpg';

if (function_exists('exif_imagetype')) {
	if (exif_imagetype("$image_path")==IMAGETYPE_JPEG) $ImageType = "jpeg";
	else if (exif_imagetype("$image_path")==IMAGETYPE_GIF) $ImageType = "gif";
	else if (exif_imagetype("$image_path")==IMAGETYPE_PNG) $ImageType = "png";
}
else {
	list($ImageWidth, $ImageHeight, $TypeCode) = getimagesize($image_path);
	$ImageType = ($TypeCode==1 ? "gif" : ($TypeCode==2 ? "jpeg" : ($TypeCode==3 ? "png" : FALSE)));
}

switch ($TypeCode) {
	case 'gif':
		$img = imagecreatefromgif("$image_path");
		break;
	case 'png':
		$img = imagecreatefrompng("$image_path");
		break;
	case 'jpeg':
	default:
		$img = imagecreatefromjpeg("$image_path");
		break;
}

# If an image was successfully loaded, test the image for size
if ($img) {

    # Get image size and scale ratio
    $width = imagesx($img);
    $height = imagesy($img);
    $scale = min($maxwidth/$width, $maxheight/$height);

    # If the image is larger than the max shrink it
    if ($scale < 1) {
        $new_width = floor($scale*$width);
        $new_height = floor($scale*$height);

        # Create a new temporary image
        $tmp_img = imagecreatetruecolor($new_width, $new_height);

        # Copy and resize old image into new image
        imagecopyresampled($tmp_img, $img, 0, 0, 0, 0,
                         $new_width, $new_height, $width, $height);
        imagedestroy($img);
        $img = $tmp_img;
    }
}

# Create error image if necessary
if (!$img) {
    $img = imagecreate($maxwidth, $maxheight);
    imagecolorallocate($img,0,0,0);
    $c = imagecolorallocate($img,70,70,70);
    imageline($img,0,0,$maxwidth, $maxheight,$c2);
    imageline($img,$maxwidth,0,0, $maxheight,$c2);
}

# Display the image
header("Content-type: image/jpeg");
imagepng($img);
?>