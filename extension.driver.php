<?php

	class Extension_PGI_Loader extends Extension {

		public function about() {
			return array(
				'name' => 'Payment Gateway Loader',
				'version' => '0.1',
				'release-date' => '2011-12-14',
				'author' => array(
					array(
						'name' => 'Brendan Abbott',
						'email' => 'brendan@bloodbone.ws'
					),
				),
				'description' => 'Provides an common interface for Payment Gateway extensions to extend so developers can interact with a number of gateways using the same API'
			);
		}

	/*-------------------------------------------------------------------------
		Definition:
	-------------------------------------------------------------------------*/

		public function getSubscribedDelegates() {
			return array(
				array(
					'page' => '/backend/',
					'delegate' => 'AdminPagePreGenerate',
					'callback' => 'appendAssets'
				),
				array(
					'page'		=> '/system/preferences/',
					'delegate'	=> 'AddCustomPreferenceFieldsets',
					'callback'	=> 'appendPreferences'
				),
				array(
					'page'		=> '/system/preferences/',
					'delegate'	=> 'Save',
					'callback'	=> 'savePreferences'
				)
			);
		}

	/*-------------------------------------------------------------------------
		Delegate Callbacks:
	-------------------------------------------------------------------------*/

		/**
		 * @uses AdminPagePreGenerate
		 */
		public function appendAssets(&$context) {
			if(class_exists('Administration')
				&& Administration::instance() instanceof Administration
				&& Administration::instance()->Page instanceof HTMLPage
			) {
				// System Preferences
				if($context['oPage'] instanceof contentSystemPreferences) {
					Administration::instance()->Page->addScriptToHead(URL . '/extensions/pgi_loader/assets/pgi_loader.preferences.js', 10001, false);
					Administration::instance()->Page->addStylesheetToHead(URL . '/extensions/pgi_loader/assets/pgi_loader.preferences.css', 'screen', 45);
				}
			}
		}

		/**
		 * Allows a user to set their default Payment Gateway for extensions to use
		 *
		 * @uses AddCustomPreferenceFieldsets
		 */
		public function appendPreferences($context) {
			// Get available Payment Gateways
			require_once EXTENSIONS . '/pgi_loader/lib/class.paymentgatewaymanager.php';

			$payment_gateway_manager = new PaymentGatewayManager($this);
			$payment_gateways = $payment_gateway_manager->listAll();
			if(count($payment_gateways) >= 1){
				$fieldset = new XMLElement('fieldset', NULL, array('class' => 'settings pgi-picker'));
				$fieldset->appendChild(new XMLElement('legend', __('Payment Gateway')));
				$label = Widget::Label(__('Gateway'));
				$options = array();

				ksort($payment_gateways);

				// Get the default gateway
				try {
					$default_gateway = $payment_gateway_manager->getDefaultGateway();
				}
				catch (PaymentGatewayException $ex) {
					$default_gateway = false;
				}

				foreach($payment_gateways as $handle => $details) {
					$options[] = array(
						$handle,
						$handle == $default_gateway,
						$details['name']
					);
				}

				$select = Widget::Select('settings[pgi_loader][default_gateway]', $options);
				$label->appendChild($select);
				$fieldset->appendChild($label);

				// Append payment gateway selection
				$context['wrapper']->appendChild($fieldset);
			}

			foreach($payment_gateways as $gateway) {
				$gateway_settings = $payment_gateway_manager->create($gateway['handle'])->getPreferencesPane();

				if(is_a($gateway_settings, 'XMLElement')) {
					$context['wrapper']->appendChild($gateway_settings);
				}
			}
		}

		/**
		 * Saves the Payment Gateway information
		 *
		 * @uses savePreferences
		 */
		public function savePreferences(array &$context){
			$settings = $context['settings'];

			Administration::instance()->saveConfig();
		}

	}
