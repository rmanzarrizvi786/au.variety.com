<?php
// phpcs:disable WordPress.NamingConventions --- We're using third party library
// TODO: These codes will get re-factor

use PMC\Exacttarget\Api;

use FuelSdk\ET_ContentArea;
use FuelSdk\ET_DataExtension;
use FuelSdk\ET_DataExtension_Row;
use FuelSdk\ET_Email;
use FuelSdk\ET_Email_SendDefinition;
use FuelSdk\ET_Folder;
use FuelSdk\ET_List;
use FuelSdk\ET_SendClassification;
use FuelSdk\ET_Send;
use FuelSdk\ET_Subscriber;
use FuelSdk\ET_Template;
use FuelSdk\ET_TriggeredSend;
use PMC\Exacttarget\Email_Rest;
use PMC\Exacttarget\Folders_Rest;
use PMC\Exacttarget\Rest_Error;
use PMC\Exacttarget\Templates_Rest;
use PMC\Exacttarget\Rest_Request;
use PMC\Exacttarget\Content_Block_Rest;

class Exact_Target {

	private static $_whitelist_folder = 'active wordpress sends';

	/**
	 * This is the default folder in ET where all the content blocks used for recurring newsletters are stored.
	 *
	 * @var string
	 */
	private static $_default_content_block_folder = 'my contents';

	// This helper function return true if plugin is enabled and configured
	public static function is_active() {
		return Api::get_instance()->is_active();
	}

	public static function get_object( array $config = [], $force = false ) {
		return Api::get_instance()->get_client( $config, $force );
	}

	public static function get_lists() {

		$et_client = self::get_object();
		if ( empty( $et_client ) || ! is_object( $et_client ) ) {
			return [];
		}

		$lists           = new ET_List();
		$lists->authStub = $et_client;
		$lists->props    = array(
			'ID',
			'ListName',
		);

		$response = $lists->get();

		$list_array = array();

		$results = self::get_results( $response );

		while ( $response->moreResults ) {
			$response = $lists->GetMoreResults();

			$results = array_merge( $results, self::get_results( $response ) );
		}

		foreach ( $results as $result ) {
			$list_array[ $result->ID ] = $result->ListName;
		}

		return $list_array;
	}

	/**
	 * Return templates from whitelisted folder if it exist or all templates
	 *
	 * Return single template if template ID passed.
	 *
	 * @param string $id ID   of the template.
	 *
	 * @return array|object $template_array
	 */
	public static function get_templates( $id = '' ) {

		$et_client = static::get_object();
		if ( empty( $et_client ) ) {
			return [];
		}

		$templates           = new ET_Template();
		$templates->authStub = $et_client;
		$templates->props    = array(
			'ID',
			'TemplateName',
			'LayoutHTML',
		);

		if ( ! empty( $id ) ) {

			$templates->filter = array(
				'Property'       => 'ID',
				'SimpleOperator' => 'equals',
				'Value'          => $id,
			);

		} else {

			$folders = static::get_folders( 'template' );

			$folders_ids = static::get_whitelisted_folders_ids( $folders );

			if ( ! empty( $folders_ids ) ) {

				$templates->filter = array(
					'Property'       => 'CategoryID',
					'SimpleOperator' => ( count( $folders_ids ) > 1 ) ? 'IN' : 'equals',
					'Value'          => $folders_ids,
				);

			}
		}

		$response = $templates->get();

		$template_array = array();

		$results = static::get_results( $response );

		while ( $response->moreResults ) {
			$response = $templates->GetMoreResults();

			$results = array_merge( $results, static::get_results( $response ) );
		}

		foreach ( $results as $result ) {
			if ( ! empty( $id ) ) {
				return $result;
			} else {
				$template_array[ $result->ID ] = $result->TemplateName;
			}
		}

		return $template_array;
	}

	/**
	 * Return templates from allowed folders in ET, if allowed folder don't exist then returns all templates.
	 *
	 * @see https://developer.salesforce.com/docs/atlas.en-us.mc-apis.meta/mc-apis/assetAdvancedQuery.htm
	 *
	 * @return array
	 */
	public static function get_templates_from_content_builder() : array {

		$et_templates = new Templates_Rest();
		$filter       = [];

		$folders     = static::get_folders_from_content_builder();
		$folders_ids = (array) static::get_allowed_folders_ids_from_content_builder( $folders );

		if ( ! empty( $folders_ids ) ) {

			$filter = [
				'property'       => 'category.id',
				'simpleOperator' => 'IN',
				'value'          => $folders_ids,
			];
		}

		$template_args = [
			'query'  => $filter,
			'fields' => [
				'ID',
				'name',
			],
		];

		$response = $et_templates->get( $template_args );
		$results  = [];

		$templates = $response->get_result_items();

		while ( $response->more_results ) {

			$response->get_more_results();
			$templates = array_merge( $templates, $response->get_result_items() );
		}

		foreach ( $templates as $template ) {

			if ( ! empty( $template->id ) ) {

				$results[ $template->id ] = $template->name;
			}
		}

		return $results;
	}

