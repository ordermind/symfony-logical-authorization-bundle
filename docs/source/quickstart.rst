==========
Quickstart
==========

This page provides a quick introduction to Logical Authorization Bundle and introductory examples.
If you have not already installed Logical Authorization Bundle, head over to the :ref:`installation`
page.

Permission types
================

Permission types are used to check different kinds of conditions for access control. Listed below are the permission types available by default. You can also use boolean permissions if you want to allow or disallow an action completely. Boolean permissions are useful in combination with access bypass in some situations. Please refer to https://github.com/ordermind/logical-permissions-js#boolean-permissions for details regarding boolean permissions.

``role``
    Checks if a user has one or more roles.

    **Examples**

    .. code-block:: javascript

        // Allow access only if user has a single role

        {"role": "ROLE_ADMIN"}

        // Allow access only if user has at least one of the following roles

        {
            "role": {
                "OR": [
                    "ROLE_ADMIN",
                    "ROLE_SALES"
                ]
            }
        }

        // Allow access only if user has both roles

        {
            "role": {
                "AND": [
                    "ROLE_ADMIN",
                    "ROLE_SALES"
                ]
            }
        }

``flag``
    A flag is a boolean condition of some sort. The following flags are available by default:

    ``user_can_bypass_access`` checks if the user can bypass access.

    ``user_has_account`` checks if the user has an account, i.e. is not an anonymous user.

    ``user_is_author`` checks if the user is the author of an entity or document.

    **Examples**

    .. code-block:: javascript

        // Allow access if user normally can bypass access

        {"flag": "user_can_bypass_access"}

        // Allow access if user has an account

        {"flag": "user_has_account"}

        // Allow access if user is the author of an entity or document

        {"flag": "user_is_author"}
``host``
    Checks if the current request comes from an approved host.

    **Example**

    .. code-block:: javascript

        // Allow access only if the request comes from localhost

        {"host": "localhost"}
``ip``
    Checks if the current request comes from an approved ip address.

    **Example**

    .. code-block:: javascript

        // Allow access only if the request comes from 127.0.0.1

        {"ip": "127.0.0.1"}
``method``
    Checks if the current request uses an approved method.

    **Example**

    .. code-block:: javascript

        // Allow access only if the request uses the POST method

        {"method": "POST"}

Adding a custom permission type
===============================

Custom permission types can be added by creating a service with the tag ``logauth.tag.permission_type`` and which implements ``Ordermind\LogicalAuthorizationBundle\PermissionTypes\PermissionTypeInterface``.

If your needs are simple you may prefer to create a flag instead of a whole permission type. You can do that by creating a service with the tag ``logauth.tag.permission_type.flag`` and which implements ``Ordermind\LogicalAuthorizationBundle\PermissionTypes\Flag\FlagInterface``.

Access Bypass
=============

This library supports the ability for a superuser to completely circumvent access checks. To enable a user to bypass access, implement ``Ordermind\LogicalAuthorizationBundle\Interfaces\UserInterface`` in your user class and use the ``setBypassAccess()`` method. This user will now always be granted access, except if you use ``NO_BYPASS`` at the first level of a permission declaration.

**Examples**

.. code-block:: javascript

    // A user with bypass access enabled will be granted access no matter if they have this role or not.

    {"role": "ROLE_ADMIN"}

    // If you want to negate the ability to bypass access for an action, add NO_BYPASS to the first level of nesting like this:

    {
        "role": "ROLE_ADMIN",
        "NO_BYPASS": true
    }

Declaring Permissions
=====================

Permissions may be declared both inline together with for example the declaration of a route, or in the logauth configuration file located at ``/config/packages/logauth.yaml``. These permissions will override any inline permission declarations. For help with the use of logic gates and nesting, please refer to the documentation at https://github.com/ordermind/logical-permissions-js#logic-gates.

Routes
------

Inline route permission declarations are supported for routes defined with annotations, YAML and XML. You can also declare them in a configuration file, and there it is also possible to use regex patterns to declare permissions for multiple routes at once.

