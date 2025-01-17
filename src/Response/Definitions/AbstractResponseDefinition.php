<?php
declare(strict_types=1);

namespace ArrayAccess\RdapClient\Response\Definitions;

use ArrayAccess\RdapClient\Exceptions\InvalidDataTypeException;
use ArrayAccess\RdapClient\Exceptions\MismatchProtocolBehaviorException;
use ArrayAccess\RdapClient\Interfaces\RdapRequestInterface;
use ArrayAccess\RdapClient\Interfaces\RdapResponseDefinitionInterface;
use ArrayAccess\RdapClient\Interfaces\RdapResponseInterface;
use ArrayAccess\RdapClient\Interfaces\ResponseData\RdapResponseDataConformanceDataInterface;
use ArrayAccess\RdapClient\Interfaces\ResponseData\RdapResponseDataInterface;
use ArrayAccess\RdapClient\Interfaces\ResponseData\RdapResponseDataNamedInterface;
use ArrayAccess\RdapClient\Interfaces\ResponseData\RdapResponseDataStringableInterface;
use ArrayAccess\RdapClient\Response\Data\Algorithm;
use ArrayAccess\RdapClient\Response\Data\AsEventActor;
use ArrayAccess\RdapClient\Response\Data\ContactURI;
use ArrayAccess\RdapClient\Response\Data\Country;
use ArrayAccess\RdapClient\Response\Data\Definitions\DomainDefinitionObjectClassName;
use ArrayAccess\RdapClient\Response\Data\Definitions\DsDataDefinition;
use ArrayAccess\RdapClient\Response\Data\Definitions\EntityDefinitionObjectClassName;
use ArrayAccess\RdapClient\Response\Data\Definitions\EventsCollection;
use ArrayAccess\RdapClient\Response\Data\Definitions\NamedRecursiveObjectData;
use ArrayAccess\RdapClient\Response\Data\Definitions\NameserverDefinitionObjectClassName;
use ArrayAccess\RdapClient\Response\Data\Definitions\NameServersDefinition;
use ArrayAccess\RdapClient\Response\Data\Definitions\NetworksDefinition;
use ArrayAccess\RdapClient\Response\Data\Definitions\NoticesDefinition;
use ArrayAccess\RdapClient\Response\Data\Definitions\PublicIdsDefinitions;
use ArrayAccess\RdapClient\Response\Data\Definitions\RdapCustomConformanceDataCollection;
use ArrayAccess\RdapClient\Response\Data\Definitions\RemarksDefinition;
use ArrayAccess\RdapClient\Response\Data\Definitions\VCardDefinition;
use ArrayAccess\RdapClient\Response\Data\Definitions\VCardsDefinitions;
use ArrayAccess\RdapClient\Response\Data\DelegationSigned;
use ArrayAccess\RdapClient\Response\Data\Description;
use ArrayAccess\RdapClient\Response\Data\Digest;
use ArrayAccess\RdapClient\Response\Data\DigestType;
use ArrayAccess\RdapClient\Response\Data\DomainSearchResults;
use ArrayAccess\RdapClient\Response\Data\DsData;
use ArrayAccess\RdapClient\Response\Data\EndAddress;
use ArrayAccess\RdapClient\Response\Data\EndAutNum;
use ArrayAccess\RdapClient\Response\Data\Entities;
use ArrayAccess\RdapClient\Response\Data\EntitySearchResults;
use ArrayAccess\RdapClient\Response\Data\ErrorCode;
use ArrayAccess\RdapClient\Response\Data\EventAction;
use ArrayAccess\RdapClient\Response\Data\EventActor;
use ArrayAccess\RdapClient\Response\Data\EventDate;
use ArrayAccess\RdapClient\Response\Data\Events;
use ArrayAccess\RdapClient\Response\Data\Handle;
use ArrayAccess\RdapClient\Response\Data\Href;
use ArrayAccess\RdapClient\Response\Data\HrefLang;
use ArrayAccess\RdapClient\Response\Data\Identifier;
use ArrayAccess\RdapClient\Response\Data\IpAddresses;
use ArrayAccess\RdapClient\Response\Data\Ipv4;
use ArrayAccess\RdapClient\Response\Data\Ipv6;
use ArrayAccess\RdapClient\Response\Data\IpVersion;
use ArrayAccess\RdapClient\Response\Data\KeyTag;
use ArrayAccess\RdapClient\Response\Data\Label;
use ArrayAccess\RdapClient\Response\Data\Lang;
use ArrayAccess\RdapClient\Response\Data\LdhName;
use ArrayAccess\RdapClient\Response\Data\Link;
use ArrayAccess\RdapClient\Response\Data\Links;
use ArrayAccess\RdapClient\Response\Data\Media;
use ArrayAccess\RdapClient\Response\Data\Name;
use ArrayAccess\RdapClient\Response\Data\NameServers;
use ArrayAccess\RdapClient\Response\Data\NameserverSearchResults;
use ArrayAccess\RdapClient\Response\Data\Networks;
use ArrayAccess\RdapClient\Response\Data\NonStandards\CustomArrayData;
use ArrayAccess\RdapClient\Response\Data\NonStandards\CustomNamedData;
use ArrayAccess\RdapClient\Response\Data\NonStandards\CustomUnNamedData;
use ArrayAccess\RdapClient\Response\Data\NonStandards\EmptyObject;
use ArrayAccess\RdapClient\Response\Data\Notices;
use ArrayAccess\RdapClient\Response\Data\ObjectClassName;
use ArrayAccess\RdapClient\Response\Data\ParentHandle;
use ArrayAccess\RdapClient\Response\Data\Port43;
use ArrayAccess\RdapClient\Response\Data\PublicIds;
use ArrayAccess\RdapClient\Response\Data\RdapConformance;
use ArrayAccess\RdapClient\Response\Data\Rel;
use ArrayAccess\RdapClient\Response\Data\Remarks;
use ArrayAccess\RdapClient\Response\Data\Roles;
use ArrayAccess\RdapClient\Response\Data\SecureDNS;
use ArrayAccess\RdapClient\Response\Data\StartAddress;
use ArrayAccess\RdapClient\Response\Data\StartAutNum;
use ArrayAccess\RdapClient\Response\Data\Status;
use ArrayAccess\RdapClient\Response\Data\Title;
use ArrayAccess\RdapClient\Response\Data\Type;
use ArrayAccess\RdapClient\Response\Data\Value;
use ArrayAccess\RdapClient\Response\Data\VcardArray;
use ArrayAccess\RdapClient\Response\Data\ZoneSigned;
use ArrayAccess\RdapClient\Response\Traits\AssertionTrait;
use Throwable;
use function array_filter;
use function array_shift;
use function array_values;
use function get_object_vars;
use function gettype;
use function in_array;
use function is_array;
use function is_string;
use function json_decode;
use function json_encode;
use function property_exists;
use function sprintf;
use const JSON_UNESCAPED_SLASHES;