	/**
	 * Gets a template from ET based on ID.
	 *
	 * @param string $id            ID of the template.
	 * @param string $use_legacy_id Whether to use legacy ID or Asset ID for retrieving the template.
	 *
	 * @see https://developer.salesforce.com/docs/atlas.en-us.mc-apis.meta/mc-apis/assetAdvancedQuery.htm
	 *
	 * @return Rest_Request|Rest_Error
	 */
	public static function get_template_from_content_builder_by_id( $id = '', $use_legacy_id = false ) {

		if ( empty( $id ) ) {
			return new Rest_Error( 'Missing ID for fetching template!' );
		}

		$templates = new Templates_Rest();

		$template_args = [
			'query'  => [
				'property'       => $use_legacy_id ? 'data.email.legacy.legacyId' : 'id',
				'simpleOperator' => 'equal',
				'value'          => $id,
			],
			'fields' => [
				'ID',
				'name',
				'content',
			],
		];

		$response = $templates->get( $template_args );
		$template = $response->get_result_items();

		if ( empty( $template ) || ! is_array( $template ) ) {
			return new Rest_Error( sprintf( 'Could not find template in ET, Template ID: %s, Error: %s ', $id, $response->message ) );
		}

		unset( $response->results );
		$response->results = $template[0];

		return $response;
	}

	public static function get_sendclassifications() {

		$et_client = self::get_object();
		if ( empty( $et_client ) ) {
			return [];
		}

		$send_class           = new ET_SendClassification();
		$send_class->authStub = $et_client;
		$send_class->props    = array(
			'Name',
			'CustomerKey',
		);

		$response = $send_class->get();

		$list_array = array();

		$results = self::get_results( $response );

		while ( $response->moreResults ) {
			$response = $send_class->GetMoreResults();

			$results = array_merge( $results, self::get_results( $response ) );
		}

		if ( ! empty( $results ) ) {
			foreach ( $results as $result ) {
				$list_array[ $result->CustomerKey ] = $result->Name;
			}
		}

		return $list_array;
	}

	/**
	 * Get folder list based on content type.
	 *
	 * @param string $content_type Content type of folder. i.e dataextension, template and etc.
	 *
	 * @return array $results Array of folders.
	 */
	public static function get_folders( $content_type ) {

		$et_client = static::get_object();
		if ( empty( $et_client ) ) {
			return [];
		}

		$folder           = new ET_Folder();
		$folder->authStub = $et_client;
		$folder->filter   = array(
			'Property'       => 'ContentType',
			'SimpleOperator' => 'equals',
			'Value'          => $content_type,
		);

		$response = $folder->get();

		$results = static::get_results( $response );

		while ( $response->moreResults ) {
			$response = $folder->GetMoreResults();

			$results = array_merge( $results, static::get_results( $response ) );
		}

		return $results;

	}

	/**
	 * Get folder list based on content type.
	 *
	 * @param string $content_type Content type of folder. i.e dataextension, template and etc.
	 *
	 * @return array $results Array of folders.
	 */
	public static function get_folders_from_content_builder() {

		$folder = new Folders_Rest();

		$response = $folder->get();

		$results = $response->get_result_items();

		while ( $response->more_results ) {
			$response->get_more_results();
			$results = array_merge( $results, $response->get_result_items() );
		}

		return $results;
	}

	/**
	 * Return whitelisted folder ids from where Data Extension and Templates will fetch.
	 *
	 * @param array $folders Array of folders list.
	 * @param int   $parent  ID of parent folder.
	 *
	 * @return array
	 */
	public static function get_whitelisted_folders_ids( $folders, $parent = 0 ) : array {

		if ( empty( $folders ) ) {
			return [];
		}

		$folders_ids = array();

		foreach ( $folders as $folder ) {

			// If current folder is whitelisted folder then save its ID and make recursive call to get its child folders ID.
			if ( 0 === intval( $parent ) && ! empty( $folder->Name ) && strtolower( $folder->Name ) === static::$_whitelist_folder ) {

				$folders_ids[] = $folder->ID;
				$folders_ids   = array_merge( $folders_ids, static::get_whitelisted_folders_ids( $folders, $folder->ID ) );

				break;

			} elseif ( 0 !== intval( $parent ) ) {

				// If current folder is child of whitelisted folder then save its ID and make recursive call to get its child folders ID.
				if ( $folder->ParentFolder->ID === $parent ) {

					$folders_ids[] = $folder->ID;
					$folders_ids   = array_merge( $folders_ids, static::get_whitelisted_folders_ids( $folders, $folder->ID ) );

				}
			}
		}

		return $folders_ids;

	}

	/**
	 * Return allowed folder ids from where Data Extension and Templates will fetch.
	 *
	 * @param array $folders Array of folders list.
	 * @param int   $parent  ID of parent folder.
	 *
	 * @return array
	 */
	public static function get_allowed_folders_ids_from_content_builder( $folders, $parent = 0 ) {

		if ( empty( $folders ) ) {
			return [];
		}

		$folders_ids = [];

		foreach ( $folders as $folder ) {

			// If current folder is allowed folder then save its ID and make recursive call to get its child folders ID.
			if ( 0 === intval( $parent ) && ! empty( $folder->name ) && strtolower( $folder->name ) === static::$_whitelist_folder ) {

				$folders_ids[] = $folder->id;
				$folders_ids   = array_merge( $folders_ids, static::get_allowed_folders_ids_from_content_builder( $folders, $folder->id ) );

				break;

			} elseif ( 0 !== intval( $parent ) ) {

				// If current folder is child of allowed folder then save its ID and make recursive call to get its child folders ID.
				if ( $folder->parentId === $parent ) {

					$folders_ids[] = $folder->id;
					$folders_ids   = array_merge( $folders_ids, static::get_allowed_folders_ids_from_content_builder( $folders, $folder->id ) );
				}
			}
		}

		return $folders_ids;
	}