.. tabs::

    .. tab:: Annotations

        .. note::
            In order to declare route permissions in annotations within a controller, the type ``logauth_annotation`` must be used in the routing file for this controller.

            **Example**

            If you want to enable the default application controllers for declaring permissions in the annotations, you can create the file ``/config/routes/logauth_annotations.yaml`` with this content:

            .. code-block:: yaml

                logauth_annotations:
                    resource: ../src/Controller/
                    type: logauth_annotation

        Once your controller is configured to work with ``logauth_annotation``, you may declare permissions with your route in json format by using the ``@Permissions`` annotation defined in ``Ordermind\LogicalAuthorizationBundle\Annotation\Routing\Permissions``:

        .. code-block:: php

            use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
            use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
            use Symfony\Bundle\FrameworkBundle\Controller\Controller;
            use Symfony\Component\HttpFoundation\Request;
            use Symfony\Component\HttpFoundation\Response;
            use Ordermind\LogicalAuthorizationBundle\Annotation\Routing\Permissions;

            class DefaultController extends Controller {
                /**
                  * @Route("/route-role", name="route_role")
                  * @Method({"GET"})
                  * @Permissions({
                  *   "role": "ROLE_ADMIN"
                  * })
                  */
                public function routeRoleAction(Request $request) {
                    return new Response('');
                }
            }

    .. tab:: YAML

        If you define your route in yaml you can declare permissions like this:

        .. code-block:: yaml

            route_role:
                path: /route-role
                defaults: { _controller: App\Controller\DefaultController::routeRoleAction }
                permissions:
                    role: ROLE_ADMIN

    .. tab:: XML

        If you define your route in xml you can declare permissions like this:

        .. code-block:: xml

            <route id="route_role" path="/route-role">
                <default key="_controller">App\Controller\DefaultController::routeRoleAction</default>
                <permissions>
                    <role>ROLE_ADMIN</role>
                </permissions>
            </route>

    .. tab:: Config File

        Here are a couple of examples of permission declarations that you can put in the configuration file.

        **Single route example**

        .. code-block:: yaml

            # LogicalAuthorization Configuration
            logauth:
                permissions:
                    routes:
                        route_role:
                            role: ROLE_ADMIN

        **Route pattern example**

        .. code-block:: yaml

            # LogicalAuthorization Configuration
            logauth:
                permissions:
                    route_patterns:
                        ^/route-role:
                            role: ROLE_ADMIN

Doctrine ORM
------------

Inline entity permission declarations are supported for entities mapped with annotations, YAML and XML. You can also declare these permissions in the configuration file. You can declare permissions both on the entity level and on the field level. The permissions are separately declared for each action. For entities the default actions are "create", "read", "update" and "delete", while for fields they are "get" and "set".

