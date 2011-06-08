<?php

	class Extension_PGI_Loader extends Extension {

		public function about() {
			return array(
				'name' => 'Payment Gateway Loader',
				'version' => '0.1',
				'release-date' => 'unreleased',
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
		 * Allows a user to set their default Payment Gateway for extensions to use
		 *
		 * @uses AddCustomPreferenceFieldsets
		 */
		public function appendPreferences($context) {
			$fieldset = new XMLElement('fieldset');
			$fieldset->setAttribute('class', 'settings');

			$context['wrapper']->appendChild($fieldset);
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
