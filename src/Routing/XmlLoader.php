<?php

declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\Routing;

use Symfony\Component\Config\Loader\FileLoader;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\Config\Util\XmlUtils;
use Symfony\Component\Routing\RouteCollection;

/**
 * {@inheritdoc}
 */
class XmlLoader extends FileLoader
{
    private const NAMESPACE_URI = 'http://symfony.com/schema/routing';
    private const SCHEME_PATH = '/schema/routing/routing-1.0.xsd';

    /**
     * Loads an XML file.
     *
     * @param string      $file An XML file path
     * @param string|null $type The resource type
     *
     * @return RouteCollection A RouteCollection instance
     *
     * @throws \InvalidArgumentException when the file cannot be loaded or when the XML cannot be
     *                                   parsed because it does not validate against the scheme
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function load($file, $type = null): RouteCollection
    {
        $path = $this->locator->locate($file);

        $xml = $this->loadFile($path);

        $collection = new RouteCollection();
        $collection->addResource(new FileResource($path));

        // process routes and imports
        foreach ($xml->documentElement->childNodes as $node) {
            if (!$node instanceof \DOMElement) {
                continue;
            }

            $this->parseNode($collection, $node, $path, $file);
        }

        return $collection;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($resource, $type = null): bool
    {
        return is_string($resource) && 'xml' === pathinfo($resource, PATHINFO_EXTENSION) && 'logauth_xml' === $type;
    }

    /**
     * Parses a node from a loaded XML file.
     *
     * @param RouteCollection $collection Collection to associate with the node
     * @param \DOMElement     $node       Element to parse
     * @param string          $path       Full path of the XML file being processed
     * @param string          $file       Loaded file name
     *
     * @throws \InvalidArgumentException When the XML is invalid
     */
    protected function parseNode(RouteCollection $collection, \DOMElement $node, string $path, string $file)
    {
        if (self::NAMESPACE_URI !== $node->namespaceURI) {
            return;
        }

        switch ($node->localName) {
            case 'route':
                $this->parseRoute($collection, $node, $path);
                break;
            case 'import':
                $this->parseImport($collection, $node, $path, $file);
                break;
            default:
                throw new \InvalidArgumentException(
                    sprintf(
                        'Unknown tag "%s" used in file "%s". Expected "route" or "import".',
                        $node->localName,
                        $path
                    )
                );
        }
    }

    /**
     * Parses a route and adds it to the RouteCollection.
     *
     * @param RouteCollection $collection RouteCollection instance
     * @param \DOMElement     $node       Element to parse that represents a Route
     * @param string          $path       Full path of the XML file being processed
     *
     * @throws \InvalidArgumentException When the XML is invalid
     */
    protected function parseRoute(RouteCollection $collection, \DOMElement $node, string $path)
    {
        if ('' === ($id = $node->getAttribute('id')) || !$node->hasAttribute('path')) {
            throw new \InvalidArgumentException(
                sprintf('The <route> element in file "%s" must have an "id" and a "path" attribute.', $path)
            );
        }

        $schemes = preg_split('/[\s,\|]++/', $node->getAttribute('schemes'), -1, PREG_SPLIT_NO_EMPTY);
        $methods = preg_split('/[\s,\|]++/', $node->getAttribute('methods'), -1, PREG_SPLIT_NO_EMPTY);

        list($defaults, $requirements, $options, $condition, $permissions) = $this->parseConfigs($node, $path);

        $route = new Route(
            $node->getAttribute('path'),
            $defaults,
            $requirements,
            $options,
            $node->getAttribute('host'),
            $schemes,
            $methods,
            $condition,
            $permissions
        );
        $collection->add($id, $route);
    }

    /**
     * Parses an import and adds the routes in the resource to the RouteCollection.
     *
     * @param RouteCollection $collection RouteCollection instance
     * @param \DOMElement     $node       Element to parse that represents a Route
     * @param string          $path       Full path of the XML file being processed
     * @param string          $file       Loaded file name
     *
     * @throws \InvalidArgumentException When the XML is invalid
     */
    protected function parseImport(RouteCollection $collection, \DOMElement $node, string $path, string $file)
    {
        if ('' === $resource = $node->getAttribute('resource')) {
            throw new \InvalidArgumentException(
                sprintf('The <import> element in file "%s" must have a "resource" attribute.', $path)
            );
        }

        $type = $node->getAttribute('type');
        $prefix = $node->getAttribute('prefix');
        $host = $node->hasAttribute('host') ? $node->getAttribute('host') : null;
        $schemes =
            $node->hasAttribute('schemes')
            ? preg_split('/[\s,\|]++/', $node->getAttribute('schemes'), -1, PREG_SPLIT_NO_EMPTY)
            : null;
        $methods =
            $node->hasAttribute('methods')
            ? preg_split('/[\s,\|]++/', $node->getAttribute('methods'), -1, PREG_SPLIT_NO_EMPTY)
            : null;

        list($defaults, $requirements, $options, $condition) = $this->parseConfigs($node, $path);

        $this->setCurrentDir(dirname($path));

        $subCollection = $this->import($resource, ('' !== $type ? $type : null), false, $file);
        // @var $subCollection RouteCollection
        $subCollection->addPrefix($prefix);
        if (null !== $host) {
            $subCollection->setHost($host);
        }
        if (null !== $condition) {
            $subCollection->setCondition($condition);
        }
        if (null !== $schemes) {
            $subCollection->setSchemes($schemes);
        }
        if (null !== $methods) {
            $subCollection->setMethods($methods);
        }
        $subCollection->addDefaults($defaults);
        $subCollection->addRequirements($requirements);
        $subCollection->addOptions($options);

        $collection->addCollection($subCollection);
    }

