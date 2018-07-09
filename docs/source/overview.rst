========
Overview
========

Requirements
============

#. Symfony 4.1 or higher
#. Optionally MongoDB or any database supported by Doctrine ORM

Installation
============

Main bundle:

.. code-block:: bash

    composer require ordermind/logical-authorization-bundle

Support for Doctrine ORM:

.. code-block:: bash

    composer require ordermind/logical-authorization-doctrine-orm-bundle

Support for Doctrine MongoDB:

.. code-block:: bash

    composer require ordermind/logical-authorization-doctrine-mongo-bundle

License
=======

Licensed using the `MIT license <http://opensource.org/licenses/MIT>`_.

    Copyright (c) 2018 Kristofer Tengström <https://github.com/ordermind>

    Permission is hereby granted, free of charge, to any person obtaining a copy
    of this software and associated documentation files (the "Software"), to deal
    in the Software without restriction, including without limitation the rights
    to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
    copies of the Software, and to permit persons to whom the Software is
    furnished to do so, subject to the following conditions:

    The above copyright notice and this permission notice shall be included in
    all copies or substantial portions of the Software.

    THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
    IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
    FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
    AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
    LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
    OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
    THE SOFTWARE.


Contributing
============


Guidelines
----------

1. Guzzle utilizes PSR-1, PSR-2, PSR-4, and PSR-7.
2. Guzzle is meant to be lean and fast with very few dependencies. This means
   that not every feature request will be accepted.
3. Guzzle has a minimum PHP version requirement of PHP 5.5. Pull requests must
   not require a PHP version greater than PHP 5.5 unless the feature is only
   utilized conditionally.
4. All pull requests must include unit tests to ensure the change works as
   expected and to prevent regressions.


Running the tests
-----------------

In order to contribute, you'll need to checkout the source from GitHub and
install Guzzle's dependencies using Composer:

.. code-block:: bash

    git clone https://github.com/guzzle/guzzle.git
    cd guzzle && curl -s http://getcomposer.org/installer | php && ./composer.phar install --dev

Guzzle is unit tested with PHPUnit. Run the tests using the Makefile:

.. code-block:: bash

    make test

.. note::

    You'll need to install node.js v0.5.0 or newer in order to perform
    integration tests on Guzzle's HTTP handlers.


Reporting a security vulnerability
==================================

We want to ensure that Guzzle is a secure HTTP client library for everyone. If
you've discovered a security vulnerability in Guzzle, we appreciate your help
in disclosing it to us in a `responsible manner <http://en.wikipedia.org/wiki/Responsible_disclosure>`_.

Publicly disclosing a vulnerability can put the entire community at risk. If
you've discovered a security concern, please email us at
security@guzzlephp.org. We'll work with you to make sure that we understand the
scope of the issue, and that we fully address your concern. We consider
correspondence sent to security@guzzlephp.org our highest priority, and work to
address any issues that arise as quickly as possible.

After a security vulnerability has been corrected, a security hotfix release will
be deployed as soon as possible.
