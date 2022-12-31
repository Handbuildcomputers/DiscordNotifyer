<?php
// Prefend from module gets executed from outside PS
if (!defined("_PS_VERSION_")) {
	exit;
}


// Main class, gets called when module Gets loaded
class DiscordNotifyer extends Module {
	// Gets called when module gets loaded
	public function __construct() {
		$this->name = "DiscordNotifyer";
		$this->tab = "checkout";
		$this->version = "2.1.0";
		$this->author = "Kelvin de Reus";
		$this->need_instance = 0;
		// Checks compatiblity
		$this->ps_versions_compliancy = array("min" => "1.6", "max" => "1.8.99.99");
		$this->bootstrap = true;
		// Parent contructor
		parent::__construct();
		// Name & description for in module catalogus
		$this->displayName = $this->l("Discord notifyer");
		$this->description = $this->l("Sends notification to Discord on order, contact, payment, order confirmation and backoffice test mail");

		$this->confirmUninstall = $this->l("Are you sure you want to uninstall?");
	}
	

	// Gets called when module gets installed
	public function install()
	{	
		// Register actionEmailSendBefore hook
		return parent::install()
		&& $this->registerHook("actionEmailSendBefore");
	}
	

	// Gets called when module gets uninstalled
	public function uninstall()
	{
		return (

			parent::uninstall() &&
			// Deletes webhook and langauge values and switches
			Configuration::deleteByName("WEBHOOK_URL") && 
			Configuration::deleteByName("LANGUAGE_VALUE") && 
			Configuration::deleteByName("SWITCH_CONTACT_FORM") &&
			Configuration::deleteByName("SWITCH_ACCOUNT_CREATION") && 
			Configuration::deleteByName("SWITCH_ORDER_CONF") && 
			Configuration::deleteByName("SWITCH_PAYMENT") && 
			Configuration::deleteByName("SWITCH_TEST")

		);
	}


	// Configuration code
	public function getContent()
	{	
		// Idfk why this is here but just don't touch it lol
		$output = "";

		// When forum gets submitted
		if (Tools::isSubmit("submit" . $this->name)) {
			// Get values from config form
			$configValue = (string) Tools::getValue("WEBHOOK_URL");
			$languageModule = (string) Tools::getValue("LANGUAGE_VALUE");
			$switchContactForm = (string) Tools::getValue("SWITCH_CONTACT_FORM");
			$switchAccount = (string) Tools::getValue("SWITCH_ACCOUNT_CREATION");
			$switchOrderConf = (string) Tools::getValue("SWITCH_ORDER_CONF");
			$switchPayment = (string) Tools::getValue("SWITCH_PAYMENT");
			$switchTest = (string) Tools::getValue("SWITCH_TEST");

			// check that the value is valid
			if (empty($configValue) || !Validate::isGenericName($configValue)) {
				// invalid value, show an error
				$output = $this->displayError($this->l("Invalid Configuration value"));
			} else {
				// Sets setting webhook url
				Configuration::updateValue("WEBHOOK_URL", $configValue);
				// Sets setting language
				Configuration::updateValue("LANGUAGE", $languageModule);	
				// Sets setting switches
				Configuration::updateValue("SWITCH_CONTACT_FORM", $switchContactForm);	
				Configuration::updateValue("SWITCH_ACCOUNT_CREATION", $switchAccount);	
				Configuration::updateValue("SWITCH_ORDER_CONF", $switchOrderConf);	
				Configuration::updateValue("SWITCH_PAYMENT", $switchPayment);	
				Configuration::updateValue("SWITCH_TEST", $switchTest);	

				$output = $this->displayConfirmation($this->l("Settings updated"));

			}

		}

		// display any message, then the form
		return $output . $this->displayForm();
	}


