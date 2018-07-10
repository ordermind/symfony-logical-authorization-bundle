[![Build Status](https://travis-ci.org/ordermind/symfony-logical-authorization-bundle.svg?branch=master)](https://travis-ci.org/ordermind/symfony-logical-authorization-bundle)

# Logical Authorization Bundle

This Symfony bundle provides a unifying solution for authorization that aims to be flexible, convenient and consistent. It combines the expressive power of https://github.com/ordermind/logical-permissions-php with the philosophy of Matthias Noback in his blog post https://matthiasnoback.nl/2014/05/inject-a-repository-instead-of-an-entity-manager to create a solid authorization experience for the developer.

- Declare your permissions in the mappings for your routes and entities
- Combine multiple permissions with logic gates such as AND and OR
- Support for routes, Doctrine ORM and Doctrine MongoDB
- Review all of your permissions in a single overview tree
- Filter results from repositories automatically with repository decorators
- Intercept interactions with entities automatically with entity decorators
- Export your permissions for easy synchronization with client-side applications
- Debug each access check with detailed information

## Installation

Requirements: Symfony 4.1 or higher.

**Main bundle**

```
composer require ordermind/logical-authorization-bundle
```

**Support for Doctrine ORM**

```
composer require ordermind/logical-authorization-doctrine-orm-bundle
```

**Support for Doctrine MongoDB**

```
composer require ordermind/logical-authorization-doctrine-mongo-bundle
```

## Getting started

Find the documentation here: https://ordermindlogical-authorization-bundle.readthedocs.io