.. tabs::

    .. tab:: Annotations

        If you map your entity with annotations, you can declare permissions in json format by using the ``@Permissions`` annotation defined in ``Ordermind\LogicalAuthorizationDoctrineORMBundle\Annotation\Doctrine\Permissions``. Here is an example of permission declarations on both entity and field levels.

        .. code-block:: php

            use Doctrine\ORM\Mapping as ORM;
            use Ordermind\LogicalAuthorizationDoctrineORMBundle\Annotation\Doctrine\Permissions;
            use Ordermind\LogicalAuthorizationBundle\Interfaces\UserInterface;
            use Ordermind\LogicalAuthorizationBundle\Interfaces\ModelInterface;

            /**
            * TestEntityRoleAuthor
            *
            * @ORM\Table(name="testentities_roleauthor")
            * @ORM\Entity(repositoryClass="App\Repository\TestEntityRoleAuthorRepository")
            * @Permissions({
            *   "create": {
            *     "role": "ROLE_ADMIN"
            *   },
            *   "read": {
            *     "OR": {
            *       "role": "ROLE_ADMIN",
            *       "flag": "user_is_author"
            *     }
            *   },
            *   "update": {
            *     "OR": {
            *       "role": "ROLE_ADMIN",
            *       "flag": "user_is_author"
            *     }
            *   },
            *   "delete": {
            *     "OR": {
            *       "role": "ROLE_ADMIN",
            *       "flag": "user_is_author"
            *     }
            *   }
            * })
            */
            class TestEntityRoleAuthor implements ModelInterface
            {
                /**
                * @var int
                *
                * @ORM\Column(name="id", type="integer")
                * @ORM\Id
                * @ORM\GeneratedValue(strategy="AUTO")
                */
                private $id;

                /**
                * @var string
                *
                * @ORM\Column(name="field1", type="string", length=255)
                * @Permissions({
                *   "get": {
                *     "role": "ROLE_ADMIN",
                *     "flag": "user_is_author"
                *   },
                *   "set": {
                *     "role": "ROLE_ADMIN",
                *     "flag": "user_is_author"
                *   }
                * })
                */
                private $field1 = '';

                /**
                * @var Ordermind\LogicalAuthorizationBundle\Interfaces\UserInterface
                *
                * @ORM\ManyToOne(targetEntity="App\Entity\TestUser")
                * @ORM\JoinColumn(name="author_id", referencedColumnName="id")
                */
                private $author;

                /**
                * Set field1
                *
                * @param string $field1
                *
                * @return TestEntityRoleAuthor
                */
                public function setField1($field1)
                {
                    $this->field1 = $field1;

                    return $this;
                }

                /**
                * Get field1
                *
                * @return string
                */
                public function getField1()
                {
                    return $this->field1;
                }

                /**
                * Set author
                *
                * @param Ordermind\LogicalAuthorizationBundle\Interfaces\UserInterface $author
                *
                * @return entity implementing ModelInterface
                */
                public function setAuthor(UserInterface $author)
                {
                    $this->author = $author;

                    return $this;
                }

                /**
                * Get authorId
                *
                * @return Ordermind\LogicalAuthorizationBundle\Interfaces\UserInterface
                */
                public function getAuthor(): ?UserInterface
                {
                    return $this->author;
                }
            }

    .. tab:: YAML

        If you map your entity with yaml you can declare permissions like this in the mapping file:

        .. code-block:: yaml

            App\Entity\TestEntityRoleAuthor:
                type: entity
                repositoryClass: App\Repository\TestEntityRoleAuthorRepository
                table: testentities_roleauthor

                permissions:
                    create:
                        role: ROLE_ADMIN
                    read:
                        OR:
                            role: ROLE_ADMIN
                            flag: user_is_author
                    update:
                        OR:
                            role: ROLE_ADMIN
                            flag: user_is_author
                    delete:
                        OR:
                            role: ROLE_ADMIN
                            flag: user_is_author

                id:
                    id:
                        type: integer
                        generator:
                            strategy: AUTO

                fields:
                    field1:
                        type: string
                        length: 255
                        permissions:
                            get:
                                role: ROLE_ADMIN
                                flag: user_is_author
                            set:
                                role: ROLE_ADMIN
                                flag: user_is_author

                manyToOne:
                    author:
                        targetEntity: App\Entity\TestUser
                        joinColumn:
                            name: author_id
                            referencedColumnName: id

    .. tab:: XML

        If you map your entity with xml you can declare permissions like this in the mapping file:

        .. code-block:: xml

            <entity name="App\Entity\TestEntityRoleAuthor" repository-class="App\Repository\TestEntityRoleAuthorRepository" table="testentities_roleauthor">
                <permissions>
                    <create>
                        <role>ROLE_ADMIN</role>
                    </create>
                    <read>
                        <OR>
                            <role>ROLE_ADMIN</role>
                            <flag>user_is_author</flag>
                        </OR>
                    </read>
                    <update>
                        <OR>
                            <role>ROLE_ADMIN</role>
                            <flag>user_is_author</flag>
                        </OR>
                    </update>
                    <delete>
                        <OR>
                            <role>ROLE_ADMIN</role>
                            <flag>user_is_author</flag>
                        </OR>
                    </delete>
                </permissions>

                <id name="id" type="integer" column="id">
                    <generator strategy="AUTO"/>
                </id>

                <field name="field1" column="field1" type="string" length="255">
                    <permissions>
                        <get>
                            <role>ROLE_ADMIN</role>
                            <flag>user_is_author</flag>
                        </get>
                        <set>
                            <role>ROLE_ADMIN</role>
                            <flag>user_is_author</flag>
                        </set>
                    </permissions>
                </field>

                <many-to-one field="author" target-entity="App\Entity\TestUser">
                    <join-column name="author_id" referenced-column-name="id" />
                </many-to-one>
            </entity>

    .. tab:: Config File

        In the config file you can declare entity and field permissions like this:

        .. code-block:: yaml

            # LogicalAuthorization Configuration
            logauth:
                permissions:
                    models:
                        App\Entity\TestEntityRoleAuthor:
                            create:
                                role: ROLE_ADMIN
                            read:
                                OR:
                                    role: ROLE_ADMIN
                                    flag: user_is_author
                            update:
                                OR:
                                    role: ROLE_ADMIN
                                    flag: user_is_author
                            delete:
                                OR:
                                    role: ROLE_ADMIN
                                    flag: user_is_author
                            fields:
                                field1:
                                    get:
                                        role: ROLE_ADMIN
                                        flag: user_is_author
                                    set:
                                        role: ROLE_ADMIN
                                        flag: user_is_author

