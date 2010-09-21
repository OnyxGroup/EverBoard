<?php

require_once("PEAR/Exception.php");

class HTTP_Exception extends PEAR_Exception {

	public function getCauseMessage() {
		$causes = parent::getCause();
		if (is_array($causes) && !empty($causes))
			return implode(", ", parent::getCause());
		else
			return "";
	}

}

?>