	/**
	 * Get Data Extensions from whitelisted folder if it exist or all Data Extensions.
	 *
	 * @return array
	 */
	public static function get_data_extensions() {

		$et_client = static::get_object();
		if ( empty( $et_client ) ) {
			return [];
		}

		$data_extensions           = new ET_DataExtension();
		$data_extensions->authStub = $et_client;
		$data_extensions->props    = array( 'CustomerKey', 'Name' );

		$folders = static::get_folders( 'dataextension' );

		$folders_ids = static::get_whitelisted_folders_ids( $folders );

		if ( ! empty( $folders_ids ) ) {

			$data_extensions->filter = array(
				'Property'       => 'CategoryID',
				'SimpleOperator' => ( count( $folders_ids ) > 1 ) ? 'IN' : 'equals',
				'Value'          => $folders_ids,
			);

		}

		$response = $data_extensions->get();

		$data_extension_array = array();

		$results = static::get_results( $response );

		while ( $response->moreResults ) {
			$response = $data_extensions->GetMoreResults();

			$results = array_merge( $results, static::get_results( $response ) );
		}

		if ( ! empty( $results ) ) {
			foreach ( $results as $result ) {
				$data_extension_array[ $result->CustomerKey ] = $result->Name;
			}
		}

		return $data_extension_array;
	}

	public static function upsert_email( $name, $subject, $html ) {
		$email = self::get_email( $name );
		if ( ! empty( $email ) ) {
			$et_email = self::update_email( $name, $subject, $html );
		} else {
			$et_email = self::create_email( $name, $subject, $html );
		}

		return $et_email;
	}

	/**
	 * Updates an email in content builder if it exists or adds it.
	 *
	 * @param string $name    Name of the email to upsert.
	 * @param string $subject Subject of the email.
	 * @param string $content Content of the email.
	 *
	 * @return Rest_Request|Rest_Error
	 */
	public static function upsert_email_to_content_builder( $name, $subject, $content ) {

		if (
			empty( $name )
			|| empty( $subject )
			|| empty( $content )
		) {
			return new Rest_Error( 'One or more required params are missing' );
		}

		$et_email = new Email_Rest();
		$email    = $et_email->get_email_by_name( $name, [ 'name' ] );

		if ( true === $email->status && ! empty( $email->results ) && ! empty( $email->results->id ) ) {

			// Email exists, update it.
			$email = $et_email->update( $email->results->id, $name, $subject, $content );

		} else {

			// Email doesn't exists so create it.
			$email_args = array(
				'name'    => $name,
				'subject' => $subject,
				'content' => $content,
			);

			$email = $et_email->create( $email_args );
		}

		return $email;
	}

	/**
	 * Structures post body for api call to create Email in Salesforce in Content Builder.
	 *
	 * @param string $name    email name
	 * @param string $subject email subject line
	 * @param string $html    template markup
	 *
	 * @return array|object response from Salesforce API
	 */
	public static function create_email_in_content_builder( $name, $subject, $html ) {

		if (
			empty( $name )
			|| empty( $subject )
			|| empty( $html )
		) {
			return [];
		}

		$post_email = new Email_Rest();
		$email      = [
			'name'    => $name,
			'subject' => $subject,
			'content' => $html,
		];

		$post_result = $post_email->create( $email );

		return self::get_results( $post_result );
	}

	/**
	 * Structures post body for api call to create Email in Salesforce
	 *
	 * @param string $name email name
	 * @param string $subject email subject line
	 * @param string $html template markup
	 *
	 * @return array response from Salesforce API
	 */
	public static function create_email( $name, $subject, $html ) {

		$et_client = self::get_object();
		if ( empty( $et_client ) ) {
			return null;
		}

		$post_email           = new ET_Email();
		$post_email->authStub = $et_client;
		$post_email->props    = array(
			'CustomerKey' => $name,
			'Name'        => $name,
			'Subject'     => $subject,
			'HTMLBody'    => $html,
			'EmailType'   => 'HTML',
			'IsHTMLPaste' => 'true',
		);

		$post_result = $post_email->post();

		return self::get_results( $post_result );
	}

	public static function update_email( $name, $subject, $html ) {

		$et_client = self::get_object();
		if ( empty( $et_client ) ) {
			return null;
		}

		$patch_email           = new ET_Email();
		$patch_email->authStub = $et_client;
		$patch_email->props    = array(
			'CustomerKey' => $name,
			'Name'        => $name,
			'Subject'     => $subject,
			'HTMLBody'    => $html,
		);
		$patch_result          = $patch_email->patch();

		return self::get_results( $patch_result );

	}