Doctrine MongoDB
----------------

Inline document permission declarations are supported for documents mapped with annotations, YAML and XML. You can also declare these permissions in a configuration file. You can declare permissions both on the document level and on the field level. The permissions are separately declared for each action. For documents the default actions are "create", "read", "update" and "delete", while for fields they are "get" and "set".

.. tabs::

    .. tab:: Annotations

        If you map your document with annotations, you can declare permissions in json format by using the ``@Permissions`` annotation defined in ``Ordermind\LogicalAuthorizationDoctrineMongoBundle\Annotation\Doctrine\Permissions``. Here is an example of permission declarations on both document and field levels.

        .. code-block:: php

            use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
            use Ordermind\LogicalAuthorizationDoctrineMongoBundle\Annotation\Doctrine\Permissions;
            use Ordermind\LogicalAuthorizationBundle\Interfaces\UserInterface;
            use Ordermind\LogicalAuthorizationBundle\Interfaces\ModelInterface;

            /**
            * TestDocumentRoleAuthor
            *
            * @ODM\Document(repositoryClass="App\Repository\TestDocumentRoleAuthorRepository", collection="testdocuments_roleauthor")
            * @Permissions({
            *   "create": {
            *     "role": "ROLE_ADMIN"
            *   },
            *   "read": {
            *     "OR": {
            *       "role": "ROLE_ADMIN",
            *       "flag": "user_is_author"
            *     }
            *   },
            *   "update": {
            *     "OR": {
            *       "role": "ROLE_ADMIN",
            *       "flag": "user_is_author"
            *     }
            *   },
            *   "delete": {
            *     "OR": {
            *       "role": "ROLE_ADMIN",
            *       "flag": "user_is_author"
            *     }
            *   }
            * })
            */
            class TestDocumentRoleAuthor implements ModelInterface
            {
                /**
                * @var int
                *
                * @ODM\Field(name="id", type="integer")
                * @ODM\Id
                */
                private $id;

                /**
                * @var string
                *
                * @ODM\Field(name="field1", type="string")
                * @Permissions({
                *   "get": {
                *     "role": "ROLE_ADMIN",
                *     "flag": "user_is_author"
                *   },
                *   "set": {
                *     "role": "ROLE_ADMIN",
                *     "flag": "user_is_author"
                *   }
                * })
                */
                private $field1 = '';

                /**
                * @var Ordermind\LogicalAuthorizationBundle\Interfaces\UserInterface
                *
                * @ODM\ReferenceOne(targetDocument="App\Document\TestUser")
                */
                protected $author;

                /**
                * Get id
                *
                * @return int
                */
                public function getId()
                {
                    return $this->id;
                }

                /**
                * Set field1
                *
                * @param string $field1
                *
                * @return TestDocumentRoleAuthor
                */
                public function setField1($field1)
                {
                    $this->field1 = $field1;

                    return $this;
                }

                /**
                * Get field1
                *
                * @return string
                */
                public function getField1()
                {
                    return $this->field1;
                }

                /**
                * Set author
                *
                * @param Ordermind\LogicalAuthorizationBundle\Interfaces\UserInterface $author
                *
                * @return TestDocumentRoleAuthor
                */
                public function setAuthor(UserInterface $author)
                {
                    $this->author = $author;

                    return $this;
                }

                /**
                * Get authorId
                *
                * @return Ordermind\LogicalAuthorizationBundle\Interfaces\UserInterface
                */
                public function getAuthor(): ?UserInterface
                {
                    return $this->author;
                }
            }

    .. tab:: YAML

        If you map your document with yaml you can declare permissions like this in the mapping file:

        .. code-block:: yaml

            App\Document\TestDocumentRoleAuthor:
                type: document
                repositoryClass: App\Repository\TestDocumentRoleAuthorRepository
                collection: testdocuments_roleauthor

                permissions:
                    create:
                        role: ROLE_ADMIN
                    read:
                        OR:
                            role: ROLE_ADMIN
                            flag: user_is_author
                    update:
                        OR:
                            role: ROLE_ADMIN
                            flag: user_is_author
                    delete:
                        OR:
                            role: ROLE_ADMIN
                            flag: user_is_author

                fields:
                    id:
                        id: true
                    field1:
                        type: string
                        permissions:
                            get:
                                role: ROLE_ADMIN
                                flag: user_is_author
                            set:
                                role: ROLE_ADMIN
                                flag: user_is_author

                referenceOne:
                    author:
                        targetDocument: App\Document\TestUser


    .. tab:: XML

        If you map your document with xml you can declare permissions like this in the mapping file:

        .. code-block:: xml

            <document name="App\Document\TestDocumentRoleAuthor" repository-class="App\Repository\TestDocumentRoleAuthorRepository" collection="testdocuments_roleauthor">
              <permissions>
                <create>
                  <role>ROLE_ADMIN</role>
                </create>
                <read>
                  <OR>
                    <role>ROLE_ADMIN</role>
                    <flag>user_is_author</flag>
                  </OR>
                </read>
                <update>
                  <OR>
                    <role>ROLE_ADMIN</role>
                    <flag>user_is_author</flag>
                  </OR>
                </update>
                <delete>
                  <OR>
                    <role>ROLE_ADMIN</role>
                    <flag>user_is_author</flag>
                  </OR>
                </delete>
              </permissions>

              <field name="id" id="true" />

              <field name="field1" type="string">
                <permissions>
                  <get>
                    <role>ROLE_ADMIN</role>
                    <flag>user_is_author</flag>
                  </get>
                  <set>
                    <role>ROLE_ADMIN</role>
                    <flag>user_is_author</flag>
                  </set>
                </permissions>
              </field>

              <reference-one field="author" target-document="App\Document\TestUser" />
            </document>

    .. tab:: Config File

        In the config file you can declare document and field permissions like this:

        .. code-block:: yaml

            # LogicalAuthorization Configuration
            logauth:
                permissions:
                    models:
                        App\Document\TestDocumentRoleAuthor:
                            create:
                                role: ROLE_ADMIN
                            read:
                                OR:
                                    role: ROLE_ADMIN
                                    flag: user_is_author
                            update:
                                OR:
                                    role: ROLE_ADMIN
                                    flag: user_is_author
                            delete:
                                OR:
                                    role: ROLE_ADMIN
                                    flag: user_is_author
                            fields:
                                field1:
                                    get:
                                        role: ROLE_ADMIN
                                        flag: user_is_author
                                    set:
                                        role: ROLE_ADMIN
                                        flag: user_is_author
        
