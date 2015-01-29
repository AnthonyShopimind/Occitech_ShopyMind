# ShopyMind extension for Magento

Abandoned cart recovery and email marketing automation. This extension is the official integration of the [ShopyMind](http://www.shopymind.com/) service with Magento.

## Builds statuses

* Latest release: [![Master Branch](https://travis-ci.org/occitech/Occitech_ShopyMind.png?branch=master)](https://travis-ci.org/occitech/Occitech_ShopyMind) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/occitech/Occitech_ShopyMind/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/occitech/Occitech_ShopyMind/?branch=master)
* Development branch: [![Master Branch](https://travis-ci.org/occitech/Occitech_ShopyMind.png?branch=develop)](https://travis-ci.org/occitech/Occitech_ShopyMind) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/occitech/Occitech_ShopyMind/badges/quality-score.png?b=develop)](https://scrutinizer-ci.com/g/occitech/Occitech_ShopyMind/?branch=develop)

## Installation

### Via modman

* Install [modman](https://github.com/colinmollenhour/modman)
* Use the command from your Magento installation folder: `modman clone https://github.com/occitech/Occitech_ShopyMind`
* Please make sure that the setting "Allow Symlinks" in System Configuration under *Developer -> Template Settings* is set to "YES".

### Via composer

* Install [composer](http://getcomposer.org/download/)
* Install [Magento Composer](https://github.com/magento-hackathon/magento-composer-installer)
* Create a composer.json into your project like the following sample (we recommend defining the `occitech/shopymind` version more explicitely though):

```json
{
    ...
    "require": {
        "occitech/shopymind":"*"
    },
    "repositories": [
        {
            "type": "composer",
            "url": "http://packages.firegento.com"
        }
    ],
    "extra":{
        "magento-root-dir": "./"
    }
}
```

* Then from your `composer.json` folder: `php composer.phar install` or `composer install`

### Manually

Copy the content of the `src/` directory in your project root directory.

## Documentation

See the [docs/](docs/) directory for a living documentation.

## Contributing

You detected an issue? Would like to suggest an improvement? This is awesome!

Please read the [guidelines for contributing](CONTRIBUTING.md)

## License

New BSD License. See [LICENSE](LICENSE.md)

## Get in touch

For any other questions here is where to go:

* ShopyMind related questions: contact the [ShopyMind product team](http://www.shopymind.com/contactez-nous/), or [@shopymind](https://twitter.com/shopymind)
* Magento integration: contact the [Occitech team](http://www.occitech.fr/contact/), or [@occitech](https://twitter.com/occitech)
