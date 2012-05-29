<?php

	require_once(EXTENSIONS . '/pgi_loader/lib/class.paymentgateway.php');
	require_once(FACE . '/interface.fileresource.php');

	/**
	 * A manager to standardize the finding and listing of installed gateways.
	 */
	Class PaymentGatewayManager implements FileResource {

		/**
		 * Sets the default gateway.
		 * Will throw an exception if the gateway can not be found.
		 *
		 * @param string $name
		 * @return boolean
		 */
		public static function setDefaultGateway($name){
			if(self::__getClassPath($name)){
				Symphony::Configuration()->set('default_gateway', $name, 'pgi_loader');
				return Symphony::Configuration()->write();
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
		public static function getDefaultGateway(){
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
		public static function __getClassName($name){
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
		public static function __getClassPath($name){
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
		public static function __getDriverPath($name){
			return self::__getClassPath($name) . "/pgi.$name.php";
		}

		/**
		 * Finds the name from the filename.
		 * Does not check if the gateway exists.
		 *
		 * @param string $filename
		 * @return string|boolean
		 */
		public static function __getHandleFromFilename($filename){
			return preg_replace(array('/^pgi./i', '/.php$/i'), '', $filename);
		}

		/**
		 * Returns an array of all gateways.
		 * Each item in the array will contain the return value of the about()
		 * function of each gateway.
		 *
		 * @return array
		 */
		public static function listAll(){
			$result = array();
			$extensions = Symphony::ExtensionManager()->listInstalledHandles();

			if(is_array($extensions) && !empty($extensions)){
				foreach($extensions as $e){
					if(!is_dir(EXTENSIONS . "/$e/payment-gateways")) continue;

					$tmp = General::listStructure(EXTENSIONS . "/$e/payment-gateways", '/pgi.[\\w-]+.php/', false, 'ASC', EXTENSIONS . "/$e/payment-gateways");

					if(is_array($tmp['filelist']) && !empty($tmp['filelist'])){
						foreach($tmp['filelist'] as $f){
							$f = preg_replace(array('/^pgi./i', '/.php$/i'), '', $f);
							$result[$f] = self::about($f);
						}
					}
				}
			}

			ksort($result);
			return $result;
		}

		public static function about($name) {
			$classname = self::__getClassName($name);
			$path = self::__getDriverPath($name);

			if(!@file_exists($path)) return false;

			require_once($path);

			$handle = self::__getHandleFromFilename(basename($path));

			if(is_callable(array($classname, 'about'))){
				$about = call_user_func(array($classname, 'about'));
				return array_merge($about, array('handle' => $handle));
			}
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
		public static function &create($name) {
			$classname = self::__getClassName($name);
			$path = self::__getDriverPath($name);

			if(!is_file($path)){
				throw new PaymentGatewayException(
					__('Could not find Payment Gateway <code>%s</code>. Ensure that it is installed, and enabled.', array($name))
				);
			}

			if(!class_exists($classname)) {
				require_once($path);
			}

			return new $classname;
		}

	}