Checking Permissions
====================

Now that you have declared your permissions, it's time to put them to use. This section explains how you check these permissions.

Routes
------

Route permissions are automatically checked, so if you have declared permissions for a route correctly they should just work without any further effort on your part.

Doctrine ORM
------------

Entity permissions are not checked automatically. In order to make that job easier for you, there are decorators that you can use for this purpose. The recommended way is to declare each repository as a service like this:

.. code-block:: yaml

    repository.test_entity_roleauthor:
        class: Ordermind\LogicalAuthorizationDoctrineORMBundle\Services\Decorator\RepositoryDecorator
        factory: ['@logauth_doctrine_orm.service.repository_decorator_factory', getRepositoryDecorator]
        arguments:
            - App\Entity\TestEntityRoleAuthor

The only thing you will need to alter in the above configuration is the service name (``repository.test_entity_roleauthor``) and the entity class argument (``App\Entity\TestEntityRoleAuthor``) for the related entity. The rest can be left as is.

Once you have declared a repository as a service in this way, it can easily be fetched by dependency injection. You can now use it as a regular repository, but the difference is that the results from queries are automatically filtered by permissions, so if a user doesn't have permissions for the "read" action for an entity, it will be removed from the result. The resulting objects will also be instances of ``Ordermind\LogicalAuthorizationDoctrineORMBundle\Services\Decorator\EntityDecorator`` rather than the raw entities. The entity decorators, just like the repository decorators, act just like the wrapped objects but allows for automatic permission checks when you try to interact with it.

One exception to this rule is lazy loaded entities, which are sometimes used for performance purposes. Because filtering would require instantiating the objects and thus defeat the whole purpose of using lazy loaded collections in the first place, these are not filtered or wrapped by decorators by default. If you do want to filter and wrap them, you can enable it like this:

.. code-block:: yaml

    # LogicalAuthorization Doctrine ORM Configuration
    logauth_doctrine_orm:
        check_lazy_loaded_entities: true

If you already have an entity object and want to wrap it in a decorator to do permission checks, you can load the corresponding repository decorator service that you have declared as laid out above and then use ``Ordermind\LogicalAuthorizationDoctrineORMBundle\Services\Decorator\RepositoryDecoratorInterface::wrapEntity()`` to get the decorator.

Doctrine MongoDB
----------------

Document permissions are not checked automatically. In order to make that job easier for you, there are decorators that you can use for this purpose. The recommended way is to declare each repository as a service like this:

.. code-block:: yaml

    repository.test_document_roleauthor:
        class: Ordermind\LogicalAuthorizationDoctrineMongoBundle\Services\Decorator\RepositoryDecorator
        factory: ['@logauth_doctrine_mongo.service.repository_decorator_factory', getRepositoryDecorator]
        arguments:
            - App\Document\TestDocumentRoleAuthor

The only thing you will need to alter in the above configuration is the service name (``repository.test_document_roleauthor``) and the document class argument (``App\Document\TestDocumentRoleAuthor``) for the related document. The rest can be left as is.

Once you have declared a repository as a service in this way, it can easily be fetched by dependency injection. You can now use it as a regular repository, but the difference is that the results from queries are automatically filtered by permissions, so if a user doesn't have permissions for the "read" action for an document, it will be removed from the result. The resulting objects will also be instances of ``Ordermind\LogicalAuthorizationDoctrineMongoBundle\Services\Decorator\DocumentDecorator`` rather than the raw documents. The document decorators, just like the repository decorators, act just like the wrapped objects but allows for automatic permission checks when you try to interact with it.

One exception to this rule is lazy loaded documents, which are sometimes used for performance purposes. Because filtering would require instantiating the objects and thus defeat the whole purpose of using lazy loaded collections in the first place, these are not filtered or wrapped by decorators by default. If you do want to filter and wrap them, you can enable it like this:

.. code-block:: yaml

    # LogicalAuthorization Mongo Configuration
    logauth_doctrine_mongo:
        check_lazy_loaded_documents: true

If you already have an document object and want to wrap it in a decorator to do permission checks, you can load the corresponding repository decorator service that you have declared as laid out above and then use ``Ordermind\LogicalAuthorizationDoctrineMongoBundle\Services\Decorator\RepositoryDecoratorInterface::wrapDocument()`` to get the decorator.

Debugging
=========

This bundle has extensive support for debugging to help making it crystal clear exactly what goes on when the permissions are being checked. If you run the environment in dev mode, you will see a padlock icon in the devbar with the total amount of access checks for the current request, as well as their outcome. If you click on the icon you end up on the debug panel where you can take a closer look at each of the access checks to see which permissions were checked, the context and from where the access check was made. If the permissions are complex, they will be broken down into parts so that you can see the return value for each part. If you click the "Permission Tree" tab, you can navigate a tree of all the declared permissions for the site. This tree can also be seen by running the console command ``logauth:dump-permission-tree``.

Using Responses
===============

In the previous examples, we retrieved a ``$response`` variable or we were
delivered a response from a promise. The response object implements a PSR-7
response, ``Psr\Http\Message\ResponseInterface``, and contains lots of
helpful information.

You can get the status code and reason phrase of the response:

.. code-block:: php

    $code = $response->getStatusCode(); // 200
    $reason = $response->getReasonPhrase(); // OK

You can retrieve headers from the response:

.. code-block:: php

    // Check if a header exists.
    if ($response->hasHeader('Content-Length')) {
        echo "It exists";
    }

    // Get a header from the response.
    echo $response->getHeader('Content-Length')[0];

    // Get all of the response headers.
    foreach ($response->getHeaders() as $name => $values) {
        echo $name . ': ' . implode(', ', $values) . "\r\n";
    }

The body of a response can be retrieved using the ``getBody`` method. The body
can be used as a string, cast to a string, or used as a stream like object.

.. code-block:: php

    $body = $response->getBody();
    // Implicitly cast the body to a string and echo it
    echo $body;
    // Explicitly cast the body to a string
    $stringBody = (string) $body;
    // Read 10 bytes from the body
    $tenBytes = $body->read(10);
    // Read the remaining contents of the body as a string
    $remainingBytes = $body->getContents();


