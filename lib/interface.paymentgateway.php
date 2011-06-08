<?php

	/**
	 * Provides an interface that extensions can utilise so that developers
	 * have a consistent API to interface with.
	 */
	interface iPaymentGateway {

		public static function processTransaction(array $values);

	}