	public static function get_email( $key = '', $Property = 'CustomerKey', $return_type = 'list' ) {

		$et_client = self::get_object();
		if ( empty( $et_client ) ) {
			return null;
		}

		$get_email           = new ET_Email();
		$get_email->authStub = $et_client;

		if ( ! empty( $key ) ) {
			$get_email->filter = array(
				'Property'       => $Property,
				'SimpleOperator' => 'equals',
				'Value'          => $key,
			);
		}

		$get_email->props = array(
			'ID',
			'PartnerKey',
			'CreatedDate',
			'ModifiedDate',
			'Client.ID',
			'Name',
			'Folder',
			'CategoryID',
			'HTMLBody',
			'TextBody',
			'Subject',
			'IsActive',
			'IsHTMLPaste',
			'ClonedFromID',
			'Status',
			'EmailType',
			'CharacterSet',
			'HasDynamicSubjectLine',
			'ContentCheckStatus',
			'Client.PartnerClientKey',
			'ContentAreas',
			'CustomerKey',
		);
		$get_response     = $get_email->get();

		$results = self::get_results( $get_response );

		while ( $get_response->moreResults ) {
			$get_response = $get_email->GetMoreResults();
			$results      = array_merge( $results, self::get_results( $get_response ) );
		}

		if ( 'list' !== $return_type ) {
			return $results;
		}

		$email_array = array();
		if ( ! empty( $results ) ) {
			foreach ( $results as $result ) {
				$email_array[ $result->ID ] = $result->Name;
			}
		}

		return $email_array;
	}

	/**
	 * Helper for retrieving email from Content Builder using field mentioned in $field param.
	 *
	 * @param mixed  $value Value for the field provided in $field param.
	 * @param string $field Name of the field to use for fetching the email, e.g. ID, name etc.
	 *
	 * @return Rest_Request|Rest_Error
	 */
	public static function get_email_from_content_builder( $value, $field = 'ID' ) {

		if ( empty( $value ) || empty( $field ) ) {
			return new Rest_Error( 'Required parameters are missing' );
		}

		$get_email = new Email_Rest();
		$response  = $get_email->query_email_by( $field, $value );

		return $response;
	}

	public static function delete_email( $value, $type = 'CustomerKey' ) {

		$et_client = self::get_object();
		if ( empty( $et_client ) ) {
			return false;
		}

		$email = self::get_email( $value, $type, 'object' );

		if ( ! empty( $email[0]->Name ) ) {
			self::delete_content( $email[0]->Name );
		}

		$delete_email           = new ET_Email();
		$delete_email->authStub = $et_client;
		$delete_email->props    = array( $type => $value );
		$result                 = $delete_email->delete();

		return $result->status;

	}

	/**
	 * Helper for deleting email in content buider.
	 *
	 * @param string|int $email_id ID.
	 *
	 * @return bool True if successfully deleted, false otherwise.
	 */
	public static function delete_email_from_content_builder( $email_id ) {

		if ( empty( $email_id ) ) {
			return false;
		}

		$et_email = new Email_Rest();
		$email    = $et_email->get_email_by_id( $email_id, [ 'name' ] );

		if ( false === $email->status || empty( $email->results ) ) {
			return false;
		}

		return $et_email->delete( $email_id );
	}

	public static function delete_content( $value, $type = 'CustomerKey' ) {

		$et_client = self::get_object();
		if ( empty( $et_client ) ) {
			return false;
		}

		$value                    = sanitize_title_with_dashes( $value );
		$delete_content           = new ET_ContentArea();
		$delete_content->authStub = $et_client;
		$delete_content->props    = array( $type => $value );
		$result                   = $delete_content->delete();

		return $result->status;
	}

	public static function create_senddefinition( $name, $email_id, $list_id, $customer_key = 'Default Commercial' ) {

		$et_client = self::get_object();
		if ( empty( $et_client ) ) {
			return null;
		}

		$post_senddefinition                              = new ET_Email_SendDefinition();
		$post_senddefinition->authStub                    = $et_client;
		$post_senddefinition->props                       = array( 'Name' => $name );
		$post_senddefinition->props['CustomerKey']        = $name;
		$post_senddefinition->props['Description']        = 'Created with PHPSDK';
		$post_senddefinition->props['SendClassification'] = array( 'CustomerKey' => $customer_key );

		$post_senddefinition->props['SendDefinitionList'] = array(
			'List'             => array( 'ID' => $list_id ),
			'DataSourceTypeID' => 'List',
		);

		$post_senddefinition->props['Email'] = array( 'ID' => $email_id );
		$post_result                         = $post_senddefinition->post();

		return self::format_response( $post_result );

	}

	public static function send_senddefinition( $email_id, $dataextension, $customer_key = 'PMC' ) {

		try {

			$et_client = self::get_object();
			if ( empty( $et_client ) ) {
				return null;
			}

			# Call SendEmailToList
			$response = $et_client->SendEmailToDataExtension( $email_id, $dataextension, $customer_key );

			return $response;

		} catch ( Exception $e ) {
			return 'Caught exception: ' . $e->getMessage();
		}
	}

	public static function send_senddefinition_to_list( $email_id, $list_id, $customer_key = 'PMC' ) {

		try {

			$et_client = self::get_object();
			if ( empty( $et_client ) ) {
				return array( 'error' => 'ET Client not initialized!' );
			}

			# Call SendEmailToList
			$response = $et_client->SendEmailToList( $email_id, $list_id, $customer_key );

			if ( ! $response->status ) {

				// This case is almost impossible to occure, $et_client->SendEmailToList throws an exception if $response->status is false,
				// Keeping this check for code completion and ensuring the output of the function always remain the same.
				return array( 'error' => $response ); // @codeCoverageIgnore
			}

			return array( 'status' => $response );

		} catch ( Exception $e ) {
			return array( 'error' => 'Caught exception: ' . $e->getMessage() );
		}
	}

