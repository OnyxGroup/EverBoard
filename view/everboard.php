<?php 
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . "config.php";
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . "utils.php";
require_once EVERBOARD_CODE_PATH.'reader.php';
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<title>Onyx EverBoard</title>
    <link rel="stylesheet" href="<?php print EVERBOARD_VIEW_URL; ?>css/style.css" media="screen" />
    <link rel="stylesheet" href="<?php print EVERBOARD_VIEW_URL; ?>css/jquery.fancybox-1.3.1.css" media="screen" />

    <script src="<?php print EVERBOARD_VIEW_URL; ?>js/jquery-1.4.2.min.js"></script>
    <script src="<?php print EVERBOARD_VIEW_URL; ?>js/jquery.masonry.js"></script>
    <script src="<?php print EVERBOARD_VIEW_URL; ?>js/default.js"></script>
    <script src="<?php print EVERBOARD_VIEW_URL; ?>js/jquery-ui-1.8.4.custom.min.js"></script>
    <script src="<?php print EVERBOARD_VIEW_URL; ?>js/jquery.fancybox-1.3.1.js"></script>
    <script src="<?php print EVERBOARD_VIEW_URL; ?>js/jquery.easing-1.3.pack.js"></script>
    <script src="<?php print EVERBOARD_VIEW_URL; ?>js/jquery.mousewheel-3.0.2.pack.js"></script>
	</head>
	<body>
	   <?php 
	     $notebook = getCurrentNotebook($notebooks);
	     if($notebook === false){
	       die('<h2>No notebooks found</h2>');
	     }
	   ?>
      
      <?php
      $tags = array();
      $palette = array();
      foreach ($notebook->notes as $noteGuid=>$note) {
        //Check for Palette
        if(trim(strtolower($note->title) == 'palette')){

          foreach($note->tagNames as $color){
            if(isHexColor($color) || (is_int($color) && (strlen($color) == 6 || strlen($color) == 3))){
              $palette[] = $color;
            }
          }
          unset($notebook->notes[$noteGuid]);
        }
        //Pull tags from notes that have images
      	else if ($note->tagNames && is_array($note->tagNames) && isset($note->hasImages) && $note->hasImages) {
      		foreach ($note->tagNames as $tag) {
      			$tags[cleanText($tag)] = htmlspecialchars($tag);
      			$notebook->notes[$noteGuid]->tagClass .= ' '.cleanText($tag);
      		}
      	}
      }
      ?>
     <div id="container">      
      <div id="header" class="clearfix">
        <h1><?php print htmlspecialchars($notebook->notebook->name); ?></h1>
        
         <div id="header-container">
          <div id="tag-list-wrap" class="column">
            <ul id="tag-list" class="clearfix">
              <li><a href="all#" id="all" class="special-tag">All Images</a></li>
              <li><a href="idea-list-text#" id="idea-list-text" class="special-tag">All Documents</a></li>              
            <?php foreach ($tags as $key=>$tag): ?>
              <li><a href="<?php print $key;?>#" id="<?php print $key; ?>"><?php print strtolower($tag); ?></a></li>
            <?php endforeach; ?>
            </ul>
          </div>
          <div id="tag-label" class="column">Tags:</div>
          <div id="project-select-wrap" class="column">
            <a id="project-select" href="#">Select Notebook</a>
            <ul id="project-list">
              <?php foreach($notebooks as $notebookGuid=>$the_notebook): ?>
                <li><a href="<?php print buildNotebookUrl($notebookGuid); ?>" title="<?php print htmlspecialchars($the_notebook->notebook->name); ?>"><?php print htmlspecialchars($the_notebook->notebook->name); ?></a></li>
              <?php endforeach; ?>
            </ul>
          </div>
        </div> <!--#header-container-->
      </div>
      <div id="palette-wrap">
        <?php if(count($palette)): ?>
        <?php $width = floor(100/count($palette)); ?>
        <table id="palette">
          <tr>
            <?php foreach($palette as $color): ?>
              <td valign="middle" align="center" style="width:<?php print $width;?>%;background-color:<?php print $color;?>;"><?php print $color;?></td>
            <?php endforeach; ?>
          </tr>
        </table>
        <?php endif; ?>
      </div>
      
     <?php $textNotes = array(); ?> 
      
  	 <ul id="primary"  class="wrap">
      <?php foreach ($notebook->notes as $noteGuid=>$note): ?>
        <?php if (isset($note->hasImages) && $note->hasImages): ?>
          <?php foreach ($note->resources as $key=>$resource): ?>
            <?php if (isImageMime($resource->mime)): ?>
          	   <li class="<?php print (isset($note->tagClass))?($note->tagClass):(''); ?> idea-list-img idea">
          	     <a href="<?php print EVERBOARD_CACHE_URL.$resource->localFilename; ?>" rel="<?php print htmlspecialchars($notebook->notebook->name); ?>" title="<?php print htmlspecialchars($note->title); ?>" class="idea-link"/>
                  <?php $imgSize = getimagesize($resource->absThumb); ?>
                  <img src="<?php print EVERBOARD_CACHE_URL.$resource->localThumb ?>" width="<?php print @floor($imgSize[0] * .75);?>" height="<?php print @floor($imgSize[1]*.75);?>" class="idea-img"/>
                 </a>
                 <?php if(isset($note->attributes->sourceURL) && $note->attributes->sourceURL): ?>
                   <a href="<?php print $note->attributes->sourceURL;?>" class="source-url" style="display:none;">View Original</a>
                 <?php endif; ?>
          	   </li>
          	 <?php endif; ?>
          <?php endforeach;?>
        <?php else: ?>
          <li class="idea-list-text idea">
            <h3><?php print htmlspecialchars($note->title); ?> </h3>

             <iframe  width="485" height="400" src="<?php print buildAjaxUrl($notebook->notebook->guid, $noteGuid);?>"></iframe>
          </li>
      	<?php endif; ?>
  	   <?php endforeach; ?>
  	 </ul>
    </div>
 
	</body>
</html>