	// Making the form 
	public function displayForm()
	{
		// Init Fields form array
		$form = [
			"form" => [
				"legend" => [
					"title" => $this->l("Settings"),
				],
				"input" => [
					// Creating input text for webhook url
					[
						"type" => "text",
						"label" => $this->l("Discord webhook url"),
						"name" => "WEBHOOK_URL",
						"size" => 20,
						"required" => true,

					],
					// Creating sselector for language
					[
						"type" => "select",
						"label" => $this->l("Language"),
						"name" => "LANGUAGE_VALUE",
						"multiple" => false,
						"required" => true,
						"options" => array(
							"query" => array(
								array("key" => "English", "name" => "English"),
								array("key" => "Dutch", "name" => "Dutch"),
							),
							"id" => "key",
							"name" => "name"
						)
					],					
					// Creating switch for contact form notification
					[
						"type" => "switch",
						"label" => $this->l("Contact form notification"),
						"name" => "SWITCH_CONTACT_FORM",
						"class" => "fixed-width-xs",
						"values" => [
							[
								"id" => "active_on",
								"value" => "on",
								"label" => $this->trans("Yes"),
							],
							[
								"id" => "active_off",
								"value" => "off",
								"label" => $this->trans("No"),
							],
						],
					],	
					// Creating switch for account creation notification
					[
						"type" => "switch",
						"label" => $this->l("Account creation notification"),
						"name" => "SWITCH_ACCOUNT_CREATION",
						"class" => "fixed-width-xs",
						"values" => [
							[
								"id" => "active_on",
								"value" => "on",
								"label" => $this->trans("Yes"),
							],
							[
								"id" => "active_off",
								"value" => "off",
								"label" => $this->trans("No"),
							],
						],
					],			
					// Creating switch for account creation notification
					[
						"type" => "switch",
						"label" => $this->l("Order confirmed notification"),
						"name" => "SWITCH_ORDER_CONF",
						"class" => "fixed-width-xs",
						"values" => [
							[
								"id" => "active_on",
								"value" => "on",
								"label" => $this->trans("Yes"),
							],
							[
								"id" => "active_off",
								"value" => "off",
								"label" => $this->trans("No"),
							],
						],
					],	
					// Creating switch for account creation notification
					[
						"type" => "switch",
						"label" => $this->l("Payment notification"),
						"name" => "SWITCH_PAYMENT",
						"class" => "fixed-width-xs",
						"values" => [
							[
								"id" => "active_on",
								"value" => "on",
								"label" => $this->trans("Yes"),
							],
							[
								"id" => "active_off",
								"value" => "off",
								"label" => $this->trans("No"),
							],
						],
					],	
					// Creating switch for account creation notification
					[
						"type" => "switch",
						"label" => $this->l("Test mail notification"),
						"name" => "SWITCH_TEST",
						"class" => "fixed-width-xs",
						"values" => [
							[
								"id" => "active_on",
								"value" => "on",
								"label" => $this->trans("Yes"),
							],
							[
								"id" => "active_off",
								"value" => "off",
								"label" => $this->trans("No"),
							],
						],
					],																					
				],
				// Creates submit button
				"submit" => [
					"title" => $this->l("Save"),
					"class" => "btn btn-default pull-right",
				],
			],
		];

		$helper = new HelperForm();

		// Module, token and currentIndex
		$helper->table = $this->table;
		$helper->name_controller = $this->name;
		$helper->token = Tools::getAdminTokenLite("AdminModules");
		$helper->currentIndex = AdminController::$currentIndex . "&" . http_build_query(["configure" => $this->name]);
		$helper->submit_action = "submit" . $this->name;

		// Default language
		$helper->default_form_language = (int) Configuration::get("PS_LANG_DEFAULT");

		// Load current values into the form
		$helper->fields_value["WEBHOOK_URL"] = Configuration::get("WEBHOOK_URL");
		$helper->fields_value["LANGUAGE_VALUE"] = Configuration::get("LANGUAGE");
		$helper->fields_value["SWITCH_CONTACT_FORM"] = Configuration::get("SWITCH_CONTACT_FORM");
		$helper->fields_value["SWITCH_ACCOUNT_CREATION"] = Configuration::get("SWITCH_ACCOUNT_CREATION");
		$helper->fields_value["SWITCH_ORDER_CONF"] = Configuration::get("SWITCH_ORDER_CONF");
		$helper->fields_value["SWITCH_PAYMENT"] = Configuration::get("SWITCH_PAYMENT");
		$helper->fields_value["SWITCH_TEST"] = Configuration::get("SWITCH_TEST");

		// Generates the form
		return $helper->generateForm([$form]);
	}


	// Mail hook trigger function
	public function hookactionEmailSendBefore($param) {

		// Gets language set by the user in the config
		switch(Configuration::get("LANGUAGE")){
			// If set to English, use eng.txt and assign it to $file_lang
			case "English":
				$file_lang = "/home/handbuildcomputers.nl/public_html/modules/DiscordNotifyer/lang/eng.txt";
				break;
			// If set to Dutch, use nl.txt and assign it to $file_lang
			case "Dutch":
				$file_lang = "/home/handbuildcomputers.nl/public_html/modules/DiscordNotifyer/lang/nl.txt";
				break;
		}

		// Opening txt file
		$lines = file($file_lang);

		// Webhook function
		function webhookDiscord($type_mail) {
			// Webhook
			// Setting headers
			$headers = [ "Content-Type: application/json; charset=utf-8" ];
			// Webhook sending content
			$content = [ "username" => "Webstore", "content" => strval($type_mail) ];
						
			// Initialize curl and sending request
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, strval(Configuration::get("WEBHOOK_URL")));
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($content));
			curl_exec($ch);		  
			}


		// Getting type of mail that will be send, and send right text and calls the webhook
		switch($param["template"]){
			case "contact_form" && Configuration::get("SWITCH_CONTACT_FORM") == "on":
				webhookDiscord(strval($lines[0]));
				break;
			case "account" && Configuration::get("SWITCH_ACCOUNT_CREATION") == "on":
				webhookDiscord(strval($lines[0]));
				break;
			case "order_conf" && Configuration::get("SWITCH_ORDER_CONF") == "on":
				webhookDiscord(strval($lines[0]));
				break;
			case "payment" && Configuration::get("SWITCH_PAYMENT") == "on":
				webhookDiscord(strval($lines[0]));
				break;
			case "test" && Configuration::get("SWITCH_TEST") == "on":
				webhookDiscord(strval($lines[0]));
				break;			
				
		}

	}

}