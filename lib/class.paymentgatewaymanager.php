<?php

	require_once(EXTENSIONS . '/pgi_loader/lib/class.paymentgateway.php');
	require_once(TOOLKIT . '/class.manager.php');

	/**
	 * A manager to standardize the finding and listing of installed gateways.
	 */
	Class PaymentGatewayManager extends Manager {

		public function __construct() {}

		/**
		 * Sets the default gateway.
		 * Will throw an exception if the gateway can not be found.
		 *
		 * @param string $name
		 * @return void
		 */
		public function setDefaultGateway($name){
			if($this->__getClassPath($name)){
				Symphony::Configuration()->set('default_gateway', $name, 'pgi_loader');
				Administration::instance()->saveConfig();
			}
			else throw new PaymentGatewayException(
				__('This gateway can not be found. Can not save as default.')
			);
		}

		/**
		 * Returns the default gateway.
		 * Will throw an exception if the gateway can not be found.
		 *
		 * @return string
		 */
		public function getDefaultGateway(){
			$gateway = Symphony::Configuration()->get('default_gateway', 'pgi_loader');
			if($gateway) {
				return $gateway;
			}
			else throw new PaymentGatewayException(
				__('There is no default gateway found.')
			);
		}

		/**
		 * Returns the classname from the gateway name.
		 * Does not check if the gateway exists.
		 *
		 * @param string $name
		 * @return string
		 */
		public function __getClassName($name){
			return $name . 'PaymentGateway';
		}

		/**
		 * Finds the gateway by name
		 *
		 * @param string $name
		 * 	The gateway to look for
		 * @return string|boolean
		 *	If the gateway is found, the path to the folder containing the
		 *  gateway is returned.
		 *	If the gateway is not found, false is returned.
		 */
		public function __getClassPath($name){
			$extensions = Symphony::ExtensionManager()->listInstalledHandles();

			if(is_array($extensions) && !empty($extensions)){
				foreach($extensions as $e) {
					if(is_file(EXTENSIONS . "/$e/payment-gateways/pgi.$name.php")) {
						return EXTENSIONS . "/$e/payment-gateways";
					}
				}
			}

			return false;
		}

		/**
		 * Returns the path to the gateway file.
		 *
		 * @param string $name
		 * 	The gateway to look for
		 * @return string|boolean
		 * @todo fix return if gateway does not exist.
		 */
		public function __getDriverPath($name){
			return $this->__getClassPath($name) . "/pgi.$name.php";
		}

		/**
		 * Finds the name from the filename.
		 * Does not check if the gateway exists.
		 *
		 * @param string $filename
		 * @return string|boolean
		 */
		public function __getHandleFromFilename($filename){
			return preg_replace(array('/^pgi./i', '/.php$/i'), '', $filename);
		}

		/**
		 * Returns an array of all gateways.
		 * Each item in the array will contain the return value of the about()
		 * function of each gateway.
		 *
		 * @return array
		 */
		public function listAll(){
			$result = array();

			$extensions = Symphony::ExtensionManager()->listInstalledHandles();

			if(is_array($extensions) && !empty($extensions)){
				foreach($extensions as $e){
					if(!is_dir(EXTENSIONS . "/$e/payment-gateways")) continue;

					$tmp = General::listStructure(EXTENSIONS . "/$e/payment-gateways", '/pgi.[\\w-]+.php/', false, 'ASC', EXTENSIONS . "/$e/payment-gateways");

					if(is_array($tmp['filelist']) && !empty($tmp['filelist'])){
						foreach($tmp['filelist'] as $f){
							$f = preg_replace(array('/^pgi./i', '/.php$/i'), '', $f);
							$result[$f] = $this->about($f);
						}
					}
				}
			}

			ksort($result);
			return $result;
		}

		/**
		 * Creates a new object from a gateway name.
		 *
		 * @param string $name
		 * 	The gateway to look for
		 * @return PaymentGateway
		 *	If the gateway is found, an instantiated object is returned.
		 *	If the gateway is not found, an error is triggered.
		 */
		public function &create($name) {
			$classname = $this->__getClassName($name);
			$path = $this->__getDriverPath($name);

			if(!is_file($path)){
				trigger_error(__('Could not find Payment Gateway <code>%s</code>. Ensure that it is installed, and enabled.', array($name)), E_USER_ERROR);
				return false;
			}

			if(!class_exists($classname)) {
				require_once($path);
			}

			return new $classname;
		}

	}