abstract class AbstractResponseDefinition implements RdapResponseDefinitionInterface
{
    use AssertionTrait;

    /**
     * @var RdapConformance|null $rdapConformance The RDAP conformance
     */
    protected ?RdapConformance $rdapConformance = null;

    /**
     * @var ObjectClassName<string>|null $objectClassName The object class name
     */
    protected ?ObjectClassName $objectClassName = null;

    /**
     * The RDAP conformance data
     * @var RdapResponseDataConformanceDataInterface<array-key, mixed>|null $rdapConformanceData
     */
    protected ?RdapResponseDataConformanceDataInterface $rdapConformanceData = null;

    /**
     * @var ErrorCode|null $errorCode The error code
     */
    protected ?ErrorCode $errorCode = null;

    /**
     * @var Title|null $title The title
     */
    protected ?Title $title = null;

    /**
     * @var Description|null $description The description
     */
    protected ?Description $description = null;

    /**
     * @var Notices|null $notices The notices
     */
    protected ?Notices $notices = null;

    /**
     * @var Lang|null $lang The lang
     */
    protected ?Lang $lang = null;

    /**
     * @var Status|null $status The status
     */
    protected ?Status $status = null;

    /**
     * @var Links|null $links The links
     */
    protected ?Links $links = null;

    /**
     * @var Events|null $events The events
     */
    protected ?Events $events = null;

    /**
     * @var Handle|null $handle The handle
     */
    protected ?Handle $handle = null;

    /**
     * @var Entities|null $entities The entities
     */
    protected ?Entities $entities = null;

    /**
     * @var Name|null $name The name
     */
    protected ?Name $name = null;

    /**
     * @var RdapRequestInterface|false|null $relatedRequest The related request
     */
    protected RdapRequestInterface|false|null $relatedRequest = null;