Query String Parameters
=======================

You can provide query string parameters with a request in several ways.

You can set query string parameters in the request's URI:

.. code-block:: php

    $response = $client->request('GET', 'http://httpbin.org?foo=bar');

You can specify the query string parameters using the ``query`` request
option as an array.

.. code-block:: php

    $client->request('GET', 'http://httpbin.org', [
        'query' => ['foo' => 'bar']
    ]);

Providing the option as an array will use PHP's ``http_build_query`` function
to format the query string.

And finally, you can provide the ``query`` request option as a string.

.. code-block:: php

    $client->request('GET', 'http://httpbin.org', ['query' => 'foo=bar']);


Uploading Data
==============

Guzzle provides several methods for uploading data.

You can send requests that contain a stream of data by passing a string,
resource returned from ``fopen``, or an instance of a
``Psr\Http\Message\StreamInterface`` to the ``body`` request option.

.. code-block:: php

    // Provide the body as a string.
    $r = $client->request('POST', 'http://httpbin.org/post', [
        'body' => 'raw data'
    ]);

    // Provide an fopen resource.
    $body = fopen('/path/to/file', 'r');
    $r = $client->request('POST', 'http://httpbin.org/post', ['body' => $body]);

    // Use the stream_for() function to create a PSR-7 stream.
    $body = \GuzzleHttp\Psr7\stream_for('hello!');
    $r = $client->request('POST', 'http://httpbin.org/post', ['body' => $body]);

An easy way to upload JSON data and set the appropriate header is using the
``json`` request option:

.. code-block:: php

    $r = $client->request('PUT', 'http://httpbin.org/put', [
        'json' => ['foo' => 'bar']
    ]);


POST/Form Requests
------------------

In addition to specifying the raw data of a request using the ``body`` request
option, Guzzle provides helpful abstractions over sending POST data.


Sending form fields
~~~~~~~~~~~~~~~~~~~

Sending ``application/x-www-form-urlencoded`` POST requests requires that you
specify the POST fields as an array in the ``form_params`` request options.

.. code-block:: php

    $response = $client->request('POST', 'http://httpbin.org/post', [
        'form_params' => [
            'field_name' => 'abc',
            'other_field' => '123',
            'nested_field' => [
                'nested' => 'hello'
            ]
        ]
    ]);


Sending form files
~~~~~~~~~~~~~~~~~~

You can send files along with a form (``multipart/form-data`` POST requests),
using the ``multipart`` request option. ``multipart`` accepts an array of
associative arrays, where each associative array contains the following keys:

- name: (required, string) key mapping to the form field name.
- contents: (required, mixed) Provide a string to send the contents of the
  file as a string, provide an fopen resource to stream the contents from a
  PHP stream, or provide a ``Psr\Http\Message\StreamInterface`` to stream
  the contents from a PSR-7 stream.

.. code-block:: php

    $response = $client->request('POST', 'http://httpbin.org/post', [
        'multipart' => [
            [
                'name'     => 'field_name',
                'contents' => 'abc'
            ],
            [
                'name'     => 'file_name',
                'contents' => fopen('/path/to/file', 'r')
            ],
            [
                'name'     => 'other_file',
                'contents' => 'hello',
                'filename' => 'filename.txt',
                'headers'  => [
                    'X-Foo' => 'this is an extra header to include'
                ]
            ]
        ]
    ]);


Cookies
=======

Guzzle can maintain a cookie session for you if instructed using the
``cookies`` request option. When sending a request, the ``cookies`` option
must be set to an instance of ``GuzzleHttp\Cookie\CookieJarInterface``.

.. code-block:: php

    // Use a specific cookie jar
    $jar = new \GuzzleHttp\Cookie\CookieJar;
    $r = $client->request('GET', 'http://httpbin.org/cookies', [
        'cookies' => $jar
    ]);

You can set ``cookies`` to ``true`` in a client constructor if you would like
to use a shared cookie jar for all requests.

.. code-block:: php

    // Use a shared client cookie jar
    $client = new \GuzzleHttp\Client(['cookies' => true]);
    $r = $client->request('GET', 'http://httpbin.org/cookies');


Redirects
=========

Guzzle will automatically follow redirects unless you tell it not to. You can
customize the redirect behavior using the ``allow_redirects`` request option.

