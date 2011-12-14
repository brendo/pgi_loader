# Payment Gateway Interface Loader

- Version: 0.1
- Release Date: 14th December 2011
- Author: Brendan Abbott
- Requirements: Symphony 2.2

Provides an common interface for Payment Gateway extensions to extend so
developers can interact with a number of gateways using the same API

## INSTALLATION

1. Upload the `pgi_loader` folder to your Symphony `/extensions` folder.
2. Enable it by selecting the "PGI Loader", choose Install/Enable from the
With Selected menu, then click Apply.
3. Visit the Preferences page in the Symphony backend to set your default payment gateway.

## ABOUT

This extension aims to provide a common API that developers can use to interact
with Payment Gateways. A `PaymentGateway` abstract class in the `/lib` directory
provides a base skeleton to be expanded upon by developers in their own Payment
Gateway extensions. At the moment this interface describes a single method,
`processTransaction` that expects an associative array of values.

## USAGE

More to come.

## CURRENT GATEWAYS

- [eWay](https://github.com/brendo/eway)
- [SecurePay](https://github.com/brendo/securepay)
