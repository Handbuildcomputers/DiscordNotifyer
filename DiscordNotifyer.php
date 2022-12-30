<?php
// Prefend from module gets executed from outside PS
if (!defined("_PS_VERSION_")) {
	exit;
}

// Main class, gets called when module hets loaded
class DiscordNotifyer extends Module {
	public function __construct() {
		$this->name = "DiscordNotifyer";
		$this->tab = "checkout";
		$this->version = "1.0.0";
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
		return parent::install() && $this->registerHook("actionEmailSendBefore");
	}
	
	// Gets called when module gets uninstalled
	public function uninstall() {
		if (
			!parent::uninstall()
		) {
			return false;
		} else {
			return true;
		}
	}

	// Configuration code
	public function getContent()
	{
		$output = "";

		// When forum gets submitted
		if (Tools::isSubmit("submit" . $this->name)) {
			// Get webhook url
			$configValue = (string) Tools::getValue("WEBHOOK_URL");

			// check that the value is valid
			if (empty($configValue) || !Validate::isGenericName($configValue)) {
				// invalid value, show an error
				$output = $this->displayError($this->l("Invalid Configuration value"));
			} else {
				// value is ok, update it and display a confirmation message
				Configuration::updateValue("WEBHOOK_URL", $configValue);
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
					[
						"type" => "text",
						"label" => $this->l("Discord webhook url"),
						"name" => "WEBHOOK_URL",
						"size" => 20,
						"required" => true,
					],
				],
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

		// Load current value into the form
		$helper->fields_value["WEBHOOK_URL"] = Tools::getValue("WEBHOOK_URL", Configuration::get("WEBHOOK_URL"));

		return $helper->generateForm([$form]);
	}


	// Mail hook trigger
	public function hookactionEmailSendBefore($param) {
		// Getting type of mail that will be send
		switch($param["template"]){
			case "contact_form":
				$type_mail = "Er is een contact form ingediend!";
				break;
			case "account":
				$type_mail = "Er is een account aangemaakt in de webstore!";
				break;
			case "order_conf":
				$type_mail = "Er is een bevestigde order binnengekomen!";
				break;
			case "payment":
				$type_mail = "Er is een betaling verwerkt in de webstore!";
				break;
			case "test":
				$type_mail = "Er is een testmail verstuurd vanuit de backoffice.";
				break;			
		}


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

}