	/**
	 * @param      $email_to     The email address to send email to
	 * @param      $customer_key The triggered send external key
	 * @param bool $attributes   The key/value pair corresponding to the data extension field
	 *
	 * @return bool
	 */
	public static function trigger_send( $email_to, $customer_key = 'PMC', $attributes = false ) {

		try {

			$et_client = self::get_object();
			if ( empty( $et_client ) ) {
				return null;
			}

			$et_ts           = new ET_TriggeredSend();
			$et_ts->props    = array( 'CustomerKey' => $customer_key );
			$et_ts->authStub = $et_client;
			$et_subscribers  = [
				'EmailAddress'  => $email_to,
				'SubscriberKey' => $email_to,
			];
			if ( is_array( $attributes ) ) {
				$et_subscribers['Attributes'] = [];
				foreach ( $attributes as $name => $value ) {
					$et_subscribers['Attributes'][] = [
						'Name'  => $name,
						'Value' => $value,
					];
				}
			}
			$et_ts->subscribers = $et_subscribers;
			$response           = $et_ts->send();

			if ( false === $response->status ) {
				if ( ! empty( $response->results[0] ) ) {
					$error = $response->results[0]->StatusMessage;
				} else {
					$error = $response;
				}

				return array( 'error' => $error );
			}

			return true;
		} catch ( Exception $e ) {
			return 'Caught exception: ' . $e->getMessage();
		}
	}

	public static function get_sends( $filter_date = '', $operator = 'greaterThan' ) {

		$et_client = self::get_object();
		if ( empty( $et_client ) ) {
			return null;
		}

		$get_send           = new ET_Send();
		$get_send->authStub = $et_client;
		$get_send->props    = array(
			'ID',
			'PartnerKey',
			'CreatedDate',
			'ModifiedDate',
			'Client.ID',
			'Client.PartnerClientKey',
			'Email.ID',
			'Email.PartnerKey',
			'SendDate',
			'FromAddress',
			'FromName',
			'Duplicates',
			'InvalidAddresses',
			'ExistingUndeliverables',
			'ExistingUnsubscribes',
			'HardBounces',
			'SoftBounces',
			'OtherBounces',
			'ForwardedEmails',
			'UniqueClicks',
			'UniqueOpens',
			'NumberSent',
			'NumberDelivered',
			'NumberTargeted',
			'NumberErrored',
			'NumberExcluded',
			'Unsubscribes',
			'MissingAddresses',
			'Subject',
			'PreviewURL',
			'SentDate',
			'EmailName',
			'Status',
			'IsMultipart',
			'SendLimit',
			'SendWindowOpen',
			'SendWindowClose',
			'IsAlwaysOn',
			'Additional',
			'BCCEmail',
			'EmailSendDefinition.ObjectID',
			'EmailSendDefinition.CustomerKey',
		);

		if ( ! empty( $filter_date ) ) {
			$get_send->filter = array(
				'Property'       => 'SendDate',
				'SimpleOperator' => 'greaterThan',
				'DateValue'      => $filter_date,
			);
		}

		$get_response = $get_send->get();

		return $get_response;

	}

	/**
	 * Sends a single email immediately. Temporarily creates an email and a senddefinition to send the email and deletes both once email is sent.
	 * Note that the return values needs to be kept in the same format, this is to keep legacy code consistent
	 * and to make sure we don't bring many changes to existing code.
	 *
	 * @param array $data An array with below described email format, it's used to create a tmeporary email in ET.
	 * array(
	 *    'email_name' => 'Name of the email being sent',
	 *    'subject'    => 'Email subject',
	 *    'template'   => 'Content of the email',
	 * )
	 * @param string $dataextension       Sendable data extension customer key.
	 * @param string $send_classification Send classification customer key.
	 *
	 * @return array
	 */
	public static function send_fast_newsletter_from_content_builder( $data, $dataextension, $send_classification ) {

		$return = [];

		if ( ! is_array( $data ) || empty( $dataextension ) || empty( $send_classification ) ) {
			$return['error'][] = 'One or more required parameters are missing';
			return $return;
		}

		// Default array args for $data parameter.
		$defaults = [
			'email_name',
			'subject',
			'template',
		];

		foreach ( $defaults as $key ) {

			if ( empty( $data[ $key ] ) ) {

				$return['error'][] = 'Empty value for:' . $key;
			}
		}

		if ( ! empty( $return['error'] ) ) {
			return $return;
		}

		$name = time() . '-' . $data['email_name'];

		/**
		 * CDWE-136 append 'CSL' in newsletter name.
		 * To compare the engagement of alerts with CSLs vs alerts with no CSLs.
		 */
		if ( ! empty( $data['custom_subject'] ) && true === $data['custom_subject'] ) {
			$name .= '-CSL';
		}

		$et_email = self::create_email_in_content_builder( $name, $data['subject'], $data['template'] );

		if ( empty( $et_email ) || empty( $et_email->id ) ) {
			$return['error'][] = 'Email Duplication failed. Please Try Again';
		}

		if ( empty( $et_email->data->email->legacy->legacyId ) ) {
			$return['error'][] = 'Email Legacy ID not found!';
		} else {
			$email_legacy_id = $et_email->data->email->legacy->legacyId;
		}

		if ( ! empty( $return['error'] ) ) {
			return $return;
		}

		$send = self::send_senddefinition( $email_legacy_id, $dataextension, $send_classification );

		if (
			! is_object( $send )
			|| ! is_array( $send->results )
			|| empty( $send->results[0]->StatusCode )
			|| 'OK' !== $send->results[0]->StatusCode ) {
			$return['error'][] = 'Email send failed. Please Try Again Error: ' . wp_json_encode( $send );
		}

		self::delete_email_from_content_builder( $et_email->id );

		$return['status'] = $send;

		return $return;

	}

