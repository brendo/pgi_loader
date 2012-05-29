<?php

	require_once(EXTENSIONS . '/pgi_loader/lib/class.paymentgatewaymanager.php');
	require_once(EXTENSIONS . '/pgi_loader/lib/interface.paymentgateway.php');

	/**
	 * The Exception to be thrown by a Payment Gateway
	 */
	class PaymentGatewayException extends Exception {}

	/**
	 * The PaymentGateway class is a factory class to interface with different
	 * payment gateways. Payment gateways can be provided by extensions that choose
	 * to implement the `PaymentGateway` interface
	 */
	abstract class PaymentGateway implements iPaymentGateway {

		/**
		 * Returns the PaymentGateway to interact with
		 * Calling this function multiple times will return unique objects.
		 *
		 * @param string $gateway
		 *  The name of the gateway to use. If no `$gateway` is provided this
		 *  will return the Default Gateway set by Symphony.
		 * @return PaymentGateway
		 */
		public static function create($gateway = null){
			if(!is_null($gateway)){
				return PaymentGatewayManager::create($gateway);
			}
			else{
				return PaymentGatewayManager::create(PaymentGatewayManager::getDefaultGateway());
			}
		}

		/**
		 * The preferences to add to the preferences pane in the admin area.
		 *
		 * @return XMLElement
		 */
		public function getPreferencesPane(){
			return new XMLElement('fieldset');
		}
	}