    /**
     * Loads an XML file.
     *
     * @param string $file An XML file path
     *
     * @return \DOMDocument
     *
     * @throws \InvalidArgumentException When loading of XML file fails because of syntax errors
     *                                   or when the XML structure is not as expected by the scheme -
     *                                   see validate()
     */
    protected function loadFile(string $file): \DOMDocument
    {
        return XmlUtils::loadFile($file, __DIR__ . static::SCHEME_PATH);
    }

    /**
     * Parses the config elements (default, requirement, option).
     *
     * @param \DOMElement $node Element to parse that contains the configs
     * @param string      $path Full path of the XML file being processed
     *
     * @return array An array with the defaults as first item, requirements as second and options as third
     *
     * @throws \InvalidArgumentException When the XML is invalid
     */
    private function parseConfigs(\DOMElement $node, string $path): array
    {
        $defaults = [];
        $requirements = [];
        $options = [];
        $condition = null;
        $permissions = null;

        foreach ($node->getElementsByTagNameNS(self::NAMESPACE_URI, '*') as $n) {
            if ($node !== $n->parentNode) {
                continue;
            }

            switch ($n->localName) {
                case 'default':
                    if ($this->isElementValueNull($n)) {
                        $defaults[$n->getAttribute('key')] = null;
                    } else {
                        $defaults[$n->getAttribute('key')] = $this->parseDefaultsConfig($n, $path);
                    }

                    break;
                case 'requirement':
                    $requirements[$n->getAttribute('key')] = trim($n->textContent);
                    break;
                case 'option':
                    $options[$n->getAttribute('key')] = trim($n->textContent);
                    break;
                case 'condition':
                    $condition = trim($n->textContent);
                    break;
                case 'permissions':
                    $simplexml = simplexml_import_dom($n);
                    $permissions = json_decode(json_encode($simplexml), true);
                    break;
                default:
                    throw new \InvalidArgumentException(
                        sprintf(
                            'Unknown tag "%s" used in file "%s". Expected "default", "requirement" or "option".',
                            $n->localName,
                            $path
                        )
                    );
            }
        }

        return [$defaults, $requirements, $options, $condition, $permissions];
    }

    /**
     * Parses the "default" elements.
     *
     * @param \DOMElement $element The "default" element to parse
     * @param string      $path    Full path of the XML file being processed
     *
     * @return array|bool|float|int|string|null The parsed value of the "default" element
     */
    private function parseDefaultsConfig(\DOMElement $element, string $path)
    {
        if ($this->isElementValueNull($element)) {
            return;
        }

        // Check for existing element nodes in the default element. There can
        // only be a single element inside a default element. So this element
        // (if one was found) can safely be returned.
        foreach ($element->childNodes as $child) {
            if (!$child instanceof \DOMElement) {
                continue;
            }

            if (self::NAMESPACE_URI !== $child->namespaceURI) {
                continue;
            }

            return $this->parseDefaultNode($child, $path);
        }

        // If the default element doesn't contain a nested "bool", "int", "float",
        // "string", "list", or "map" element, the element contents will be treated
        // as the string value of the associated default option.
        return trim($element->textContent);
    }

    /**
     * Recursively parses the value of a "default" element.
     *
     * @param \DOMElement $node The node value
     * @param string      $path Full path of the XML file being processed
     *
     * @return array|bool|float|int|string The parsed value
     *
     * @throws \InvalidArgumentException when the XML is invalid
     */
    private function parseDefaultNode(\DOMElement $node, string $path)
    {
        if ($this->isElementValueNull($node)) {
            return;
        }

        switch ($node->localName) {
            case 'bool':
                return 'true' === trim($node->nodeValue) || '1' === trim($node->nodeValue);
            case 'int':
                return (int) trim($node->nodeValue);
            case 'float':
                return (float) trim($node->nodeValue);
            case 'string':
                return trim($node->nodeValue);
            case 'list':
                $list = [];

                foreach ($node->childNodes as $element) {
                    if (!$element instanceof \DOMElement) {
                        continue;
                    }

                    if (self::NAMESPACE_URI !== $element->namespaceURI) {
                        continue;
                    }

                    $list[] = $this->parseDefaultNode($element, $path);
                }

                return $list;
            case 'map':
                $map = [];

                foreach ($node->childNodes as $element) {
                    if (!$element instanceof \DOMElement) {
                        continue;
                    }

                    if (self::NAMESPACE_URI !== $element->namespaceURI) {
                        continue;
                    }

                    $map[$element->getAttribute('key')] = $this->parseDefaultNode($element, $path);
                }

                return $map;
            default:
                throw new \InvalidArgumentException(
                    sprintf(
                        'Unknown tag "%s" used in file "%s". Expected "bool", "int", "float", "string", "list", or '
                        . '"map".',
                        $node->localName,
                        $path
                    )
                );
        }
    }

    /**
     * @internal
     *
     * @param DOMElement $element
     *
     * @return bool
     */
    private function isElementValueNull(\DOMElement $element): bool
    {
        $namespaceUri = 'http://www.w3.org/2001/XMLSchema-instance';

        if (!$element->hasAttributeNS($namespaceUri, 'nil')) {
            return false;
        }

        return
            'true' === $element->getAttributeNS($namespaceUri, 'nil')
            || '1' === $element->getAttributeNS($namespaceUri, 'nil');
    }
}