	public static function send_fast_newsletter( $data, $dataextension, $send_classification ) {

		$defaults = array(
			'email_name' => '',
			'subject'    => '',
			'template'   => '',
		);

		$data = wp_parse_args( $data, $defaults );

		$return = array();

		foreach ( $defaults as $key => $value ) {
			if ( empty( $data[ $key ] ) ) {
				$return['error'][] = 'Empty value for:' . $key;
			}
		}

		if ( ! empty( $return['error'] ) ) {
			return;
		}

		$name = time() . '-' . $data['email_name'];

		/**
		 * CDWE-136 append 'CSL' in newsletter name.
		 * To compare the engagement of alerts with CSLs vs alerts with no CSLs.
		 */
		if ( ! empty( $data['custom_subject'] ) && true === $data['custom_subject'] ) {
			$name .= '-CSL';
		}

		$et_email = self::create_email( $name, $data['subject'], $data['template'] );

		if ( 'OK' !== $et_email[0]->StatusCode || empty( $et_email[0]->NewID ) ) {
			$return['error'][] = 'Email Duplication failed. Please Try Again';
		}

		if ( ! empty( $return['error'] ) ) {
			return $return;
		}

		$send = self::send_senddefinition( $et_email[0]->NewID, $dataextension, $send_classification );

		if ( ! is_object( $send ) || 'OK' !== $send->results[0]->StatusCode ) {
			$return['error'][] = 'Email send failed. Please Try Again';
		}

		self::delete_email( $et_email[0]->NewID, 'ID' );

		$return['status'] = $send;

		return $return;

	}

	public static function send_recurring_newsletter( $email_id, $dataextension, $send_classification ) {

		$send = self::send_senddefinition( $email_id, $dataextension, $send_classification );

		if ( 'OK' !== $send->results[0]->StatusCode ) {
			$return['error'] = 'Email send failed. Please Try Again';
		}

		$return['status'] = $send;

		return $return;

	}

	public static function send_test_newsletter( $email_id, $test_email, $send_classification ) {

		$et_client = self::get_object();
		if ( empty( $et_client ) ) {
			return false;
		}

		try {

			$list_name = 'PMC-SDKListSubscriber';

			$new_list_id = self::create_test_list( $list_name );

			// Create Subscriber on List
			$sub_create           = new ET_Subscriber();
			$sub_create->authStub = $et_client;
			$sub_create->props    = array(
				'EmailAddress'  => $test_email,
				'Lists'         => array( 'ID' => $new_list_id ),
				'SubscriberKey' => $test_email . '-dev',
			);

			$post_result = $sub_create->post();

			if ( ! $post_result->status ) {
				// If the subscriber already exists in the account then we need to do an update.
				// Update Subscriber On List
				if ( '12014' === (string) $post_result->results[0]->ErrorCode ) {
					// Update Subscriber to add to List
					$sub_patch           = new ET_Subscriber();
					$sub_patch->authStub = $et_client;
					$sub_patch->props    = array(
						'EmailAddress'  => $test_email,
						'Lists'         => array( 'ID' => $new_list_id ),
						'Status'        => 'Active',
						'SubscriberKey' => $test_email . '-dev',
					);

					$patch = $sub_patch->patch();
				}
			}

			$result = self::send_senddefinition_to_list( $email_id, $new_list_id, $send_classification );

			return $result;

		} catch ( Exception $e ) {
			if ( ! empty( $result ) ) {
				return $result;
			}
		}

	}

	public static function create_test_list( $list_name ) {

		$et_client = self::get_object();
		if ( empty( $et_client ) ) {
			return false;
		}

		$get_email_list           = new ET_List();
		$get_email_list->authStub = $et_client;
		$get_email_list->filter   = array(
			'Property'       => 'ListName',
			'SimpleOperator' => 'equals',
			'Value'          => $list_name,
		);
		$get_email_list->props    = array(
			'ID',
			'PartnerKey',
			'CreatedDate',
			'ModifiedDate',
			'Client.ID',
			'Client.PartnerClientKey',
			'ListName',
			'Description',
			'Category',
			'Type',
			'CustomerKey',
			'ListClassification',
			'AutomatedEmail.ID',
		);

		$response = $get_email_list->get();

		if ( ! $response->status || empty( $response->results[0]->ID ) ) {

			// Create List
			$post_content           = new ET_List();
			$post_content->authStub = $et_client;
			$post_content->props    = array(
				'ListName'    => $list_name,
				'Description' => 'This list was created with the PHPSDK',
				'Type'        => 'Private',
			);
			$post_response          = $post_content->post();

			$list_id = $post_response->results[0]->NewID;
		} else {
			$list_id = $response->results[0]->ID;
		}

		return $list_id;
	}

