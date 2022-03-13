<?php
namespace FuelSdk;

class ET_Template extends ET_CUDWithUpsertSupport {

	/**
	* @var int Gets or sets the folder identifier.
	*/
	public $folderId;

	/**
	* Initializes a new instance of the class.
	*/
	function __construct() {
		$this->obj             = "Template";
		$this->folderProperty  = "Category";
		$this->folderMediaType = "template";
	}

}