    /**
     * Constructor
     * @param RdapResponseInterface $rdapResponseObject
     */
    public function __construct(
        protected RdapResponseInterface $rdapResponseObject
    ) {
        $json = json_decode($rdapResponseObject->getResponseJson(), true);
        if (!is_array($json)) {
            throw new InvalidDataTypeException(
                'Response is not valid json content'
            );
        }
        foreach ($json as $key => $item) {
            if (!is_string($key)) {
                throw new InvalidDataTypeException(
                    sprintf(
                        'Root key only accepted key string: %s given',
                        gettype($key)
                    )
                );
            }

            $data = $this->createObject($key, $item, 0);
            if (property_exists($this, $key)
                && !in_array($key, ['relatedRequest', 'rdapConformanceData', 'rdapResponseObject'])
            ) {
                $this->$key = $data;
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function getRdapResponseObject(): RdapResponseInterface
    {
        return $this->rdapResponseObject;
    }

    /**
     * Create object
     *
     * @param string $keyName
     * @param mixed $valueData
     * @param int $depth
     * @return RdapResponseDataInterface
     */
    protected function createObject(
        string $keyName,
        mixed $valueData,
        int $depth
    ) : RdapResponseDataInterface {
        switch ($keyName) {
            case 'rdapConformance':
            case 'status':
            case 'description':
            case 'roles':
                $this->assertArray($valueData, $keyName);
                $this->assertArrayStringValue($valueData, $keyName);
                $valueData = array_values($valueData);
                $data = match ($keyName) {
                    'rdapConformance' => new RdapConformance(...$valueData),
                    'status' => new Status(...$valueData),
                    'roles' => new Roles(...$valueData),
                    'description' => new Description(...$valueData),
                };
                break;
            case 'objectClassName':
            case 'title':
            case 'lang':
            case 'label':
            case 'handle':
            case 'href':
            case 'rel':
            case 'identifier':
            case 'port43':
            case 'eventAction':
            case 'eventActor':
            case 'eventDate':
            case 'ldhName':
            case 'media':
            case 'startAddress':
            case 'endAddress':
            case 'parentHandle':
            case 'ipVersion':
            case 'name':
            case 'country':
            case 'digest':
            case 'contact_URI':
                $this->assertString($valueData, $keyName);
                $data = match ($keyName) {
                    'objectClassName' => new ObjectClassName($valueData),
                    'title' => new Title($valueData),
                    'lang' => new Lang($valueData),
                    'label' => new Label($valueData),
                    'handle' => new Handle($valueData),
                    'href' => new Href($valueData),
                    'rel' => new Rel($valueData),
                    'identifier' => new Identifier($valueData),
                    'port43' => new Port43($valueData),
                    'eventAction' => new EventAction($valueData),
                    'eventActor' => new EventActor($valueData),
                    'eventDate' => new EventDate($valueData),
                    'ldhName' => new LdhName($valueData),
                    'media' => new Media($valueData),
                    'startAddress' => new StartAddress($valueData),
                    'endAddress' => new EndAddress($valueData),
                    'parentHandle' => new ParentHandle($valueData),
                    'name' => new Name($valueData),
                    'country' => new Country($valueData),
                    'ipVersion' => new IpVersion($valueData),
                    'digest' => new Digest($valueData),
                    'contact_URI' => new ContactURI($valueData),
                };
                break;
            case 'startAutnum':
            case 'endAutnum':
                $this->assertNumeric($valueData, $keyName);
                $data = match ($keyName) {
                    'startAutnum' => new StartAutNum($valueData),
                    'endAutnum' => new EndAutNum($valueData),
                };
                break;
            case 'errorCode':
            case 'keyTag':
            case 'digestType':
            case 'algorithm':
                $this->assertInteger($valueData, $keyName);
                $data = match ($keyName) {
                    'errorCode' => new ErrorCode($valueData),
                    'keyTag' => new KeyTag($valueData),
                    'digestType' => new DigestType($valueData),
                    'algorithm' => new Algorithm($valueData),
                };
                break;
            case 'link':
                $this->assertArray($valueData, $keyName);
                $this->assertArrayStringKey($valueData, $keyName);
                $hrefLang = $valueData['hreflang']??null;
                if ($hrefLang !== null) {
                    $this->assertArrayStringValue($hrefLang);
                }
                unset($valueData['hreflang']);
                $this->assertArrayStringValue($valueData, $keyName);
                $values = [];
                foreach ($valueData as $keyName => $item) {
                    $values[] = $this->createObject(
                        $keyName,
                        $item,
                        $depth
                    );
                }
                if ($hrefLang !== null) {
                    $values[] = new HrefLang(...$hrefLang);
                }
                $values = array_filter($values, static function ($item) {
                    return $item instanceof HrefLang || $item instanceof Value
                        || $item instanceof Rel
                        || $item instanceof Href
                        || $item instanceof Title
                        || $item instanceof Media
                        || $item instanceof Type;
                });
                $values = array_values($values);
                $data = new Link(...$values);
                break;
            case 'type':
            case 'value':
                $this->assertStringOrArray($valueData, $keyName);
                // root type should be as string
                if ($depth === 0 && $keyName === 'type') {
                    $this->assertString($valueData, $keyName);
                }
                $data = $keyName === 'type' ? new Type($valueData) : new Value($valueData);
                break;
            case 'ipAddresses':
                $this->assertArray($valueData);
                $this->assertArrayStringKey($valueData, $keyName);
                $values = [];
                foreach ($valueData as $key => $item) {
                    $this->assertArrayStringValue($item);
                    if ($key === 'v4') {
                        $values[] = new Ipv4(...$item);
                        continue;
                    }
                    if ($key === 'v6') {
                        $values[] = new Ipv6(...$item);
                    }
                }
                $data = new IpAddresses(...$values);
                break;
            case 'secureDNS':
                $this->assertArray($valueData, $keyName);
                $data = [];
                if (($valueData['delegationSigned']??null) !== null) {
                    $this->assertBoolean($valueData['delegationSigned'], $keyName);
                    $data[] = new DelegationSigned($valueData['delegationSigned']);
                } elseif (($valueData['zoneSigned']??null) !== null) {
                    $this->assertBoolean($valueData['zoneSigned'], $keyName);
                    $data[] = new ZoneSigned($valueData['zoneSigned']);
                } elseif (($valueData['dsData']??null) !== null) {
                    $this->assertArray($valueData['dsData'], $keyName);
                    $values = [];
                    foreach ($valueData['dsData'] as $item) {
                        $values[] = $this->createObject(
                            DsDataDefinition::class,
                            $item,
                            $depth + 1
                        );
                    }
                    $values = array_filter($values, static function ($item) {
                        return $item instanceof DsDataDefinition;
                    });
                    $values = array_values($values);
                    $data[] = new DsData(...$values);
                }
                $data = new SecureDNS(...$data);
                break;
            case 'vcardArray':
                $this->assertArray($valueData, $keyName);
                $name = array_shift($valueData);
                $this->assertString($name);
                // $this->assertEqual($name, 'vcard');
                $values = [];
                foreach ($valueData as $item) {
                    $values[] = $this->createObject(
                        VCardsDefinitions::class,
                        $item,
                        $depth + 1
                    );
                }
                $values = array_filter($values, static function ($item) {
                    return $item instanceof VCardsDefinitions;
                });
                $data = new VcardArray(...$values);
                break;
            case 'publicIds':
            case 'notices':
            case 'nameservers':
            case 'remarks':
            case 'entities':
            case 'events':
            case 'asEventActor':
            case 'links':
            case 'networks':
                $this->assertArray($valueData, $keyName);
                $keyMatch = match ($keyName) {
                    'notices' => NoticesDefinition::class,
                    'nameservers' => NameServersDefinition::class,
                    'entities' => EntityDefinitionObjectClassName::class,
                    'remarks' => RemarksDefinition::class,
                    'publicIds' => PublicIdsDefinitions::class,
                    'networks' => NetworksDefinition::class,
                    'links' => Link::class,
                    'events',
                    'asEventActor' => EventsCollection::class,
                };
                $values = [];
                foreach ($valueData as $item) {
                    $values[] = $this->createObject(
                        $keyMatch,
                        $item,
                        $depth + 1
                    );
                }
                $data = match ($keyName) {
                    'notices' => new Notices(...array_values(
                        array_filter($values, static function ($item) {
                            return $item instanceof NoticesDefinition;
                        })
                    )),
                    'nameservers' => new NameServers(...array_values(
                        array_filter($values, static function ($item) {
                            return $item instanceof NameServersDefinition;
                        })
                    )),
                    'entities' => new Entities(...array_values(
                        array_filter($values, static function ($item) {
                            return $item instanceof EntityDefinitionObjectClassName;
                        })
                    )),
                    'remarks' => new Remarks(...array_values(
                        array_filter($values, static function ($item) {
                            return $item instanceof RemarksDefinition;
                        })
                    )),
                    'publicIds' => new PublicIds(...array_values(
                        array_filter($values, static function ($item) {
                            return $item instanceof PublicIdsDefinitions;
                        })
                    )),
                    'events' => new Events(...array_values(
                        array_filter($values, static function ($item) {
                            return $item instanceof EventsCollection;
                        })
                    )),
                    'links' => new Links(...array_values(
                        array_filter($values, static function ($item) {
                            return $item instanceof Link;
                        })
                    )),
                    'asEventActor' => new AsEventActor(...array_values(
                        array_filter($values, static function ($item) {
                            return $item instanceof EventsCollection;
                        })
                    )),
                    'networks' => new Networks(...array_values(
                        array_filter($values, static function ($item) {
                            return $item instanceof NetworksDefinition;
                        })
                    )),
                };
                break;
            case 'domainSearchResults':
            case 'entitySearchResults':
            case 'nameserverSearchResults':
                $this->assertArray($valueData, $keyName);
                $values = [];
                $target = match ($keyName) {
                    'domainSearchResults' => DomainDefinitionObjectClassName::class,
                    'entitySearchResults' => EntityDefinitionObjectClassName::class,
                    'nameserverSearchResults' => NameserverDefinitionObjectClassName::class,
                };
                foreach ($valueData as $item) {
                    $values[] = $this->createObject(
                        $target,
                        $item,
                        $depth
                    );
                }
                $data = match ($keyName) {
                    'domainSearchResults' => new DomainSearchResults(...array_values(
                        array_filter($values, static function ($item) {
                            return $item instanceof DomainDefinitionObjectClassName;
                        })
                    )),
                    'entitySearchResults' => new EntitySearchResults(...array_values(
                        array_filter($values, static function ($item) {
                            return $item instanceof EntityDefinitionObjectClassName;
                        })
                    )),
                    'nameserverSearchResults' => new NameserverSearchResults(...array_values(
                        array_filter($values, static function ($item) {
                            return $item instanceof NameserverDefinitionObjectClassName;
                        })
                    )),
                };
                break;
            case EmptyObject::class:
                $data = new EmptyObject();
                break;
            // vCard
            case VCardDefinition::class:
                $this->assertArray($valueData, $keyName);
                $valueData = array_values($valueData);
                // {0, 1:{}, 2, 3 ...} -> 4
                $this->assertCountGreaterOrEqual($valueData, 4);
                $name = array_shift($valueData);
                // 0 -> name
                $this->assertString($name);
                // 1 -> object type or label
                $object = array_shift($valueData);
                // assert array
                $this->assertArray($object);
                // 3 -> type value
                $typeValue = array_shift($valueData);
                // should not empty
                // $valueData = array_values($valueData);
                $this->assertCountGreaterThan($valueData, 0);
                foreach ($valueData as $val) {
                    $this->assertStringOrArray($val);
                }

                /**
                 * @var NamedRecursiveObjectData $object
                 */
                $object = $this->createObject(
                    NamedRecursiveObjectData::class,
                    $object,
                    $depth + 1
                );
                $data = new VCardDefinition(
                    $name,
                    $object,
                    $typeValue,
                    ...$valueData
                );
                break;
            case VCardsDefinitions::class:
                $this->assertArray($valueData, $keyName);
                $values = [];
                foreach ($valueData as $item) {
                    if (!$item) {
                        continue;
                    }
                    $item = $this->createObject(
                        VCardDefinition::class,
                        $item,
                        $depth + 1
                    );
                    $values[] = $item;
                }
                $data= new VCardsDefinitions(...array_values(array_filter($values, static function ($item) {
                    return $item instanceof VCardDefinition;
                })));
                break;
            case PublicIdsDefinitions::class:
                $this->assertArrayStringValue($valueData, $keyName);
                $this->assertArrayStringKey($valueData, $keyName);
                $values = [];
                foreach ($valueData as $k => $item) {
                    $values[] = $this->createObject(
                        $k,
                        $item,
                        $depth
                    );
                }
                $data = new PublicIdsDefinitions(...array_values(array_filter($values, static function ($item) {
                    return $item instanceof RdapResponseDataStringableInterface
                        || $item instanceof  RdapResponseDataNamedInterface;
                })));
                break;
            case EventsCollection::class:
            case RemarksDefinition::class:
            case NetworksDefinition::class:
            case NamedRecursiveObjectData::class:
            case NoticesDefinition::class:
            case NameServersDefinition::class:
            case DomainDefinitionObjectClassName::class:
            case EntityDefinitionObjectClassName::class:
            case NameserverDefinitionObjectClassName::class:
            case DsDataDefinition::class:
                $this->assertArray($valueData, $keyName);
                $this->assertArrayStringKey($valueData, $keyName);
                $values = [];
                foreach ($valueData as $key => $item) {
                    $values[] = $this->createObject(
                        $key,
                        $item,
                        $depth+1
                    );
                }
                try {
                    $data = match ($keyName) {
                        EntityDefinitionObjectClassName::class => new EntityDefinitionObjectClassName(
                            ...array_values(
                                array_filter($values, static function ($item) {
                                    return $item instanceof RdapResponseDataNamedInterface;
                                })
                            )
                        ),
                        RemarksDefinition::class => new RemarksDefinition(...array_values(
                            array_filter($values, static function ($item) {
                                return $item instanceof RdapResponseDataNamedInterface;
                            })
                        )),
                        NetworksDefinition::class => new NetworksDefinition(...array_values(
                            array_filter($values, static function ($item) {
                                return $item instanceof RdapResponseDataNamedInterface;
                            })
                        )),
                        NamedRecursiveObjectData::class => new NamedRecursiveObjectData(
                            ...array_values(
                                array_filter($values, static function ($item) {
                                    return $item instanceof RdapResponseDataNamedInterface
                                        || $item instanceof RdapResponseDataStringableInterface;
                                })
                            )
                        ),
                        EventsCollection::class => new EventsCollection(...array_values(
                            array_filter($values, static function ($item) {
                                return $item instanceof EventActor
                                    || $item instanceof EventAction
                                    || $item instanceof EventDate
                                    || $item instanceof Links;
                            })
                        )),
                        NoticesDefinition::class => new NoticesDefinition(...array_values(
                            array_filter($values, static function ($item) {
                                return $item instanceof RdapResponseDataNamedInterface;
                            })
                        )),
                        NameServersDefinition::class => new NameServersDefinition(...array_values(
                            array_filter($values, static function ($item) {
                                return $item instanceof RdapResponseDataNamedInterface;
                            })
                        )),
                        DomainDefinitionObjectClassName::class => new DomainDefinitionObjectClassName(
                            ...array_values(
                                array_filter($values, static function ($item) {
                                    return $item instanceof RdapResponseDataNamedInterface;
                                })
                            )
                        ),
                        NameserverDefinitionObjectClassName::class => new NameserverDefinitionObjectClassName(
                            ...array_values(
                                array_filter($values, static function ($item) {
                                    return $item instanceof RdapResponseDataNamedInterface;
                                })
                            )
                        ),
                        DsDataDefinition::class => new DsDataDefinition(
                            ...array_values(
                                array_filter($values, static function ($item) {
                                    return $item instanceof RdapResponseDataNamedInterface;
                                })
                            )
                        )
                    };
                } catch (Throwable) {
                    print_r($values);
                    exit;
                }
                break;
            default:
                if ($this->rdapConformance?->containPrefixHint($keyName)) {
                    $this->rdapConformanceData ??= new RdapCustomConformanceDataCollection();
                    $data = $this->rdapConformanceData->addFromData(
                        $keyName,
                        $valueData
                    );
                } else {
                    $values = [];
                    if (is_array($valueData)) {
                        foreach ($valueData as $key => $item) {
                            if (is_string($key)) {
                                $values[] = new CustomNamedData($key, $item);
                                continue;
                            }
                            $values[] = new CustomUnNamedData($item);
                        }
                        $data = new CustomArrayData($keyName, ...$values);
                    } else {
                        $data = new CustomNamedData($keyName, $valueData);
                    }
                }
                break;
        }

        if ($depth > 0 && $data->rootOnly()) {
            throw new InvalidDataTypeException(
                sprintf(
                    'Data "%s" only allowed in root object. Data tree is on depth "%d"',
                    $data->getName(),
                    $depth + 1
                )
            );
        }
        return $data;
    }

    /**
     * @inheritDoc
     */
    public function getRdapConformance(): ?RdapConformance
    {
        return $this->rdapConformance??null;
    }

    /**
     * @inheritDoc
     */
    public function getRdapConformanceData(): ?RdapResponseDataConformanceDataInterface
    {
        return $this->rdapConformanceData;
    }

    /**
     * @return ObjectClassName<string>|null
     */
    public function getObjectClassName(): ?ObjectClassName
    {
        return $this->objectClassName??null;
    }

    /**
     * @inheritDoc
     */
    public function getErrorCode(): ?ErrorCode
    {
        return $this->errorCode??null;
    }

    /**
     * @inheritDoc
     */
    public function getTitle(): ?Title
    {
        return $this->title??null;
    }

    /**
     * @inheritDoc
     */
    public function getStatus(): ?Status
    {
        return $this->status??null;
    }

    /**
     * @inheritDoc
     */
    public function getDescription(): ?Description
    {
        return $this->description??null;
    }

    /**
     * @inheritDoc
     */
    public function getLang(): ?Lang
    {
        return $this->lang??null;
    }

    /**
     * @inheritDoc
     */
    public function getNotices(): ?Notices
    {
        return $this->notices??null;
    }

    /**
     * @inheritDoc
     */
    public function getLinks(): ?Links
    {
        return $this->links??null;
    }

    /**
     * @inheritDoc
     */
    public function getHandle(): ?Handle
    {
        return $this->handle??null;
    }

    /**
     * @inheritDoc
     */
    public function getEvents(): ?Events
    {
        return $this->events??null;
    }

    /**
     * @inheritDoc
     */
    public function getEntities(): ?Entities
    {
        return $this->entities??null;
    }

    /**
     * @inheritDoc
     */
    public function getName(): ?Name
    {
        return $this->name??null;
    }

    /**
     * @inheritDoc
     */
    public function isError(): bool
    {
        return $this->getErrorCode() !== null;
    }

    /**
     * @inheritDoc
     */
    public function getRelatedRequest(): ?RdapRequestInterface
    {
        if ($this->relatedRequest !== null) {
            return $this->relatedRequest?:null;
        }
        $this->relatedRequest = false;
        foreach (($this->getLinks()?->getLinks()??[]) as $link) {
            if ($link->getRel()?->getPlainData() !== 'related') {
                continue;
            }
            $type = $link->getType()?->getPlainData();
            if (!$type || !is_string($type) || !str_contains($type, 'application/rdap+json')) {
                continue;
            }
            $url = $link->getHref()?->getPlainData();
            if ($url && ($this->relatedRequest = $this->createObjectRdapRequestURL($url)??false)) {
                return $this->relatedRequest;
            }
        }
        return null;
    }

    /**
     * Create object from RDAP request URL
     *
     * @param string|null $url
     * @return RdapRequestInterface|null
     */
    private function createObjectRdapRequestURL(?string $url): ?RdapRequestInterface
    {
        if ($url && preg_match('~^https?://~i', $url)) {
            try {
                return $this
                    ->getRdapResponseObject()
                    ->getRequest()
                    ->withRdapSearchURL($url);
            } catch (MismatchProtocolBehaviorException) {
            }
        }

        return null;
    }

    /**
     * Prevent set object prop
     *
     * @param string $name
     * @param mixed $value
     * @return void
     */
    public function __set(string $name, mixed $value): void
    {
        // pass
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function __get(string $name) : mixed
    {
        return property_exists($this, $name) ? $this->{$name} : null;
    }

    /**
     * @return array<string, RdapResponseDataInterface>
     */
    public function jsonSerialize(): array
    {
        $data = [];
        foreach (get_object_vars($this) as $key => $value) {
            if ($key === 'rdapConformanceData') {
                continue;
            }
            if ($value instanceof RdapResponseDataInterface) {
                $data[$value->getName()] = $value;
            }
        }
        foreach (($this->getRdapConformanceData()??[]) as $key => $item) {
            if (!isset($data[$key])) {
                $data[$key] = $item;
            }
        }
        return $data;
    }

    /**
     * @return string returns the JSON representation of the object
     */
    public function __toString(): string
    {
        return json_encode($this, JSON_UNESCAPED_SLASHES)?:'';
    }
}