	public static function create_xml_source( $name, $url ) {

		$et_client = self::get_object();
		if ( empty( $et_client ) ) {
			return null;
		}

		$name = sanitize_title_with_dashes( $name );

		$content_area           = new ET_ContentArea();
		$content_area->authStub = $et_client;

		$content_area->props = array(
			'CustomerKey' => $name,
			'Name'        => $name,
			'Content'     => '%%before; httpget; 1 "' . esc_url_raw( $url ) . '"%%',
			'Layout'      => 'RawText',
		);

		$response = $content_area->post();

		return self::get_results( $response );

	}

	/**
	 * Creates a Content Block in Content Builder.
	 *
	 * @param string $name Block name.
	 * @param string $url  URL to update in the block.
	 *
	 * @return Rest_Request|Rest_Error
	 */
	public static function create_xml_source_in_content_builder( $name, $url ) {

		if ( empty( $name ) || empty( $url ) ) {
			return new Rest_Error( 'Required parameters are missing!' );
		}

		$name = sanitize_title_with_dashes( $name );

		$default_folder_id = static::get_default_content_block_id();

		if ( false === $default_folder_id ) {
			return new Rest_Error( 'Something went wrong while fetching default Category (Folder) ID for storing content block!' );
		}

		// @TODO: replace feed url and inject the XML data generated during the ET send. See PMC\Exacttarget\RSS:render function.
		$content  = '%%before; httpget; 1 "' . esc_url_raw( $url ) . '"%%';
		$block    = new Content_Block_Rest();
		$args     = [
			'name'        => $name,
			'content'     => $content,
			'category_id' => $default_folder_id,
		];
		$response = $block->create( $args );

		return $response;
	}

	/**
	 * Helper function to get the ID of default Content Block folder.
	 *
	 * @return int|bool
	 */
	public static function get_default_content_block_id() {

		$folders = static::get_folders_from_content_builder();
		$root_id = 0;

		if ( empty( $folders ) ) {
			return false;
		}

		// Get ID for root folder ( 'Content Builder ).
		foreach ( $folders as $folder ) {

			if ( 0 === $folder->parentId && 'content builder' === strtolower( $folder->name ) ) {
				$root_id = $folder->id;
				break;
			}
		}

		if ( 0 !== $root_id ) {

			foreach ( $folders as $folder ) {

				if ( $folder->parentId === $root_id && strtolower( $folder->name ) === static::$_default_content_block_folder ) {
					return $folder->id;
				}
			}
		}

		return false;
	}

	public static function update_xml_source( $name, $url ) {

		$et_client = self::get_object();
		if ( empty( $et_client ) ) {
			return null;
		}

		$name = sanitize_title_with_dashes( $name );

		$content_area           = new ET_ContentArea();
		$content_area->authStub = $et_client;

		// @TODO: replace feed url and inject the XML data generated during the ET send. See PMC\Exacttarget\RSS:render function.
		$content_area->props = array(
			'CustomerKey' => $name,
			'Name'        => $name,
			'Content'     => '%%before; httpget; 1 "' . esc_url_raw( $url ) . '"%%',
			'Layout'      => 'RawText',
		);

		$response = $content_area->patch();

		return self::get_results( $response );

	}

	/**
	 * Updates a Content Block in Content Builder.
	 *
	 * @param string|int $block_id Block ID.
	 * @param string     $url      URL to update in the block.
	 *
	 * @return Rest_Request|Rest_Error
	 */
	public static function update_xml_source_in_content_builder( $block_id, $url ) {

		if ( empty( $block_id ) || empty( $url ) ) {
			return new Rest_Error( 'Required parameters are missing!' );
		}

		$updated_content = '%%before; httpget; 1 "' . esc_url_raw( $url ) . '"%%';
		$block           = new Content_Block_Rest();
		$response        = $block->update( $block_id, $updated_content );

		return $response;
	}

	public static function upsert_xml_source( $name, $url ) {

		$name = sanitize_title_with_dashes( $name );

		$source = self::get_content_area( $name );
		if ( ! empty( $source ) ) {
			$et_source = self::update_xml_source( $name, $url );
		} else {
			$et_source = self::create_xml_source( $name, $url );
		}

		return $et_source;

		return $et_source;

	}

	/**
	 * Upserts XML Feed URL to a Content Block.
	 *
	 * @param string $name Name of the Content Block to upsert.
	 * @param string $url  Feed URL.
	 *
	 * @return Rest_Request|Rest_Error
	 */
	public static function upsert_xml_source_to_content_builder( $name, $url ) {

		$block = self::get_content_block_from_content_builder( $name );

		if ( $block->status ) {
			$response = self::update_xml_source_in_content_builder( $block->results->id, $url );
		} else {
			$response = self::create_xml_source_in_content_builder( $name, $url );
		}

		return $response;
	}

	/**
	 * Gets Content Block based on name.
	 *
	 * @param string $name Name of the Content Block to upsert.
	 *
	 * @return Rest_Request|Rest_Error
	 */
	public static function get_content_block_from_content_builder( $name ) {

		$name = sanitize_title_with_dashes( $name );

		if ( empty( $name ) ) {
			return new Rest_Error( 'Missing name parameter' );
		}

		$block    = new Content_Block_Rest();
		$response = $block->get_block_by_name( $name );

		return $response;
	}