- Set to ``true`` to enable normal redirects with a maximum number of 5
  redirects. This is the default setting.
- Set to ``false`` to disable redirects.
- Pass an associative array containing the 'max' key to specify the maximum
  number of redirects and optionally provide a 'strict' key value to specify
  whether or not to use strict RFC compliant redirects (meaning redirect POST
  requests with POST requests vs. doing what most browsers do which is
  redirect POST requests with GET requests).

.. code-block:: php

    $response = $client->request('GET', 'http://github.com');
    echo $response->getStatusCode();
    // 200

The following example shows that redirects can be disabled.

.. code-block:: php

    $response = $client->request('GET', 'http://github.com', [
        'allow_redirects' => false
    ]);
    echo $response->getStatusCode();
    // 301


Exceptions
==========

**Tree View**

The following tree view describes how the Guzzle Exceptions depend
on each other.

.. code-block:: none

    . \RuntimeException
    ├── SeekException (implements GuzzleException)
    └── TransferException (implements GuzzleException)
        └── RequestException
            ├── BadResponseException
            │   ├── ServerException
            │   └── ClientException
            ├── ConnectionException
            └── TooManyRedirectsException
 
Guzzle throws exceptions for errors that occur during a transfer.

- In the event of a networking error (connection timeout, DNS errors, etc.),
  a ``GuzzleHttp\Exception\RequestException`` is thrown. This exception
  extends from ``GuzzleHttp\Exception\TransferException``. Catching this
  exception will catch any exception that can be thrown while transferring
  requests.

  .. code-block:: php

      use GuzzleHttp\Psr7;
      use GuzzleHttp\Exception\RequestException;

      try {
          $client->request('GET', 'https://github.com/_abc_123_404');
      } catch (RequestException $e) {
          echo Psr7\str($e->getRequest());
          if ($e->hasResponse()) {
              echo Psr7\str($e->getResponse());
          }
      }

- A ``GuzzleHttp\Exception\ConnectException`` exception is thrown in the
  event of a networking error. This exception extends from
  ``GuzzleHttp\Exception\RequestException``.

- A ``GuzzleHttp\Exception\ClientException`` is thrown for 400
  level errors if the ``http_errors`` request option is set to true. This
  exception extends from ``GuzzleHttp\Exception\BadResponseException`` and
  ``GuzzleHttp\Exception\BadResponseException`` extends from
  ``GuzzleHttp\Exception\RequestException``.

  .. code-block:: php

      use GuzzleHttp\Exception\ClientException;

      try {
          $client->request('GET', 'https://github.com/_abc_123_404');
      } catch (ClientException $e) {
          echo Psr7\str($e->getRequest());
          echo Psr7\str($e->getResponse());
      }

- A ``GuzzleHttp\Exception\ServerException`` is thrown for 500 level
  errors if the ``http_errors`` request option is set to true. This
  exception extends from ``GuzzleHttp\Exception\BadResponseException``.

- A ``GuzzleHttp\Exception\TooManyRedirectsException`` is thrown when too
  many redirects are followed. This exception extends from ``GuzzleHttp\Exception\RequestException``.

All of the above exceptions extend from
``GuzzleHttp\Exception\TransferException``.


Environment Variables
=====================

Guzzle exposes a few environment variables that can be used to customize the
behavior of the library.

``GUZZLE_CURL_SELECT_TIMEOUT``
    Controls the duration in seconds that a curl_multi_* handler will use when
    selecting on curl handles using ``curl_multi_select()``. Some systems
    have issues with PHP's implementation of ``curl_multi_select()`` where
    calling this function always results in waiting for the maximum duration of
    the timeout.
``HTTP_PROXY``
    Defines the proxy to use when sending requests using the "http" protocol.
    
    Note: because the HTTP_PROXY variable may contain arbitrary user input on some (CGI) environments, the variable is only used on the CLI SAPI. See https://httpoxy.org for more information.
``HTTPS_PROXY``
    Defines the proxy to use when sending requests using the "https" protocol.


Relevant ini Settings
---------------------

Guzzle can utilize PHP ini settings when configuring clients.

``openssl.cafile``
    Specifies the path on disk to a CA file in PEM format to use when sending
    requests over "https". See: https://wiki.php.net/rfc/tls-peer-verification#phpini_defaults
