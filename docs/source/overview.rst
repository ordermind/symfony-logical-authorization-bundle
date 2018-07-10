========
Overview
========

Requirements
============

This bundle requires Symfony 4.1 or higher.

.. _installation:

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

Contribution Guidelines
=======================

#. Make sure that your code is formatted according to the Symfony coding standards at https://symfony.com/doc/current/contributing/code/standards.html
#. All pull requests must include unit tests to ensure the change works as
   expected and to prevent regressions.

Reporting a security vulnerability
==================================

If you've discovered a security vulnerability in Logical Authorization Bundle, we appreciate your help
in disclosing it to us in a `responsible manner <http://en.wikipedia.org/wiki/Responsible_disclosure>`_.

Publicly disclosing a vulnerability can put the entire community at risk. If
you've discovered a security concern, please email us at
ordermind@gmail.com. We'll work with you to make sure that we understand the
scope of the issue, and that we fully address your concern.

After a security vulnerability has been corrected, a security hotfix release will
be deployed as soon as possible.