	public static function get_content_area( $name ) {

		$et_client = self::get_object();
		if ( empty( $et_client ) ) {
			return [];
		}

		$get_content           = new ET_ContentArea();
		$get_content->authStub = $et_client;
		$get_content->filter   = array(
			'Property'       => 'CustomerKey',
			'SimpleOperator' => 'equals',
			'Value'          => $name,
		);
		$get_content->props    = array(
			'RowObjectID',
			'ObjectID',
			'ID',
			'CustomerKey',
			'Client.ID',
			'ModifiedDate',
			'CreatedDate',
			'CategoryID',
			'Name',
			'Layout',
			'IsDynamicContent',
			'Content',
			'IsSurvey',
			'IsBlank',
			'Key',
		);

		$get_response = $get_content->get();

		$results = self::get_results( $get_response );

		while ( $get_response->moreResults ) {
			$get_response = $get_content->GetMoreResults();
			$results      = array_merge( $results, self::get_results( $get_response ) );
		}

		$content_array = array();
		foreach ( $results as $result ) {
			$content_array[ $result->ID ] = $result->Name;
		}

		return $content_array;
	}

	public static function format_response( $post_result ) {

		$response  = 'Status: ' . ( $post_result->status ? 'true' : 'false' ) . '<br/>';
		$response .= 'Code: ' . $post_result->code . '<br/>';
		$response .= 'Message: ' . $post_result->message . '<br/>';
		$response .= 'Results Length: ' . count( $post_result->results ) . '<br/>';
		$response .= 'Results: ' . '<br/>';
		$response .= json_encode( $post_result->results ); // phpcs:ignore --- This library is use by non-wp project

		return $response;
	}

	public static function get_results( $response ) {
		if ( empty( $response->results ) ) {
			return []; // return empty result, null may cause other function to throw errors
		}

		return $response->results;

	}

	/**
	 * @since 2015-11-01 Amit Sannad Added functionality to save data in pmc logs table rather then sending emails on each error. If there are continuous 2 errors then only send email.
	 */
	public static function log() {

		if ( ! empty( self::$_error ) ) {
			$to_list = array(
				'asannad@pmc.com',
				'cyeoh@pmc.com',
				'ncatton@pmc.com',
				'dramsay@pmc.com',
				'tyler.stewart@pmc.com',
				'drochester@pmc.com',
				'dist.dev@pmc.com'
			);

			//Check if we have already saved error before & then only send email to alert people
			$errored_already = wp_cache_get( 'error', 'pmc-exacttarget' );

			if ( ! empty( $errored_already ) ) {

				wp_cache_delete( 'error', 'pmc-exacttarget' );
				wp_mail( $to_list, 'Exact Target Error', $errored_already . self::$_error );

			} else {

				wp_cache_add( 'error', self::$_error, 'pmc-exacttarget' );

				$data = array(
					'site'  => get_bloginfo( 'name' ),
					'error' => $errored_already . ' ' . self::$_error
				);

				//send log data
				wp_remote_post( 'https://pistachio.pmc.com/pmc-exacttarget-logger.php', array(
					'method'      => 'POST',
					'timeout'     => 2,
					'redirection' => 5,
					'httpversion' => '1.0',
					'blocking'    => false,
					'headers'     => array(),
					'body'        => $data,
					'cookies'     => array(),
					'user-agent'  => $_SERVER['HTTP_USER_AGENT']
				) );
			}

		}
	}

	/**
	 * Add new row in data extensions
	 */
	public static function create_new_data_extension_row( $data_extension_customer_key, $subscriber_detail ) {

		$et_client = self::get_object();

		if ( empty( $et_client ) ) {
			return null;
		}

		$data_extension_customer_key = sanitize_text_field( $data_extension_customer_key );
		$subscriber_detail           = array_map( 'sanitize_text_field', wp_unslash( $subscriber_detail ) );

		try {
			$dataextensionrow              = new ET_DataExtension_Row();
			$dataextensionrow->authStub    = $et_client;
			$dataextensionrow->CustomerKey = $data_extension_customer_key;

			/*********** Post Row Starts here ***********************/
			$dataextensionrow->props = $subscriber_detail;
			$response                = $dataextensionrow->post();
			/*********** Post Row Ends here ***********************/

			return $response;
		} catch ( Exception $e ) {
			return 'Caught exception: ' . $e->getMessage();
		}
	}

	/**
	 * Update row status in data extension
	 */
	public static function update_data_extension_row( $data_extension_customer_key, $field_name_values ) {

		$et_client = self::get_object();
		if ( empty( $et_client ) ) {
			return null;
		}

		$data_extension_customer_key = sanitize_text_field( $data_extension_customer_key );

		try {
			$data_extension_row              = new ET_DataExtension_Row();
			$data_extension_row->authStub    = $et_client;
			$data_extension_row->CustomerKey = $data_extension_customer_key;

			/*********** Update Row Starts here ***********************/
			$data_extension_row->props = $field_name_values;
			$response                  = $data_extension_row->patch();
			/*********** Update Row Ends here ***********************/

			return $response;
		} catch ( Exception $e ) {
			return 'Caught exception: ' . $e->getMessage();
		}
	}

}

//EOF
