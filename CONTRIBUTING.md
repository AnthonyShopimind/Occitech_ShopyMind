# How to contribute

We're really glad you're reading this, because we welcome any help from volunteer developers to help improve this project.

There are several ways to help out:
* Create an [issue](https://github.com/occitech/Occitech_ShopyMind/issues) on GitHub, if you have found a bug
* Write test cases for open bug issues
* Write patches for open bug/feature issues, preferably with test cases included
* Contribute to the [documentation](docs/)

## Getting Started

* Make sure you have a [GitHub account](https://github.com/signup/free).
* Submit an [issue](https://github.com/occitech/Occitech_ShopyMind/issues), assuming one does not already exist.
  * Clearly describe the issue including steps to reproduce when it is a bug along with what you would have expected
* Fork the repository on GitHub.

## Feature requests

Please be careful: this repository is about integrating [ShopyMind](http://www.shopymind.com/) with your Magento installation.
If you would like to suggest new features to the ShopyMind product itself [contact the ShopyMind product team](http://www.shopymind.com/contactez-nous/)

For feature requests related to the Magento integration, you're at home! Feel free to [submit a new issue](https://github.com/occitech/Occitech_ShopyMind/issues)

## Running tests

To run the extension test suite run the `composer run test 1.9.1.0` command from the project root
(where `1.9.1.0` is the Magento version to test against).

## Submitting Changes

* Push your changes to a topic branch in your fork of the repository.
* Ensure the tests are all green
* Submit a pull request to the repository in the cakephp organization, with the
  correct target branch.

## Which branch to base the work

We try to follow the [Git-flow](http://jeffkreeftmeijer.com/2010/why-arent-you-using-git-flow/) branching workflow.

* Bugfix branches will be based on `master`
* New features or improvements will be based on `develop`
