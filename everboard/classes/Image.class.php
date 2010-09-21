<?php
/**
 * Image.class.php
 *
 * @copyright 2010, Onyx Creative Group - (onyxcreates.com)
 * @author Adrian Mummey - http://mummey.org
 * @version $Id$
**/

ini_set('gd.jpeg_ignore_warning', 1);

class Image{

  protected $path;
  protected $mime;
  protected $image;
  protected $new_width;
  protected $new_height;

  public function __construct($path, $mime){
    $this->path = $path;
    $this->mime = $mime;
    
    $this->processImage();
  }
  
  public function getProcessedImage($new_width, $new_height){
    if(!$this->image){
      return false;
    }
     $width = imagesx($this->image);
     $height = imagesy($this->image);
     
     // generate new w/h if not provided
    if( $new_width && !$new_height ) {
        
        $new_height = $height * ( $new_width / $width );
        
    } elseif($new_height && !$new_width) {
        
        $new_width = $width * ( $new_height / $height );
        
    } elseif(!$new_width && !$new_height) {
        
        $new_width = $width;
        $new_height = $height;
        
    }
    $this->new_width = $new_width;
    $this->new_height = $new_height;
    
    // create a new true color image
    $canvas = imagecreatetruecolor( $new_width, $new_height );
    imagealphablending($canvas, false);
    // Create a new transparent color for image
    $color = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
    // Completely fill the background of the new image with allocated color.
    imagefill($canvas, 0, 0, $color);
    // Restore transparency blending
    imagesavealpha($canvas, true);
    $src_x = $src_y = 0;
    $src_w = $width;
    $src_h = $height;

    $cmp_x = $width  / $new_width;
    $cmp_y = $height / $new_height;

    // calculate x or y coordinate and width or height of source

    if ( $cmp_x > $cmp_y ) {

        $src_w = round( ( $width / $cmp_x * $cmp_y ) );
        $src_x = round( ( $width - ( $width / $cmp_x * $cmp_y ) ) / 2 );

    } elseif ( $cmp_y > $cmp_x ) {

        $src_h = round( ( $height / $cmp_y * $cmp_x ) );
        $src_y = round( ( $height - ( $height / $cmp_y * $cmp_x ) ) / 2 );

    }
    
    imagecopyresampled( $canvas, $this->image, 0, 0, $src_x, $src_y, $new_width, $new_height, $src_w, $src_h );
    /*if (function_exists('imageconvolution')) {
  		$sharpenMatrix = array(
  			array(-1,-1,-1),
  			array(-1,16,-1),
  			array(-1,-1,-1),
  		);
  		$divisor = 8;
  		$offset = 0;
  
  		imageconvolution($canvas, $sharpenMatrix, $divisor, $offset);
  	}*/
  	return $this->showImage($canvas);
  }
  
  public function isImage(){
    return ($this->image)?(true):(false);
  }
  
  public function getNewSize(){
    return array('width'=>$this->new_width, 'height'=>$this->new_height);
  }
  protected function processImage(){
  
  	switch(strtolower($this->mime))
  	{
  		case 'image/jpg':
  		case 'image/jpeg':
  			$this->image = imagecreatefromjpeg($this->path);
  			break;
  		case 'images/gif':
  			$this->image = imagecreatefromgif($this->path);
  			break;
  		case 'image/png':
  			$this->image = imagecreatefrompng($this->path);
  			break;
  		default:
  			$this->image = false;
  			break;
  	}
  }
  
  protected function showImage($canvas){
    ob_start();
    switch(strtolower($this->mime))
  	{
  		case 'image/jpg':
  		case 'image/jpeg':
  		  imagejpeg($canvas, null, 95);
  			break;
  		case 'images/gif':
  			imagegif($canvas);
  			break;
  		case 'image/png':
  			imagepng($canvas, null, 9);
  			break;
  		default:
  			break;
  	}
  	$image = ob_get_contents();
  	ob_end_clean();
    return $image;
  }
}