<?php
namespace FuelSdk;

class ET_SendClassification extends ET_CUDWithUpsertSupport {

	/**
	* @var int Gets or sets the folder identifier.
	*/
	public $folderId;

	/**
	* Initializes a new instance of the class.
	*/
	function __construct() {
		$this->obj             = "SendClassification";
		$this->folderProperty  = "Category";
		$this->folderMediaType = "sendclassification";
	}

}

